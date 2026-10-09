<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\PltbbServices;
use App\Services\TikiServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ToolController extends Controller
{
    public function stt(Request $request): Response
    {
        return Inertia::render('Stt/Index', [
            'title' => 'Speech To Text',
        ]);
    }

    public function kalkulator(Request $request): Response
    {
        return Inertia::render('Kalkulator/Index', [
            'title' => 'Kalkulator Nilai',
        ]);
    }

    public function laporan_pengiriman(Request $request): Response
    {
        return Inertia::render('LaporanPengiriman/Index', [
            'title' => 'Laporan Pengiriman',
        ]);
    }

    public function laporan_luarkota(Request $request): Response
    {
        return Inertia::render('LaporanLuarkota/Index', [
            'title' => 'Laporan Luarkota',
        ]);
    }

    public function index(): Response
    {
        return Inertia::render('Sn/Index', [
            'title' => 'SN Tools',
        ]);
    }

    public function scoreboard(): Response
    {
        return Inertia::render('Scoreboard/Index', [
            'title' => 'Scoreboard',
        ]);
    }

    public function ocr(): Response
    {
        return Inertia::render('Ocr/Index', [
            'title' => 'OCR Tool',
        ]);
    }

    public function spreadsheet(): Response
    {
        return Inertia::render('Spreadsheet/Index', [
            'title' => 'Spreadsheet PLTBB',
        ]);
    }

    public function print_resi(): Response
    {
        return Inertia::render('PrintResi/Index', [
            'title' => 'Print Resi',
        ]);
    }

    public function spreadsheetIndex(): JsonResponse
    {
        try {
            $data = PltbbServices::get();

            return response()->json([
                'message' => 'Data berhasil diambil',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error get data',
                'error' => $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    public function spreadsheetSyncAll(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = PltbbServices::get();
            $updatedCount = 0;
            $updatedData = [];
            foreach ($data as $item) {
                $code = $item[3] ?? null;
                $p = parseDecimal($item[8] ?? 0);
                $l = parseDecimal($item[9] ?? 0);
                $t = parseDecimal($item[10] ?? 0);
                $b = parseDecimal($item[11] ?? 0);
                $note = $item[12] ?? null;
                if (empty($code)) {
                    continue;
                }
                $product = Product::where('code', $code)->first();
                if ($product) {
                    $product->pltbb()->updateOrCreate([
                        'product_id' => $product->id,
                    ], [
                        'p' => $p,
                        'l' => $l,
                        't' => $t,
                        'b' => $b,
                        'note' => $note,
                    ]);
                    $updatedCount++;
                }
            }
            DB::commit();

            return response()->json([
                'message' => 'Data berhasil sinkronisasi '.$updatedCount.' data!',
                'data' => $updatedData,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error get data',
                'error' => $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    public function spreadsheetSyncProduct(Request $request): JsonResponse
    {
        $this->validate($request, [
            'code' => 'required',
            'p' => 'required|decimal:2',
            'l' => 'required|decimal:2',
            't' => 'required|decimal:2',
            'b' => 'required|decimal:2',
            'note' => 'nullable',
        ]);
        $code = $request->code;
        $p = (float) $request->p;
        $l = (float) $request->l;
        $t = (float) $request->t;
        $b = (float) $request->b;
        $note = $request->note;

        $product = Product::where('code', $code)->first();
        if (! $product) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
        $product->pltbb()->updateOrCreate([
            'product_id' => $product->id,
        ], [
            'p' => $p,
            'l' => $l,
            't' => $t,
            'b' => $b,
            'note' => $note,
        ]);

        return response()->json([
            'message' => 'Data berhasil disimpan',
            'data' => $product->pltbb,
        ]);
    }

    /**
     * GET /api/tiki/track?resi=660108012346,660108011392
     *
     * Accepts one or more comma-separated connote numbers and
     * proxies the request to the TIKI tracking API.
     */
    public function tikiTrack(Request $request): JsonResponse
    {
        $request->validate([
            'resi' => 'required|string|max:1000',
        ]);

        $resi = $request->input('resi');

        $result = TikiServices::track($resi);

        if ($result === null) {
            return $this->sendResponse(null, 'Gagal mengambil data tracking dari TIKI', 502);
        }

        return $this->sendResponse($result);
    }
}
