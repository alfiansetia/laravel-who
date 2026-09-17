<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Akl;
use App\Models\IzinEdar;
use Illuminate\Http\Request;

class AklController extends Controller
{
    public function __construct()
    {
        $this->middleware('env_auth')->only(['destroy', 'destroy_batch']);
    }

    public function index(Request $request)
    {
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
            'data'        => $data,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ]);
    }

    public function destroy($id)
    {
        $akl = Akl::find($id);
        if (! $akl) {
            return $this->sendNotFound();
        }
        $akl->delete(); // file S3 dihapus via model observer

        return $this->sendResponse($akl, 'Deleted!');
    }

    public function destroy_batch(Request $request)
    {
        $this->validate($request, [
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:akls,id',
        ]);

        // Hapus per model (bukan query-delete) agar observer ikut hapus file S3.
        $images = Akl::whereIn('id', $request->ids)->get();
        foreach ($images as $akl) {
            $akl->delete();
        }

        return $this->sendResponse([
            'deleted_count' => $images->count(),
        ], 'AKL deleted successfully.');
    }

    /**
     * Cek reg_no AKL ke tabel izin_edars (cocok nomor_izin_edar).
     * GET /api/akls/{id}/check-izin
     * Return AKL + kandidat izin edar + perbandingan field.
     */
    public function checkIzin($id)
    {
        $akl = Akl::find($id);
        if (! $akl) {
            return $this->sendNotFound();
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
                'id'              => $iz->id,
                'kategori'        => $iz->kategori,
                'nomor_izin_edar' => $iz->nomor_izin_edar,
                'merk'            => $iz->merk,
                'jenis_produk'    => $iz->jenis_produk,
                'pendaftar'       => $iz->pendaftar,
                'pabrik'          => $iz->pabrik,
                'tgl_terbit'      => $izFrom,
                'tgl_exp'         => $izExp,
                'is_expired'      => (bool) $iz->is_expired,
                'diff'            => [
                    'reg_no_sama'   => $regNoSama,
                    'expired_sama'  => $expiredSama,
                    'terbit_sama'   => $terbitSama,
                    'nama_sama'     => $namaSama,
                    'vendor_sama'   => $vendorSama,
                ],
                // true bila semua field sudah sama → tidak bisa dipilih.
                'is_synced'       => $regNoSama && $expiredSama && $terbitSama && $namaSama && $vendorSama,
                // Kandidat nilai bila di-apply ke AKL:
                'saran'           => [
                    'reg_no'       => $iz->nomor_izin_edar,
                    'reg_name'     => $saranName,
                    'vendor'       => $saranVendor,
                    'date_from'    => $izFrom,
                    'date_expired' => $izExp,
                ],
            ];
        });

        return $this->sendResponse([
            'akl'     => $akl,
            'matches' => $data,
        ], $data->isEmpty() ? 'Tidak ada data cocok di Izin Edar.' : 'Ditemukan '.$data->count().' data cocok.');
    }

    /**
     * Terapkan data Izin Edar terpilih ke AKL.
     * PUT /api/akls/{id}/apply-izin  body: { izin_edar_id }
     */
    public function applyIzin(Request $request, $id)
    {
        $akl = Akl::find($id);
        if (! $akl) {
            return $this->sendNotFound();
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
            return $this->sendError('Data sudah sama, tidak perlu disimpan.', 422);
        }

        $akl->update([
            'reg_no'       => $iz->nomor_izin_edar ?: $akl->reg_no,
            'reg_name'     => $iz->merk ?: ($iz->jenis_produk ?: $akl->reg_name),
            'vendor'       => $iz->pendaftar ?: ($iz->pabrik ?: $akl->vendor),
            'date_from'    => $iz->tgl_terbit ? $iz->tgl_terbit->format('Y-m-d') : $akl->date_from,
            'date_expired' => $iz->tgl_exp ? $iz->tgl_exp->format('Y-m-d') : $akl->date_expired,
        ]);

        return $this->sendResponse($akl->fresh(), 'AKL disinkron dari Izin Edar.');
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
            return $this->sendError('Nomor izin edar kosong.', 422);
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
            return $this->sendError(
                'Sudah ada di AKL (reg_no + tgl expired sama, id AKL #'.$existing->id.'). Copy ditolak.',
                422
            );
        }

        $akl = Akl::create([
            'reg_no'       => $regNo,
            'reg_name'     => $iz->merk ?: $iz->jenis_produk,
            'vendor'       => $iz->pendaftar ?: $iz->pabrik,
            'date_from'    => $dateFrom,
            'date_expired' => $dateExpired,
            'file'         => null, // copy awal tanpa lampiran; bisa upload susulan via halaman AKL
        ]);

        return $this->sendResponse($akl, 'Berhasil dicopy ke AKL.');
    }
}
