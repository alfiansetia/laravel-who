<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;

class SpreadsheetController extends Controller
{

    public function index()
    {
        $url = config('services.spreadsheet_url');
        if (empty($url)) {
            return response()->json([
                'message' => 'URL spreadsheet tidak ditemukan',
                'data'    => []
            ], 500);
        }

        $tempPath = storage_path('app/temp_' . uniqid() . '.xlsx');

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0'
            ])->get($url);

            if (!$response->ok()) {
                return response()->json([
                    'message' => 'Gagal mengambil file dari Google Sheets',
                    'data'    => []
                ], 500);
            }

            $body = $response->body();

            if (str_contains($body, '<html') || str_contains($body, '<!DOCTYPE')) {
                return response()->json(['message' => 'Gagal: Google meminta login atau file tidak dipublish sebagai publik'], 403);
            }

            file_put_contents($tempPath, $body);
            $data = Excel::toCollection([], $tempPath);

            if ($data->isEmpty() || $data->first()->isEmpty()) {
                return response()->json(['data' => []]);
            }

            $sheet = $data->first();

            $header = $sheet->get(1);
            $header = $header ? $header->map(fn($h) => $h ? trim($h) : null) : collect();

            $result = $sheet->slice(3)->map(function ($row) use ($header) {
                return $row->take($header->count())->toArray();
            })->reject(function ($row) {
                if (empty(array_filter($row))) return true;
                return empty($row[3]) && empty($row[4]);
            })->values();

            return response()->json(['data' => $result]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error parsing Excel',
                'error'   => $e->getMessage(),
                'data'    => []
            ], 500);
        } finally {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
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
            return response()->json([
                'message' => 'Data berhasil disimpan',
                'data'    => $product->pltbb
            ]);
        } else {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
    }
}
