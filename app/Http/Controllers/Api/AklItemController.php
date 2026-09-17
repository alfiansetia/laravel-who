<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AklItem;
use App\Models\Product;
use Illuminate\Http\Request;

class AklItemController extends Controller
{
    public function index(Request $request)
    {
        $this->validate($request, [
            'akl_id' => 'required|exists:akls,id',
        ]);

        $data = AklItem::with('product:id,code,name')
            ->where('akl_id', $request->akl_id)
            ->orderBy('code')
            ->get();

        return $this->sendResponse($data, 'Success!');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'akl_id' => 'required|exists:akls,id',
            // Boleh custom / di luar master product, jadi cukup string (tanpa exists).
            'code'   => 'required|string|max:100',
            'name'   => 'nullable|string|max:255',
        ]);

        $code = trim($request->code);

        if ($code === '') {
            return $this->sendError('Code tidak boleh kosong.', 422);
        }

        // Cegah duplikat code dalam satu AKL (case-insensitive).
        $exists = AklItem::where('akl_id', $request->akl_id)
            ->whereRaw('LOWER(code) = ?', [strtolower($code)])
            ->exists();
        if ($exists) {
            return $this->sendError('Code sudah ada di AKL ini.', 422);
        }

        // Cari ke tabel product by code (exact, case-insensitive) sekalian ambil name.
        $product = Product::whereRaw('LOWER(code) = ?', [strtolower($code)])->first(['id', 'code', 'name']);

        $name = trim((string) ($request->name ?? ''));
        if ($name === '') {
            $name = $product ? $product->name : null;
        }

        $item = AklItem::create([
            'akl_id' => $request->akl_id,
            'code'   => $code,
            'name'   => $name ?: null,
        ]);

        return $this->sendResponse($item->load('product:id,code,name'), 'Item ditambahkan!');
    }

    /**
     * Cek item ke tabel products (cocok by code).
     * GET /api/akl-items/{id}/check-product
     * Return item + kandidat product + perbandingan code/name.
     */
    public function checkProduct($id)
    {
        $item = AklItem::with('product:id,code,name')->find($id);
        if (! $item) {
            return $this->sendNotFound();
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
                'id'        => $p->id,
                'code'      => $p->code,
                'name'      => $p->name,
                'diff'      => [
                    'code_sama' => $codeSama,
                    'nama_sama' => $namaSama,
                ],
                // true bila code+name sudah sama → tidak bisa dipilih.
                'is_synced' => $codeSama && $namaSama,
            ];
        });

        return $this->sendResponse([
            'item'    => $item,
            'matches' => $data,
        ], $data->isEmpty() ? 'Tidak ada yang cocok di tabel product.' : 'Ditemukan '.$data->count().' kandidat cocok.');
    }

    /**
     * Terapkan code/name product terpilih ke item.
     * PUT /api/akl-items/{id}/apply-product  body: { product_id }
     */
    public function applyProduct(Request $request, $id)
    {
        $item = AklItem::find($id);
        if (! $item) {
            return $this->sendNotFound();
        }

        $this->validate($request, [
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $product = Product::findOrFail($request->product_id);

        $norm = fn ($v) => mb_strtolower(trim((string) ($v ?? '')));
        $sudahSama = strcasecmp(trim((string) $product->code), trim((string) $item->code)) === 0
            && $norm($product->name) === $norm($item->name);

        if ($sudahSama) {
            return $this->sendError('Code/name sudah sama dengan product, tidak perlu disimpan.', 422);
        }

        $item->update([
            'code' => $product->code,
            'name' => $product->name,
        ]);

        return $this->sendResponse($item->fresh()->load('product:id,code,name'), 'Item disinkron dari product.');
    }

    public function destroy($id)
    {
        $aklItem = AklItem::find($id);
        if (! $aklItem) {
            return $this->sendNotFound();
        }
        $aklItem->delete();

        return $this->sendResponse($aklItem, 'Item dihapus!');
    }

    public function destroy_batch(Request $request)
    {
        $this->validate($request, [
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:akl_items,id',
        ]);

        $count = AklItem::whereIn('id', $request->ids)->delete();

        return $this->sendResponse(['deleted_count' => $count], 'Items deleted successfully.');
    }
}
