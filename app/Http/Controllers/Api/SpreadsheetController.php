<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\PltbbServices;
use Illuminate\Http\Request;

class SpreadsheetController extends Controller
{

    public function index()
    {
        try {
            $data = PltbbServices::get();
            return response()->json([
                'message' => 'Data berhasil diambil',
                'data'    => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error get data',
                'error'   => $e->getMessage(),
                'data'    => []
            ], 500);
        }
    }

    public function sync_all(Request $request)
    {
        try {
            $data = PltbbServices::get();
            $updatedCount = 0;
            foreach ($data as $item) {
                $code = $item[3] ?? null;
                $p = $item[8] ?? null;
                $l = $item[9] ?? null;
                $t = $item[10] ?? null;
                $b = $item[11] ?? null;
                $note = $item[12] ?? null;
                if (empty($code)) continue;
                if (empty($p) || empty($l) || empty($t) || empty($b)) continue;
                $product = Product::where('code', $code)->first();
                if ($product) {
                    $product->pltbb()->updateOrCreate([
                        'product_id' => $product->id,
                    ], [
                        'p'     => $p,
                        'l'     => $l,
                        't'     => $t,
                        'b'     => $b,
                        'note'  => $note
                    ]);
                    $updatedCount++;
                }
            }
            return response()->json([
                'message' => 'Data berhasil sinkronisasi ' . $updatedCount . ' data!',
                'data'    => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error get data',
                'error'   => $e->getMessage(),
                'data'    => []
            ], 500);
        }
    }

    public function sync_product(Request $request)
    {
        $this->validate($request, [
            'code'  => 'required',
            'p'     => 'required|decimal:2',
            'l'     => 'required|decimal:2',
            't'     => 'required|decimal:2',
            'b'     => 'required|decimal:2',
            'note'  => 'nullable'
        ]);
        $code = $request->code;
        $p = (float) $request->p;
        $l = (float) $request->l;
        $t = (float) $request->t;
        $b = (float) $request->b;
        $note = $request->note;

        $product = Product::where('code', $code)->first();
        if (!$product) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
        $product->pltbb()->updateOrCreate([
            'product_id' => $product->id,
        ], [
            'p'     => $p,
            'l'     => $l,
            't'     => $t,
            'b'     => $b,
            'note'  => $note
        ]);
        return response()->json([
            'message' => 'Data berhasil disimpan',
            'data'    => $product->pltbb
        ]);
    }
}
