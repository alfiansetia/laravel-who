<?php

namespace App\Http\Controllers;

use App\Http\Resources\AtkResource;
use App\Models\Atk;
use App\Models\AtkTransaction;
use App\Services\Breadcrumb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class AtkController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $perPage = min((int) $request->input('per_page', 25), 200);
            $page = max((int) $request->input('page', 1), 1);

            $query = Atk::query()->select('atks.*')->selectRaw('COALESCE(s.stok, 0) AS stok')
                ->leftJoinSub(
                    AtkTransaction::query()
                        ->select('atk_id')
                        ->selectRaw("COALESCE(SUM(CASE WHEN type = 'in' THEN qty ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN type = 'out' THEN qty ELSE 0 END), 0) AS stok")
                        ->groupBy('atk_id'),
                    's',
                    's.atk_id',
                    '=',
                    'atks.id'
                );

            // search
            if ($request->filled('search')) {
                $keyword = $request->search;
                $query->where(function ($q) use ($keyword) {
                    $q->where('code', 'like', "%{$keyword}%")
                        ->orWhere('name', 'like', "%{$keyword}%")
                        ->orWhere('satuan', 'like', "%{$keyword}%")
                        ->orWhere('desc', 'like', "%{$keyword}%");
                });
            }

            // satuan filter
            if ($request->filled('satuan')) {
                $query->where('satuan', $request->satuan);
            }

            // stok filter: kosong = 0, ada = > 0
            if ($request->stok === 'kosong') {
                $query->whereRaw('COALESCE(s.stok, 0) = 0');
            } elseif ($request->stok === 'ada') {
                $query->whereRaw('COALESCE(s.stok, 0) > 0');
            }

            $total = (clone $query)->count();
            $data = $query->orderBy('code', 'asc')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

            return response()->json([
                'data' => $data,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => (int) ceil($total / $perPage),
            ]);
        }

        return Inertia::render('Atk/Index', [
            'title' => 'Data ATK',
            'filters' => $request->only(['search', 'page', 'satuan', 'stok']),
            'satuanList' => ['pcs', 'dus', 'pack', 'kotak', 'lusin', 'pad', 'rim', 'roll', 'tube', 'box', 'buah', 'buku'],
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|max:200|unique:atks,code',
            'name' => 'required|max:200',
            'satuan' => 'required|max:200',
            'desc' => 'nullable|max:200',
        ]);
        $atk = Atk::create([
            'code' => $request->code,
            'name' => $request->name,
            'satuan' => $request->satuan,
            'desc' => $request->desc,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $atk,
                'message' => 'Success Insert Data',
            ], 200);
        }

        return redirect()->back()->with('success', 'Success Insert Data');
    }

    public function show(Request $request, Atk $atk)
    {
        if ($request->wantsJson()) {
            return response()->json(['data' => new AtkResource($atk->load('transactions'))], 200);
        }

        return redirect()->back();
    }

    public function update(Request $request, Atk $atk)
    {
        $this->validate($request, [
            'code' => 'required|max:200|unique:atks,code,'.$atk->id,
            'name' => 'required|max:200',
            'satuan' => 'required|max:200',
            'desc' => 'nullable|max:200',
        ]);
        $atk->update([
            'code' => $request->code,
            'name' => $request->name,
            'satuan' => $request->satuan,
            'desc' => $request->desc,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $atk,
                'message' => 'Success Update Data',
            ], 200);
        }

        return redirect()->back()->with('success', 'Success Update Data');
    }

    public function destroy(Request $request, Atk $atk)
    {
        $atk->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $atk,
                'message' => 'Success Delete Data',
            ], 200);
        }

        return redirect()->back()->with('success', 'Success Delete Data');
    }

    public function destroy_batch(Request $request)
    {
        $this->validate($request, [
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:atks,id',
        ]);
        $deleted = Atk::whereIn('id', $request->ids)->delete();

        if ($request->wantsJson()) {
            return $this->sendResponse(['deleted_count' => $deleted], 'Atk deleted successfully.');
        }

        return redirect()->back()->with('success', 'Atk deleted successfully.');
    }

    public function import(Request $request)
    {
        $satuanAllowed = ['pcs', 'dus', 'pack', 'kotak', 'lusin', 'pad', 'rim', 'roll', 'tube', 'box', 'buah', 'buku'];

        if ($request->wantsJson() || $request->isMethod('post')) {
            $this->validate($request, [
                'data' => 'required|array|min:1',
                'data.*' => 'required|array',
                'data.*.code' => 'required|string|max:200',
                'data.*.name' => 'required|string|max:200',
                'data.*.satuan' => 'required|string|max:200',
            ], [
                'data.required' => 'Tidak ada data untuk diimport.',
                'data.array' => 'Format data tidak valid.',
                'data.min' => 'Tidak ada data untuk diimport.',
                'data.*.array' => 'Format baris tidak valid.',
                'data.*.code.required' => 'Kode wajib diisi.',
                'data.*.code.max' => 'Kode maksimal 200 karakter.',
                'data.*.name.required' => 'Nama wajib diisi.',
                'data.*.name.max' => 'Nama maksimal 200 karakter.',
                'data.*.satuan.required' => 'Satuan wajib diisi.',
                'data.*.satuan.max' => 'Satuan maksimal 200 karakter.',
            ]);

            $rows = collect($request->input('data', []))->map(function ($item) {
                return [
                    'code' => trim((string) ($item['code'] ?? '')),
                    'name' => trim((string) ($item['name'] ?? '')),
                    'satuan' => strtolower(trim((string) ($item['satuan'] ?? ''))),
                ];
            })->filter(function ($item) {
                return $item['code'] !== '' || $item['name'] !== '' || $item['satuan'] !== '';
            })->values();

            if ($rows->isEmpty()) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'message' => 'Tidak ada data untuk diimport.',
                        'errors' => ['data' => ['Tidak ada data untuk diimport.']],
                    ], 422);
                }

                return redirect()->back()->with('error', 'Tidak ada data untuk diimport.');
            }

            $errors = [];
            foreach ($rows as $i => $row) {
                $line = $i + 1;
                if ($row['code'] === '') {
                    $errors["data.{$i}.code"] = ["Baris {$line}: kode wajib diisi."];
                }
                if ($row['name'] === '') {
                    $errors["data.{$i}.name"] = ["Baris {$line}: nama wajib diisi."];
                }
                if ($row['satuan'] === '') {
                    $errors["data.{$i}.satuan"] = ["Baris {$line}: satuan wajib diisi."];
                } elseif (! in_array($row['satuan'], $satuanAllowed, true)) {
                    $errors["data.{$i}.satuan"] = ["Baris {$line}: satuan '{$row['satuan']}' tidak dikenal. Pilihan: ".implode(', ', $satuanAllowed).'.'];
                }
            }

            if ($errors !== []) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'message' => 'Validasi gagal: '.count($errors).' baris bermasalah.',
                        'errors' => $errors,
                    ], 422);
                }

                return redirect()->back()->with('error', 'Validasi gagal: '.count($errors).' baris bermasalah.');
            }

            DB::beginTransaction();
            try {
                $created = 0;
                $updated = 0;
                foreach ($rows as $row) {
                    $atk = Atk::query()->updateOrCreate(
                        ['code' => $row['code']],
                        ['code' => $row['code'], 'name' => $row['name'], 'satuan' => $row['satuan']]
                    );
                    if ($atk->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }
                }
                DB::commit();

                $message = "Import selesai: {$created} baru, {$updated} diperbarui dari {$rows->count()} baris.";

                if ($request->wantsJson()) {
                    return response()->json([
                        'data' => ['imported' => $rows->count(), 'created' => $created, 'updated' => $updated],
                        'message' => $message,
                    ]);
                }

                return redirect()->back()->with('success', $message);
            } catch (\Throwable $th) {
                DB::rollBack();

                if ($request->wantsJson()) {
                    return $this->sendError($th->getMessage());
                }

                return redirect()->back()->with('error', $th->getMessage());
            }
        }

        return Inertia::render('Atk/Import', [
            'title' => 'Import Data ATK',
            'satuanList' => $satuanAllowed,
        ]);
    }

    public function trxIndex(Request $request)
    {
        $query = AtkTransaction::with('atk');
        if ($request->filled('atk_id')) {
            $query->where('atk_id', $request->atk_id);
        }
        $data = $query->orderBy('date', 'asc')->orderBy('id', 'asc')->get();

        if ($request->wantsJson()) {
            return response()->json(['data' => $data], 200);
        }

        return redirect()->back();
    }

    public function trxStore(Request $request)
    {
        $this->validate($request, [
            'atk_id' => 'required|exists:atks,id',
            'date' => 'required|date_format:Y-m-d',
            'pic' => 'required|max:200',
            'type' => 'required|in:in,out',
            'qty' => 'required|integer|min:1',
            'desc' => 'nullable|max:200',
        ]);
        $delta = $request->type === 'in' ? (int) $request->qty : -(int) $request->qty;
        if ($message = $this->stockViolation((int) $request->atk_id, $delta)) {
            return $this->stockErrorResponse($request, $message);
        }
        $trx = AtkTransaction::create($request->only([
            'atk_id',
            'date',
            'pic',
            'type',
            'qty',
            'desc',
        ]));

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $trx,
                'message' => 'Success Insert Data',
            ], 200);
        }

        return redirect()->back()->with('success', 'Success Insert Data');
    }

    public function trxShow(Request $request, $id)
    {
        $trx = AtkTransaction::with('atk')->find($id);
        if (! $trx) {
            if ($request->wantsJson()) {
                return response()->json([
                    'data' => null,
                    'message' => 'Data Not Found!',
                ], 404);
            }

            return redirect()->back()->with('error', 'Data Not Found!');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $trx,
                'message' => '',
            ]);
        }

        return redirect()->back();
    }

    public function trxUpdate(Request $request, $id)
    {
        $trx = AtkTransaction::with('atk')->find($id);
        if (! $trx) {
            if ($request->wantsJson()) {
                return response()->json([
                    'data' => null,
                    'message' => 'Data Not Found!',
                ], 404);
            }

            return redirect()->back()->with('error', 'Data Not Found!');
        }
        $this->validate($request, [
            'atk_id' => 'required|exists:atks,id',
            'date' => 'required|date_format:Y-m-d',
            'pic' => 'required|max:200',
            'type' => 'required|in:in,out',
            'qty' => 'required|integer|min:1',
            'desc' => 'nullable|max:200',
        ]);
        $newEffect = $request->type === 'in' ? (int) $request->qty : -(int) $request->qty;
        $oldEffect = $trx->type === 'in' ? (int) $trx->qty : -(int) $trx->qty;
        if ((int) $request->atk_id === (int) $trx->atk_id) {
            if ($message = $this->stockViolation((int) $request->atk_id, $newEffect, $trx)) {
                return $this->stockErrorResponse($request, $message);
            }
        } else {
            if ($message = $this->stockViolation((int) $request->atk_id, $newEffect)) {
                return $this->stockErrorResponse($request, $message);
            }
            if ($message = $this->stockViolation((int) $trx->atk_id, -$oldEffect)) {
                return $this->stockErrorResponse($request, $message);
            }
        }
        $trx->update($request->only([
            'atk_id',
            'date',
            'pic',
            'type',
            'qty',
            'desc',
        ]));

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $trx,
                'message' => 'Success Update Data',
            ], 200);
        }

        return redirect()->back()->with('success', 'Success Update Data');
    }

    public function trxDestroy(Request $request, $id)
    {
        $trx = AtkTransaction::with('atk')->find($id);
        if (! $trx) {
            if ($request->wantsJson()) {
                return response()->json([
                    'data' => null,
                    'message' => 'Data Not Found!',
                ], 404);
            }

            return redirect()->back()->with('error', 'Data Not Found!');
        }
        $trx->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $trx,
                'message' => 'Success Delete Data',
            ], 200);
        }

        return redirect()->back()->with('success', 'Success Delete Data');
    }

    /**
     * Cek stok akhir tidak minus setelah mutasi.
     * $delta = efek bersih (+in/-out). $ignore = transaksi yang sedang
     * diubah (efek lamanya dikeluarkan dulu dari perhitungan).
     * Return pesan error atau null bila aman.
     */
    private function stockViolation(int $atkId, int $delta, ?AtkTransaction $ignore = null): ?string
    {
        $atk = Atk::query()->find($atkId);
        if (! $atk) {
            return 'Data ATK tidak ditemukan.';
        }
        $current = (int) $atk->stok;
        $stok = $current;
        if ($ignore && (int) $ignore->atk_id === $atkId) {
            $stok -= $ignore->type === 'in' ? (int) $ignore->qty : -(int) $ignore->qty;
        }
        if ($stok + $delta < 0) {
            return "Stok tidak mencukupi (sisa {$current}).";
        }

        return null;
    }

    private function stockErrorResponse(Request $request, string $message)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => $message,
                'errors' => ['qty' => [$message]],
            ], 422);
        }

        return redirect()->back()->with('error', $message)->withInput();
    }

    /**
     * Matriks rekap tahunan: 1 query agregasi IN/OUT per bulan per item.
     * Kolom: id, code, name, in_01..in_12, out_01..out_12 (tahun berjalan),
     * all_in, all_out (seluruh histori, untuk sisa stok).
     */
    private function rekapMatrix(int $year)
    {
        $query = Atk::query()
            ->select('atks.id', 'atks.code', 'atks.name')
            ->leftJoin('atk_transactions as t', 't.atk_id', '=', 'atks.id')
            ->groupBy('atks.id', 'atks.code', 'atks.name')
            ->orderBy('atks.code', 'asc');
        for ($m = 1; $m <= 12; $m++) {
            $mm = sprintf('%02d', $m);
            $query->selectRaw("COALESCE(SUM(CASE WHEN YEAR(t.date) = {$year} AND MONTH(t.date) = {$m} AND t.type = 'in' THEN t.qty ELSE 0 END), 0) AS in_{$mm}");
            $query->selectRaw("COALESCE(SUM(CASE WHEN YEAR(t.date) = {$year} AND MONTH(t.date) = {$m} AND t.type = 'out' THEN t.qty ELSE 0 END), 0) AS out_{$mm}");
        }
        $query->selectRaw("COALESCE(SUM(CASE WHEN t.type = 'in' THEN t.qty ELSE 0 END), 0) AS all_in");
        $query->selectRaw("COALESCE(SUM(CASE WHEN t.type = 'out' THEN t.qty ELSE 0 END), 0) AS all_out");

        return $query->get();
    }

    public function eksportGlobal(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $year = max(2000, min(2100, $year));

        $shortMonths = ['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGU', 'SEP', 'OKT', 'NOV', 'DES'];

        // 1 query: agregasi IN/OUT per bulan langsung di SQL (tanpa N+1, tanpa loop PHP).
        $items = $this->rekapMatrix($year);

        return view('atk.export-global', compact('items', 'shortMonths', 'year'))
            ->with(['title' => 'Rekap Stok ATK '.$year]);
    }

    public function eksportGlobalXlsx(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $year = max(2000, min(2100, $year));

        $shortMonths = ['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGU', 'SEP', 'OKT', 'NOV', 'DES'];
        $items = $this->rekapMatrix($year);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Rekap {$year}");

        // Header 2 baris: Kode | Nama | JAN..DES (masing-masing IN, OUT).
        $sheet->setCellValue('A1', 'Kode');
        $sheet->setCellValue('B1', 'Nama');
        $sheet->mergeCells('A1:A2');
        $sheet->mergeCells('B1:B2');
        foreach ($shortMonths as $i => $mon) {
            $colIn = Coordinate::stringFromColumnIndex(3 + $i * 2);
            $colOut = Coordinate::stringFromColumnIndex(4 + $i * 2);
            $sheet->setCellValue("{$colIn}1", $mon);
            $sheet->mergeCells("{$colIn}1:{$colOut}1");
            $sheet->setCellValue("{$colIn}2", 'IN');
            $sheet->setCellValue("{$colOut}2", 'OUT');
        }
        $sheet->setCellValue('AA1', 'TOTAL IN');
        $sheet->setCellValue('AB1', 'TOTAL OUT');
        $sheet->setCellValue('AC1', 'SISA');
        $sheet->mergeCells('AA1:AA2');
        $sheet->mergeCells('AB1:AB2');
        $sheet->mergeCells('AC1:AC2');

        $row = 3;
        foreach ($items as $item) {
            $sheet->setCellValue("A{$row}", $item->code);
            $sheet->setCellValue("B{$row}", $item->name);
            $inParts = [];
            $outParts = [];
            for ($m = 1; $m <= 12; $m++) {
                $mm = sprintf('%02d', $m);
                $colIn = Coordinate::stringFromColumnIndex(1 + $m * 2);
                $colOut = Coordinate::stringFromColumnIndex(2 + $m * 2);
                $in = (int) $item->{"in_{$mm}"};
                $out = (int) $item->{"out_{$mm}"};
                $sheet->setCellValue("{$colIn}{$row}", $in === 0 ? null : $in);
                $sheet->setCellValue("{$colOut}{$row}", $out === 0 ? null : $out);
                if ($in !== 0) {
                    $inParts[] = $in;
                }
                if ($out !== 0) {
                    $outParts[] = $out;
                }
            }
            // TOTAL pakai rumus literal (=10+2) agar terlihat jejak penjumlahannya.
            $sheet->setCellValue("AA{$row}", $inParts === [] ? null : '='.implode('+', $inParts));
            $sheet->setCellValue("AB{$row}", $outParts === [] ? null : '='.implode('+', $outParts));
            $sheet->setCellValue("AC{$row}", (int) $item->all_in - (int) $item->all_out);
            $row++;
        }

        $lastCol = 'AC';
        $lastRow = max($row - 1, 2);

        $sheet->getStyle("A1:{$lastCol}{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A1:{$lastCol}2")->getFont()->setBold(true);
        $sheet->getStyle("A1:{$lastCol}2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("C3:{$lastCol}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(32);
        for ($c = 3; $c <= 29; $c++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth(9);
        }
        $sheet->getColumnDimension('AA')->setWidth(11);
        $sheet->getColumnDimension('AB')->setWidth(11);
        $sheet->getColumnDimension('AC')->setWidth(10);
        $sheet->freezePane('C3');
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)->setPaperSize(PageSetup::PAPERSIZE_A4)->setFitToWidth(1)->setFitToHeight(0);

        $base = tempnam(sys_get_temp_dir(), 'rekap_atk_');
        $out = $base.'.xlsx';
        @unlink($base);
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($out);
        $spreadsheet->disconnectWorksheets();

        return response()->download($out, "rekap_atk_{$year}.xlsx", [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function eksport(Atk $atk)
    {
        $bcms = collect([
            new Breadcrumb('List ATK', route('atk.index'), true),
            new Breadcrumb('Eksport ATK', route('atk.eksport', $atk->id), false),
        ]);
        // $data = Atk::with('transactions')->get();
        $data = $atk->load('transactions');

        return view('atk.export', compact('data', 'bcms'))->with(['title' => 'Kartu Stok - '.$data->code]);
    }
}
