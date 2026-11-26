<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\PltbbServices;
use App\Services\SuhuDocService;
use App\Services\TikiServices;
use App\Services\XlsDrawingCropService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ToolController extends Controller
{
    public function __construct()
    {
        // Tulis massal pltbb wajib login; baca + halaman Inertia tetap publik.
        $this->middleware('env_auth')->only(['spreadsheetSyncAll', 'spreadsheetSyncProduct']);
    }

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

    public function barcodeReader(): Response
    {
        return Inertia::render('BarcodeReader/Index', [
            'title' => 'Barcode / QR Reader',
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

    public function tempelanDus(): Response
    {
        return Inertia::render('TempelanDus/Index', [
            'title' => 'Tempelan Dus',
            // Katalog kecil (ribuan baris) → preload sekali, pencarian filter lokal.
            'products' => Product::orderBy('code')->get(['id', 'code', 'name', 'akl']),
        ]);
    }

    public function tempelanAklUnit(): Response
    {
        return Inertia::render('TempelanAklUnit/Index', [
            'title' => 'Tempelan AKL Unit',
            // Katalog kecil (ribuan baris) → preload sekali, pencarian filter lokal.
            'products' => Product::orderBy('code')->get(['id', 'code', 'name', 'akl']),
        ]);
    }

    public function tempelanAklKemasan(): Response
    {
        return Inertia::render('TempelanAklKemasan/Index', [
            'title' => 'Tempelan AKL Kemasan',
            // Katalog kecil (ribuan baris) → preload sekali, pencarian filter lokal.
            'products' => Product::orderBy('code')->get(['id', 'code', 'name', 'akl']),
        ]);
    }

    public function stikerKartuStok(): Response
    {
        return Inertia::render('StikerKartuStok/Index', [
            'title' => 'Stiker Kartu Stok',
            // Katalog kecil (ribuan baris) → preload sekali, pencarian filter lokal.
            'products' => Product::orderBy('code')->get(['id', 'code', 'name', 'akl']),
        ]);
    }

    public function printLabel(): Response
    {
        return Inertia::render('PrintLabel/Index', [
            'title' => 'Print Label',
            // Katalog kecil (ribuan baris) → preload sekali, pencarian filter lokal.
            'products' => Product::orderBy('code')->get(['id', 'code', 'name', 'akl']),
        ]);
    }

    public function suhu(Request $request): Response
    {
        return Inertia::render('Suhu/Index', [
            'title' => 'Form Ruangan',
            'gudangOptions' => [
                ['value' => 'gudang_a', 'label' => 'Gudang A'],
                ['value' => 'gudang_b', 'label' => 'Gudang B'],
            ],
            'lokasiCount' => [
                'gudang_a' => count(SuhuDocService::lokasiFor('gudang_a')),
                'gudang_b' => count(SuhuDocService::lokasiFor('gudang_b')),
            ],
            'defaults' => [
                'bulan' => (int) now()->format('n'),
                'tahun' => (int) now()->format('Y'),
            ],
        ]);
    }

    /**
     * GET /tools/suhu/download?gudang=gudang_a&bulan=11&tahun=2026
     *
     * Hasil .docx berisi satu halaman per lokasi gudang. Baris tanggal
     * hanya diisi hari kerja (Senin–Jumat), akhir pekan dilewati.
     */
    public function suhuDownload(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'gudang' => 'required|in:gudang_a,gudang_b',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
        ], [
            'gudang.required' => 'Gudang wajib dipilih.',
            'gudang.in' => 'Pilihan gudang tidak valid.',
            'bulan.required' => 'Bulan wajib dipilih.',
            'tahun.required' => 'Tahun wajib diisi.',
        ]);

        $path = (new SuhuDocService)->generate(
            $validated['gudang'],
            (int) $validated['bulan'],
            (int) $validated['tahun'],
            'PENCATATAN SUHU'
        );

        return response()->download(
            $path,
            SuhuDocService::fileName($validated['gudang'], (int) $validated['bulan'], (int) $validated['tahun'], 'PENCATATAN SUHU'),
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
        )->deleteFileAfterSend(true);
    }

    /**
     * GET /tools/suhu-refrigrator/download?bulan=11&tahun=2026
     *
     * Satu halaman tanpa gudang. Baris tanggal hanya diisi hari kerja
     * (Senin–Jumat), akhir pekan dilewati.
     */
    public function suhuRefrigeratorDownload(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
        ], [
            'bulan.required' => 'Bulan wajib dipilih.',
            'tahun.required' => 'Tahun wajib diisi.',
        ]);

        $path = (new SuhuDocService)->generateRefrigerator(
            (int) $validated['bulan'],
            (int) $validated['tahun']
        );

        return response()->download(
            $path,
            SuhuDocService::refrigeratorFileName((int) $validated['bulan'], (int) $validated['tahun']),
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
        )->deleteFileAfterSend(true);
    }

    /**
     * GET /tools/suhu-hama/download?gudang=gudang_a&bulan=11&tahun=2026
     *
     * Satu halaman per lokasi gudang, tanpa baris tanggal.
     */
    public function hamaDownload(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'gudang' => 'required|in:gudang_a,gudang_b',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
        ], [
            'gudang.required' => 'Gudang wajib dipilih.',
            'gudang.in' => 'Pilihan gudang tidak valid.',
            'bulan.required' => 'Bulan wajib dipilih.',
            'tahun.required' => 'Tahun wajib diisi.',
        ]);

        $path = (new SuhuDocService)->generateHama(
            $validated['gudang'],
            (int) $validated['bulan'],
            (int) $validated['tahun']
        );

        return response()->download(
            $path,
            SuhuDocService::hamaFileName($validated['gudang'], (int) $validated['bulan'], (int) $validated['tahun']),
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
        )->deleteFileAfterSend(true);
    }

    /**
     * GET /tools/suhu-pembersihan/download?gudang=gudang_a&bulan=11&tahun=2026
     *
     * Satu halaman per lokasi gudang. Sel tanggal akhir pekan dicap 'X'.
     */
    public function pembersihanDownload(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'gudang' => 'required|in:gudang_a,gudang_b',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
        ], [
            'gudang.required' => 'Gudang wajib dipilih.',
            'gudang.in' => 'Pilihan gudang tidak valid.',
            'bulan.required' => 'Bulan wajib dipilih.',
            'tahun.required' => 'Tahun wajib diisi.',
        ]);

        $path = (new SuhuDocService)->generatePembersihan(
            $validated['gudang'],
            (int) $validated['bulan'],
            (int) $validated['tahun']
        );

        return response()->download(
            $path,
            SuhuDocService::pembersihanFileName($validated['gudang'], (int) $validated['bulan'], (int) $validated['tahun']),
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
        )->deleteFileAfterSend(true);
    }

    public function palet(Request $request): Response
    {
        return Inertia::render('Palet/Index', [
            'title' => 'Pembersihan Palet',
            'gudangDefaults' => SuhuDocService::paletGudang(),
            'defaults' => [
                'tanggal' => now()->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * GET /tools/pembersihan/download?tanggal=2026-11-16&gudang_a=..&gudang_b=..&gudang_c=..
     *
     * 3 halaman (satu per gudang). $tgl diisi dd/mm/yyyy.
     */
    public function paletDownload(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'gudang_a' => 'nullable|string|max:100',
            'gudang_b' => 'nullable|string|max:100',
            'gudang_c' => 'nullable|string|max:100',
        ], [
            'tanggal.required' => 'Tanggal wajib dipilih.',
            'tanggal.date' => 'Format tanggal tidak valid.',
        ]);

        $tanggal = Carbon::parse($validated['tanggal']);
        $path = (new SuhuDocService)->generatePalet([
            'a' => (string) ($validated['gudang_a'] ?? ''),
            'b' => (string) ($validated['gudang_b'] ?? ''),
            'c' => (string) ($validated['gudang_c'] ?? ''),
        ], $tanggal);

        return response()->download(
            $path,
            SuhuDocService::paletFileName($tanggal),
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
        )->deleteFileAfterSend(true);
    }

    /**
     * POST /api/print-resi/convert
     *
     * Konversi .xls legacy (OLE/BIFF) ke .xlsx agar bisa diekstrak
     * gambarnya di browser (JSZip hanya bisa membaca .xlsx).
     * Gambar embedded dipertahankan oleh PhpSpreadsheet (Escher → xl/media).
     */
    public function printResiConvert(Request $request): BinaryFileResponse|JsonResponse
    {
        $request->validate([
            'file' => 'required|file|extensions:xls|max:20480',
        ], [
            'file.required' => 'File wajib diisi.',
            'file.extensions' => 'Hanya file .xls yang perlu dikonversi.',
            'file.max' => 'Ukuran file maksimal 20MB.',
        ]);

        $file = $request->file('file');

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'File .xls tidak bisa dibaca atau rusak.',
            ], 422);
        }

        // PhpSpreadsheet mengabaikan crop gambar .xls → potong pikselnya
        // dulu agar hasil .xlsx sesuai tampilan crop di Excel.
        (new XlsDrawingCropService)->applyCrop($file->getRealPath(), $spreadsheet);

        $base = tempnam(sys_get_temp_dir(), 'resi_');
        $out = $base.'.xlsx';
        @unlink($base);

        try {
            IOFactory::createWriter($spreadsheet, 'Xlsx')->save($out);
        } catch (\Throwable $e) {
            $spreadsheet->disconnectWorksheets();

            return response()->json([
                'message' => 'Gagal mengonversi file ke .xlsx.',
            ], 500);
        }

        $spreadsheet->disconnectWorksheets();

        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME).'.xlsx';

        return response()->download($out, $name, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
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
