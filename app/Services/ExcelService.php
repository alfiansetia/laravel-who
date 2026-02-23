<?php

namespace App\Services;

use App\Models\Pack;
use App\Models\Sop;
use App\Models\Product;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Str;

class ExcelService
{
    /**
     * Generate Excel for a Single Pack
     */
    public function generatePack(Pack $pack, $filename = null)
    {
        $pack->load(['vendor', 'product', 'items']);

        $templatePath = public_path("master/master_pack.xlsx");
        if (!file_exists($templatePath)) {
            throw new \Exception("Template master_pack.xlsx not found.");
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $name   = $pack->vendor->name ?? '';
        $desc   = $pack->vendor_desc ? " ({$pack->vendor_desc})" : '';
        $vendor = "Pabrikan : {$name}{$desc}";

        $code   = $pack->product->code ?? '';
        $pname  = $pack->product->name ?? '';
        $pdesc  = $pack->desc ? " ({$pack->desc})" : '';
        $productTitle = "Produk : {$code} {$pname}{$pdesc}";

        // === HEADER INFO ===
        $sheet->setCellValue('B4', $vendor);
        $sheet->setCellValue('B5', $productTitle);

        // === ITEMS ===
        $startRow = 8;
        $row = $startRow;

        $baseStyle = $sheet->getStyle("B{$startRow}:E{$startRow}");
        $baseRowHeight = $sheet->getRowDimension($startRow)->getRowHeight();

        foreach ($pack->items as $index => $item) {
            if ($row > $startRow) {
                $sheet->duplicateStyle($baseStyle, "B{$row}:E{$row}");
                $sheet->getRowDimension($row)->setRowHeight($baseRowHeight);
            }
            $sheet->setCellValue("B{$row}", $index + 1);
            $sheet->setCellValue("C{$row}", $item->item);
            $sheet->setCellValue("D{$row}", $item->qty);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        $cdakb = config('cdakb.pack');
        $cdakb_row = "E{$row}";
        if ($pack->items->count() <= 3) {
            $cdakb_row = "E11";
        }
        $sheet->setCellValue($cdakb_row, $cdakb);
        $sheet->getStyle($cdakb_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        if (!$filename) {
            $file = $code . ($pdesc ?: '');
            $file_name = preg_replace('/[^A-Za-z0-9_.\-+()]/', '-', $file);
            $filename = "{$file_name}-PL.xlsx";
        }

        $outputPath = storage_path("app/temp/" . $filename);
        $outputDir = dirname($outputPath);
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($outputPath);

        return $outputPath;
    }

    /**
     * Generate Excel for SOP(s)
     * Supports multiple SOPs in multiple sheets
     */
    public function generateSop(Product $product, $filename = null)
    {
        // Get all SOPs for this product
        $sops = Sop::where('product_id', $product->id)->with(['items'])->get();

        if ($sops->isEmpty()) {
            return null;
        }

        $templatePath = public_path("master/master_sop.xlsx");
        if (!file_exists($templatePath)) {
            throw new \Exception("Template master_sop.xlsx not found.");
        }

        $spreadsheet = IOFactory::load($templatePath);

        foreach ($sops as $index => $sop) {
            if ($index == 0) {
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle('SOP 1');
            } else {
                $sheet = clone $spreadsheet->getSheet(0);
                $sheet->setTitle('SOP ' . ($index + 1));
                $spreadsheet->addSheet($sheet);
            }

            $product_code  = 'Kode barang : ' . ($product->code ?? '');
            $product_name  = 'Nama barang : ' . ($product->name ?? '');
            $target  = 'Target : ' . ($sop->target ?? '');

            // === HEADER INFO ===
            $sheet->setCellValue('B4', $product_code);
            $sheet->setCellValue('B5', $product_name);
            $sheet->setCellValue('B6', $target);

            // === ITEMS ===
            $startRow = 9;
            $row = $startRow;

            $baseStyle = $sheet->getStyle("B{$startRow}:C{$startRow}");
            $baseRowHeight = $sheet->getRowDimension($startRow)->getRowHeight();

            foreach ($sop->items as $iIndex => $item) {
                if ($row > $startRow) {
                    $sheet->duplicateStyle($baseStyle, "B{$row}:C{$row}");
                    $sheet->getRowDimension($row)->setRowHeight($baseRowHeight);
                }
                $sheet->setCellValue("B{$row}", $iIndex + 1);
                $sheet->setCellValue("C{$row}", $item->item);
                $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $row++;
            }
        }

        if (!$filename) {
            $file_name = preg_replace('/[^A-Za-z0-9_.\-+()]/', '-', ($product->code ?? ''));
            $filename = "{$file_name}-SOP.xlsx";
        }

        $outputPath = storage_path("app/temp/" . $filename);
        $outputDir = dirname($outputPath);
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($outputPath);

        return $outputPath;
    }
}
