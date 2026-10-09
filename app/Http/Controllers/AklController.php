<?php

namespace App\Http\Controllers;

use App\Models\Akl;
use App\Models\AklItem;
use App\Models\IzinEdar;
use App\Models\Product;
use App\Services\AklFileStorage;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AklController extends Controller
{
    public function __construct()
    {
        $this->middleware('env_auth')->only(['destroy', 'destroyBatch', 'destroy_batch']);
    }

    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            $perPage = min((int) $request->input('per_page', 25), 200);
            $page = max((int) $request->input('page', 1), 1);

            $query = Akl::query();

            if ($request->filled('search')) {
                $keyword = $request->search;
                $query->where(function ($q) use ($keyword) {
                    $q->where('reg_no', 'like', "%{$keyword}%")
                        ->orWhere('reg_name', 'like', "%{$keyword}%")
                        ->orWhere('vendor', 'like', "%{$keyword}%");
                });
            }

            $total = (clone $query)->count();

            // reg_no boleh sama di banyak baris (perpanjangan), tampilkan per dokumen.
            $data = $query->withCount('items')
                ->orderByDesc('date_expired')
                ->orderByDesc('id')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get([
                    'id',
                    'reg_no',
                    'reg_name',
                    'vendor',
                    'date_from',
                    'date_expired',
                    'file',
                    'created_at',
                ]);

            return response()->json([
                'data' => $data,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => (int) ceil($total / $perPage),
            ]);
        }

        // Data dimuat via API (api.akls.index) ala alamat_baru.
        return Inertia::render('Akl/Index', [
            'title' => 'AKL',
            'filters' => $request->only(['search', 'page']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Akl/Create', [
            'title' => 'Upload Lampiran',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'reg_no' => 'required|string|max:100',
            'reg_name' => 'nullable|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'date_from' => 'nullable|date',
            'date_expired' => 'nullable|date|after_or_equal:date_from',
            'file' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:20480',
        ]);

        $filename = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = $this->buildFilename(
                $request->reg_no,
                $request->date_expired,
                strtolower($file->getClientOriginalExtension())
            );

            try {
                $ok = AklFileStorage::put($filename, file_get_contents($file->getRealPath()));
            } catch (\Throwable $e) {
                report($e);
                $ok = false;
            }

            if (! $ok) {
                return back()->withInput()
                    ->with('error', 'Upload gagal, file tidak tersimpan di S3/R2.');
            }
        }

        Akl::create([
            'reg_no' => $request->reg_no,
            'reg_name' => $request->reg_name,
            'vendor' => $request->vendor,
            'date_from' => $request->date_from,
            'date_expired' => $request->date_expired,
            'file' => $filename,
        ]);

        return redirect()->route('akls.index')
            ->with('success', 'Data AKL berhasil disimpan.');
    }

    /**
     * Stream file dari S3 (PDF tampil inline, gambar tampil langsung).
     * Mode JSON (axios) mengembalikan data record sebagai JSON.
     */
    public function show(Request $request, Akl $akl)
    {
        if ($request->wantsJson()) {
            return $this->sendResponse($akl, 'Success!');
        }

        if (! $akl->file) {
            abort(404, 'File tidak ada.');
        }

        $contents = AklFileStorage::get($akl->file);
        if ($contents === null) {
            abort(404, 'File tidak ditemukan di S3/R2.');
        }

        return response($contents, 200, [
            'Content-Type' => AklFileStorage::mime($akl->file),
            'Content-Disposition' => 'inline; filename="'.$akl->file.'"',
        ]);
    }

    public function edit(Akl $akl): Response
    {
        return Inertia::render('Akl/Edit', [
            'title' => 'Edit Lampiran',
            'record' => $akl,
        ]);
    }

    /**
     * Kelola item (product code ref + custom) per AKL.
     */
    public function items(Akl $akl): Response
    {
        $akl->loadCount('items');

        return Inertia::render('Akl/Items', [
            'title' => 'Item '.$akl->reg_no,
            'record' => $akl,
        ]);
    }

    public function update(Request $request, Akl $akl)
    {
        $request->validate([
            'reg_no' => 'required|string|max:100',
            'reg_name' => 'nullable|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'date_from' => 'nullable|date',
            'date_expired' => 'nullable|date|after_or_equal:date_from',
            'file' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:20480',
        ]);

        $data = $request->only(['reg_no', 'reg_name', 'vendor', 'date_from', 'date_expired']);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = $this->buildFilename(
                $request->reg_no,
                $request->date_expired,
                strtolower($file->getClientOriginalExtension())
            );

            try {
                $ok = AklFileStorage::put($filename, file_get_contents($file->getRealPath()));
            } catch (\Throwable $e) {
                report($e);
                $ok = false;
            }

            if (! $ok) {
                return back()->withInput()
                    ->with('error', 'Upload gagal, file tidak tersimpan di S3/R2.');
            }

            if ($akl->file) {
                AklFileStorage::delete($akl->file);
            }
            $data['file'] = $filename;
        }

        $akl->update($data);

        return redirect()->route('akls.index')
            ->with('success', 'Data AKL berhasil diperbarui.');
    }

    public function destroy(Request $request, Akl $akl)
    {
        $akl->delete(); // file S3 dihapus via model observer

        if ($request->wantsJson()) {
            return $this->sendResponse($akl, 'Deleted!');
        }

        return redirect()->route('akls.index')
            ->with('success', 'Lampiran AKL berhasil dihapus.');
    }

    public function destroyBatch(Request $request)
    {
        $this->validate($request, [
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:akls,id',
        ]);

        // Hapus per model (bukan query-delete) agar observer ikut hapus file S3.
        $images = Akl::whereIn('id', $request->ids)->get();
        foreach ($images as $akl) {
            $akl->delete();
        }

        if ($request->wantsJson()) {
            return $this->sendResponse([
                'deleted_count' => $images->count(),
            ], 'AKL deleted successfully.');
        }

        return redirect()->route('akls.index')
            ->with('success', $images->count().' data AKL berhasil dihapus.');
    }

    /**
     * Alias snake_case untuk kompatibilitas route api.php lama.
     */
    public function destroy_batch(Request $request)
    {
        return $this->destroyBatch($request);
    }

    /**
     * Cek reg_no AKL ke tabel izin_edars (cocok nomor_izin_edar).
     * GET /api/akls/{id}/check-izin
     * Return AKL + kandidat izin edar + perbandingan field.
     */
    public function checkIzin(Request $request, $id)
    {
        $akl = Akl::find($id);
        if (! $akl) {
            if ($request->wantsJson()) {
                return $this->sendNotFound();
            }

            return back()->with('error', 'Data AKL tidak ditemukan.');
        }

        $regNo = trim((string) $akl->reg_no);

        // Exact match dulu, fallback like bila tidak ada yang persis sama.
        $exact = IzinEdar::where('nomor_izin_edar', $regNo)
            ->orderByDesc('tgl_exp')
            ->limit(10)
            ->get();

        $matches = $exact->isNotEmpty()
            ? $exact
            : IzinEdar::where('nomor_izin_edar', 'like', "%{$regNo}%")
                ->orderByDesc('tgl_exp')
                ->limit(10)
                ->get();

        $data = $matches->map(function (IzinEdar $iz) use ($akl) {
            $aklExp = $akl->date_expired ? $akl->date_expired->format('Y-m-d') : null;
            $izExp = $iz->tgl_exp ? $iz->tgl_exp->format('Y-m-d') : null;
            $aklFrom = $akl->date_from ? $akl->date_from->format('Y-m-d') : null;
            $izFrom = $iz->tgl_terbit ? $iz->tgl_terbit->format('Y-m-d') : null;

            $saranName = $iz->merk ?: $iz->jenis_produk;
            $saranVendor = $iz->pendaftar ?: $iz->pabrik;

            $norm = fn ($v) => mb_strtolower(trim((string) ($v ?? '')));

            $regNoSama = strcasecmp(trim((string) $iz->nomor_izin_edar), trim((string) $akl->reg_no)) === 0;
            $expiredSama = $aklExp === $izExp;
            $terbitSama = $aklFrom === $izFrom;
            $namaSama = $norm($saranName) === $norm($akl->reg_name);
            $vendorSama = $norm($saranVendor) === $norm($akl->vendor);

            return [
                'id' => $iz->id,
                'kategori' => $iz->kategori,
                'nomor_izin_edar' => $iz->nomor_izin_edar,
                'merk' => $iz->merk,
                'jenis_produk' => $iz->jenis_produk,
                'pendaftar' => $iz->pendaftar,
                'pabrik' => $iz->pabrik,
                'tgl_terbit' => $izFrom,
                'tgl_exp' => $izExp,
                'is_expired' => (bool) $iz->is_expired,
                'diff' => [
                    'reg_no_sama' => $regNoSama,
                    'expired_sama' => $expiredSama,
                    'terbit_sama' => $terbitSama,
                    'nama_sama' => $namaSama,
                    'vendor_sama' => $vendorSama,
                ],
                // true bila semua field sudah sama → tidak bisa dipilih.
                'is_synced' => $regNoSama && $expiredSama && $terbitSama && $namaSama && $vendorSama,
                // Kandidat nilai bila di-apply ke AKL:
                'saran' => [
                    'reg_no' => $iz->nomor_izin_edar,
                    'reg_name' => $saranName,
                    'vendor' => $saranVendor,
                    'date_from' => $izFrom,
                    'date_expired' => $izExp,
                ],
            ];
        });

        $message = $data->isEmpty() ? 'Tidak ada data cocok di Izin Edar.' : 'Ditemukan '.$data->count().' data cocok.';

        if ($request->wantsJson()) {
            return $this->sendResponse([
                'akl' => $akl,
                'matches' => $data,
            ], $message);
        }

        return back()->with('success', $message);
    }

    /**
     * Terapkan data Izin Edar terpilih ke AKL.
     * PUT /api/akls/{id}/apply-izin  body: { izin_edar_id }
     */
    public function applyIzin(Request $request, $id)
    {
        $akl = Akl::find($id);
        if (! $akl) {
            if ($request->wantsJson()) {
                return $this->sendNotFound();
            }

            return back()->with('error', 'Data AKL tidak ditemukan.');
        }

        $this->validate($request, [
            'izin_edar_id' => 'required|integer|exists:izin_edars,id',
        ]);

        $iz = IzinEdar::findOrFail($request->izin_edar_id);

        $norm = fn ($v) => mb_strtolower(trim((string) ($v ?? '')));
        $sudahSama = strcasecmp(trim((string) $iz->nomor_izin_edar), trim((string) $akl->reg_no)) === 0
            && ($akl->date_expired?->format('Y-m-d')) === ($iz->tgl_exp?->format('Y-m-d'))
            && ($akl->date_from?->format('Y-m-d')) === ($iz->tgl_terbit?->format('Y-m-d'))
            && $norm($iz->merk ?: $iz->jenis_produk) === $norm($akl->reg_name)
            && $norm($iz->pendaftar ?: $iz->pabrik) === $norm($akl->vendor);

        if ($sudahSama) {
            if ($request->wantsJson()) {
                return $this->sendError('Data sudah sama, tidak perlu disimpan.', 422);
            }

            return back()->with('error', 'Data sudah sama, tidak perlu disimpan.');
        }

        $akl->update([
            'reg_no' => $iz->nomor_izin_edar ?: $akl->reg_no,
            'reg_name' => $iz->merk ?: ($iz->jenis_produk ?: $akl->reg_name),
            'vendor' => $iz->pendaftar ?: ($iz->pabrik ?: $akl->vendor),
            'date_from' => $iz->tgl_terbit ? $iz->tgl_terbit->format('Y-m-d') : $akl->date_from,
            'date_expired' => $iz->tgl_exp ? $iz->tgl_exp->format('Y-m-d') : $akl->date_expired,
        ]);

        if ($request->wantsJson()) {
            return $this->sendResponse($akl->fresh(), 'AKL disinkron dari Izin Edar.');
        }

        return redirect()->route('akls.index')
            ->with('success', 'AKL disinkron dari Izin Edar.');
    }

    /**
     * Copy satu baris Izin Edar menjadi baris baru di tabel AKL.
     * POST /api/akls/copy-from-izin  body: { izin_edar_id }
     * Kunci duplikat: reg_no + date_expired (keduanya penting).
     * Kalau pasangan itu sudah ada di AKL → tolak 422.
     */
    public function copyFromIzin(Request $request)
    {
        $this->validate($request, [
            'izin_edar_id' => 'required|integer|exists:izin_edars,id',
        ]);

        $iz = IzinEdar::findOrFail($request->izin_edar_id);

        $regNo = trim((string) $iz->nomor_izin_edar);
        if ($regNo === '') {
            if ($request->wantsJson()) {
                return $this->sendError('Nomor izin edar kosong.', 422);
            }

            return back()->with('error', 'Nomor izin edar kosong.');
        }

        $dateExpired = $iz->tgl_exp ? $iz->tgl_exp->format('Y-m-d') : null;
        $dateFrom = $iz->tgl_terbit ? $iz->tgl_terbit->format('Y-m-d') : null;

        // Cek duplikat: reg_no (case-insensitive, trim) + date_expired sama.
        $existsQuery = Akl::whereRaw('LOWER(TRIM(reg_no)) = ?', [mb_strtolower($regNo)]);
        if ($dateExpired === null) {
            $existsQuery->whereNull('date_expired');
        } else {
            $existsQuery->whereDate('date_expired', $dateExpired);
        }
        $existing = $existsQuery->first();

        if ($existing) {
            $message = 'Sudah ada di AKL (reg_no + tgl expired sama, id AKL #'.$existing->id.'). Copy ditolak.';
            if ($request->wantsJson()) {
                return $this->sendError($message, 422);
            }

            return back()->with('error', $message);
        }

        $akl = Akl::create([
            'reg_no' => $regNo,
            'reg_name' => $iz->merk ?: $iz->jenis_produk,
            'vendor' => $iz->pendaftar ?: $iz->pabrik,
            'date_from' => $dateFrom,
            'date_expired' => $dateExpired,
            'file' => null, // copy awal tanpa lampiran; bisa upload susulan via halaman AKL
        ]);

        if ($request->wantsJson()) {
            return $this->sendResponse($akl, 'Berhasil dicopy ke AKL.');
        }

        return redirect()->route('akls.index')
            ->with('success', 'Berhasil dicopy ke AKL.');
    }

    public function aklItemIndex(Request $request)
    {
        $this->validate($request, [
            'akl_id' => 'required|exists:akls,id',
        ]);

        $data = AklItem::with('product:id,code,name')
            ->where('akl_id', $request->akl_id)
            ->orderBy('code')
            ->get();

        // Endpoint listing murni (tidak ada halaman Inertia khusus) → selalu JSON.
        return $this->sendResponse($data, 'Success!');
    }

    public function aklItemStore(Request $request)
    {
        $this->validate($request, [
            'akl_id' => 'required|exists:akls,id',
            // Boleh custom / di luar master product, jadi cukup string (tanpa exists).
            'code' => 'required|string|max:100',
            'name' => 'nullable|string|max:255',
        ]);

        $code = trim($request->code);

        if ($code === '') {
            if ($request->wantsJson()) {
                return $this->sendError('Code tidak boleh kosong.', 422);
            }

            return back()->withInput()->with('error', 'Code tidak boleh kosong.');
        }

        // Cegah duplikat code dalam satu AKL (case-insensitive).
        $exists = AklItem::where('akl_id', $request->akl_id)
            ->whereRaw('LOWER(code) = ?', [strtolower($code)])
            ->exists();
        if ($exists) {
            if ($request->wantsJson()) {
                return $this->sendError('Code sudah ada di AKL ini.', 422);
            }

            return back()->withInput()->with('error', 'Code sudah ada di AKL ini.');
        }

        // Cari ke tabel product by code (exact, case-insensitive) sekalian ambil name.
        $product = Product::whereRaw('LOWER(code) = ?', [strtolower($code)])->first(['id', 'code', 'name']);

        $name = trim((string) ($request->name ?? ''));
        if ($name === '') {
            $name = $product ? $product->name : null;
        }

        $item = AklItem::create([
            'akl_id' => $request->akl_id,
            'code' => $code,
            'name' => $name ?: null,
        ]);

        if ($request->wantsJson()) {
            return $this->sendResponse($item->load('product:id,code,name'), 'Item ditambahkan!');
        }

        return back()->with('success', 'Item ditambahkan!');
    }

    /**
     * Cek item ke tabel products (cocok by code).
     * GET /api/akl-items/{id}/check-product
     * Return item + kandidat product + perbandingan code/name.
     */
    public function aklItemCheckProduct(Request $request, $id)
    {
        $item = AklItem::with('product:id,code,name')->find($id);
        if (! $item) {
            if ($request->wantsJson()) {
                return $this->sendNotFound();
            }

            return back()->with('error', 'Item tidak ditemukan.');
        }

        $code = trim((string) $item->code);

        // Exact match (case-insensitive) dulu, fallback like bila tidak ada yang persis sama.
        $exact = Product::whereRaw('LOWER(code) = ?', [mb_strtolower($code)])
            ->orderBy('code')
            ->limit(10)
            ->get(['id', 'code', 'name']);

        $matches = $exact->isNotEmpty()
            ? $exact
            : Product::where('code', 'like', "%{$code}%")
                ->orderBy('code')
                ->limit(10)
                ->get(['id', 'code', 'name']);

        $norm = fn ($v) => mb_strtolower(trim((string) ($v ?? '')));

        $data = $matches->map(function (Product $p) use ($item, $norm) {
            $codeSama = strcasecmp(trim((string) $p->code), trim((string) $item->code)) === 0;
            $namaSama = $norm($p->name) === $norm($item->name);

            return [
                'id' => $p->id,
                'code' => $p->code,
                'name' => $p->name,
                'diff' => [
                    'code_sama' => $codeSama,
                    'nama_sama' => $namaSama,
                ],
                // true bila code+name sudah sama → tidak bisa dipilih.
                'is_synced' => $codeSama && $namaSama,
            ];
        });

        $message = $data->isEmpty() ? 'Tidak ada yang cocok di tabel product.' : 'Ditemukan '.$data->count().' kandidat cocok.';

        // Endpoint read-only murni (tidak ada halaman Inertia khusus) → selalu JSON.
        return $this->sendResponse([
            'item' => $item,
            'matches' => $data,
        ], $message);
    }

    /**
     * Terapkan code/name product terpilih ke item.
     * PUT /api/akl-items/{id}/apply-product  body: { product_id }
     */
    public function aklItemApplyProduct(Request $request, $id)
    {
        $item = AklItem::find($id);
        if (! $item) {
            if ($request->wantsJson()) {
                return $this->sendNotFound();
            }

            return back()->with('error', 'Item tidak ditemukan.');
        }

        $this->validate($request, [
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $product = Product::findOrFail($request->product_id);

        $norm = fn ($v) => mb_strtolower(trim((string) ($v ?? '')));
        $sudahSama = strcasecmp(trim((string) $product->code), trim((string) $item->code)) === 0
            && $norm($product->name) === $norm($item->name);

        if ($sudahSama) {
            if ($request->wantsJson()) {
                return $this->sendError('Code/name sudah sama dengan product, tidak perlu disimpan.', 422);
            }

            return back()->with('error', 'Code/name sudah sama dengan product, tidak perlu disimpan.');
        }

        $item->update([
            'code' => $product->code,
            'name' => $product->name,
        ]);

        if ($request->wantsJson()) {
            return $this->sendResponse($item->fresh()->load('product:id,code,name'), 'Item disinkron dari product.');
        }

        return back()->with('success', 'Item disinkron dari product.');
    }

    public function aklItemDestroy(Request $request, $id)
    {
        $aklItem = AklItem::find($id);
        if (! $aklItem) {
            if ($request->wantsJson()) {
                return $this->sendNotFound();
            }

            return back()->with('error', 'Item tidak ditemukan.');
        }
        $aklItem->delete();

        if ($request->wantsJson()) {
            return $this->sendResponse($aklItem, 'Item dihapus!');
        }

        return back()->with('success', 'Item dihapus!');
    }

    public function aklItemDestroyBatch(Request $request)
    {
        $this->validate($request, [
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:akl_items,id',
        ]);

        $count = AklItem::whereIn('id', $request->ids)->delete();

        if ($request->wantsJson()) {
            return $this->sendResponse(['deleted_count' => $count], 'Items deleted successfully.');
        }

        return back()->with('success', $count.' item berhasil dihapus.');
    }

    /**
     * Alias snake_case untuk kompatibilitas route api.php lama.
     */
    public function aklItemDestroy_batch(Request $request)
    {
        return $this->aklItemDestroyBatch($request);
    }

    /**
     * Nama file lampiran: {reg_no}_{exp Ymd/NOEXP}_{4 random}.{ext}
     * cth: AKL_123_20280112_AB12.pdf
     */
    protected function buildFilename(?string $regNo, $dateExpired, string $extension): string
    {
        $safeReg = trim(preg_replace('/[^A-Za-z0-9]+/', '_', (string) $regNo), '_');
        if ($safeReg === '') {
            $safeReg = 'AKL';
        }

        try {
            $exp = $dateExpired
                ? Carbon::parse($dateExpired)->format('Ymd')
                : 'NOEXP';
        } catch (\Throwable $e) {
            $exp = 'NOEXP';
        }

        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        // Jaga keunikan di storage (regenerasi 4 random bila tabrakan).
        for ($i = 0; $i < 5; $i++) {
            $filename = $safeReg.'_'.$exp.'_'.Str::upper(Str::random(4)).'.'.$extension;
            if (! AklFileStorage::exists($filename)) {
                return $filename;
            }
        }

        return $safeReg.'_'.$exp.'_'.Str::upper(Str::random(8)).'.'.$extension;
    }
}
