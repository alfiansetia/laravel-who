<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\StockServices;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $data = StockServices::getAll($request->location);
        return $this->sendResponse($data);
    }

    public function lot(Request $request, int $id)
    {
        $limit = 10;
        if ($request->filled('limit')) {
            $limit = intval($request->limit);
        }
        $locations = $request->location ?? [];
        $data = StockServices::lot($id, $locations, $limit);
        return $this->sendResponse($data);
    }

    public function opname(Request $request)
    {
        $loc = $request->location ?? '';
        $locations = array_filter(array_map('trim', explode(',', $loc)));
        if (count($locations) < 1) {
            return $this->sendError('Location is required', [], 400);
        }

        $templatePath = public_path("master/master_opname.xlsx");
        $spreadsheet = IOFactory::load($templatePath);
        $templateSheet = $spreadsheet->getSheet(0);

        $currentMonth = date('F Y');
        $currentDate = date('d F Y');
        $currentTime = date('H:i:s');
        $cdakb = config('cdakb.opname');

        foreach ($locations as $loc_name) {
            $data = StockServices::getAll([$loc_name]);

            // Clone template untuk sheet baru
            $currentSheet = clone $templateSheet;

            // Set Judul Sheet (Limit 31 Karakter & Sanitasi)
            $real_name = StockServices::getLocationAlias($loc_name);
            $safeTitle = str_replace(['*', ':', '/', '\\', '?', '[', ']'], '', $real_name);
            $currentSheet->setTitle(substr($safeTitle, 0, 31));

            $spreadsheet->addSheet($currentSheet);

            // === HEADER INFO ===
            $currentSheet->setCellValue('C4', ': ' . $currentMonth);
            $currentSheet->setCellValue('C5', ': ' . $real_name);
            $currentSheet->setCellValue('C6', ': ' . $currentDate);

            // === ITEMS LOGIC ===
            $startRow = 9;
            $itemsCount = count($data);
            $minRows = 30;
            $totalLines = max($itemsCount, $minRows);

            // Row 9 adalah base style
            $baseStyle = $currentSheet->getStyle("A{$startRow}:I{$startRow}");
            $baseRowHeight = $currentSheet->getRowDimension($startRow)->getRowHeight();

            // Masukkan baris baru jika lebih dari 1 baris dibutuhkan
            if ($totalLines > 1) {
                $currentSheet->insertNewRowBefore($startRow + 1, $totalLines - 1);
            }

            // Terapkan style ke seluruh range tabel sekaligus (Row 9 hingga baris terakhir)
            // Ini jauh lebih stabil untuk menduplikasi alignment tiap kolom secara tepat.
            $lastRow = $startRow + $totalLines - 1;
            $currentSheet->duplicateStyle($baseStyle, "A{$startRow}:I{$lastRow}");

            // Loop hanya untuk mengisi data dan mengatur tinggi baris
            for ($i = 0; $i < $totalLines; $i++) {
                $currentRow = $startRow + $i;
                $currentSheet->getRowDimension($currentRow)->setRowHeight(-1);

                if ($i < $itemsCount) {
                    $item = $data[$i];
                    $currentSheet->setCellValue("A{$currentRow}", $i + 1);
                    $currentSheet->setCellValue("B{$currentRow}", $item['code']);
                    $currentSheet->setCellValue("C{$currentRow}", $item['name']);
                    $currentSheet->setCellValue("E{$currentRow}", 'EA');
                    $currentSheet->setCellValue("F{$currentRow}", $item['quantity']);

                    $currentSheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $currentSheet->getStyle("B{$currentRow}")->getAlignment()->setWrapText(true);
                    $currentSheet->getStyle("C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $currentSheet->getStyle("C{$currentRow}")->getAlignment()->setWrapText(true);
                } else {
                    // Baris kosong (style border dan alignment sudah terduplikasi di atas)
                    $currentSheet->setCellValue("A{$currentRow}", "");
                    $currentSheet->setCellValue("B{$currentRow}", "");
                    $currentSheet->setCellValue("C{$currentRow}", "");
                    $currentSheet->setCellValue("D{$currentRow}", "");
                    $currentSheet->setCellValue("E{$currentRow}", "");
                    $currentSheet->setCellValue("F{$currentRow}", "");
                }
            }

            // CDAKB Logic: Default di I19 akan terdorong otomatis ke bawah
            // Karena kita insert ($totalLines - 1) baris di atasnya
            $cdakb_row_idx = 19 + ($totalLines - 1);
            $cdakb_cell = "I{$cdakb_row_idx}";

            $currentSheet->setCellValue($cdakb_cell, $cdakb);
            $currentSheet->getStyle($cdakb_cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        // Hapus sheet template asli jika ada setidaknya satu sheet baru yang ditambahkan
        if ($spreadsheet->getSheetCount() > 1) {
            $spreadsheet->removeSheetByIndex(0);
        }


        $filename = "Stock Opname {$currentDate} {$currentTime}.xlsx";
        $outputPath = storage_path("app/temp/" . $filename);
        if (!file_exists(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0775, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($outputPath);

        return response()->download($outputPath)->deleteFileAfterSend();
    }
}
