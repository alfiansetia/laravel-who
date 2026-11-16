<?php

namespace App\Services\Import;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CekImportService
{
    public const START_ROW = 15;

    public const END_ROW = 38;

    /**
     * Bangun file cek import dari template master.
     *
     * @param  array<int, array{code: string, name: string, qty: float|int}>  $items
     */
    public static function build(string $poName, string $riName, string $vendorName, string $vendorAddress, array $items): string
    {
        $templatePath = public_path('master/import/cek_import.xlsx');
        $spreadsheet = IOFactory::load($templatePath);

        $importSheet = $spreadsheet->getSheet(0);
        $kartonSheet = self::kartonSheet($spreadsheet) ?? $spreadsheet->getSheet(1);

        foreach ([$importSheet, $kartonSheet] as $sheet) {
            $sheet->setCellValue('A5', 'PO : '.trim($poName));
            $sheet->setCellValue('E5', 'RI : '.trim($riName));
            $sheet->setCellValue('C9', ': '.self::dash($vendorName));
            $sheet->setCellValue('C10', ': '.self::dash($vendorAddress));
        }

        $count = count($items);
        $capacity = self::END_ROW - self::START_ROW + 1;

        if ($count > $capacity) {
            $baseStyle = $importSheet->getStyle('A'.self::START_ROW.':F'.self::START_ROW);
            $importSheet->insertNewRowBefore(self::END_ROW + 1, $count - $capacity);
            $importSheet->duplicateStyle($baseStyle, 'A'.self::START_ROW.':F'.(self::END_ROW + $count - $capacity));
        }

        foreach (array_values($items) as $i => $item) {
            $row = self::START_ROW + $i;
            $importSheet->setCellValue("A{$row}", $i + 1);
            $importSheet->getCell("B{$row}")->setValueExplicit((string) ($item['code'] ?? ''), DataType::TYPE_STRING);
            $importSheet->getCell("C{$row}")->setValueExplicit((string) ($item['name'] ?? ''), DataType::TYPE_STRING);
            $importSheet->setCellValue("D{$row}", $item['qty'] ?? null);
            $importSheet->setCellValue("E{$row}", null);
            $importSheet->setCellValue("F{$row}", null);
        }

        $filename = self::filename($poName, $vendorName);
        $outputPath = storage_path('app/temp/'.$filename);
        if (! file_exists(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0775, true);
        }

        (new Xlsx($spreadsheet))->save($outputPath);
        $spreadsheet->disconnectWorksheets();

        return $outputPath;
    }

    public static function filename(string $poName, string $vendorName): string
    {
        $sanitize = function (string $v): string {
            $v = (string) preg_replace('/[\x00-\x1f\/\\\\:*?"<>|]+/', '_', $v);
            $v = trim((string) preg_replace('/\s+/', ' ', $v));

            return trim($v, ' .');
        };

        $po = $sanitize($poName);
        $vendor = $sanitize($vendorName);

        return ($po !== '' ? $po : '-').' - ('.($vendor !== '' ? $vendor : '-').').xlsx';
    }

    /**
     * Pecah label Odoo "[KODE] Nama Barang" menjadi [kode, nama].
     *
     * @return array{0: string, 1: string}
     */
    public static function splitProduct(mixed $productId): array
    {
        $label = is_array($productId) ? ($productId[1] ?? '') : (string) $productId;
        if (preg_match('/\[(.*?)\]\s*(.*)/', (string) $label, $m)) {
            return [trim($m[1]), trim($m[2])];
        }

        return ['', trim((string) $label)];
    }

    /**
     * Susun alamat vendor dari partner_detail Odoo; '-' bila kosong/false.
     */
    public static function composeAddress(?array $partner): string
    {
        if (! $partner) {
            return '-';
        }
        $parts = [];
        foreach (['street', 'street2', 'city', 'zip'] as $key) {
            $v = trim((string) ($partner[$key] ?? ''));
            if ($v !== '' && strtolower($v) !== 'false') {
                $parts[] = $v;
            }
        }
        foreach (['state_id', 'country_id'] as $key) {
            $v = $partner[$key] ?? null;
            $name = is_array($v) ? ($v[1] ?? '') : $v;
            $name = trim((string) $name);
            if ($name !== '' && strtolower($name) !== 'false') {
                $parts[] = $name;
            }
        }

        return $parts === [] ? '-' : implode(', ', $parts);
    }

    public static function vendorName(?array $partnerDetail, mixed $partnerId): string
    {
        $name = trim((string) ($partnerDetail['display_name'] ?? $partnerDetail['name'] ?? ''));
        if ($name !== '' && strtolower($name) !== 'false') {
            return $name;
        }
        $fallback = is_array($partnerId) ? ($partnerId[1] ?? '') : $partnerId;

        return trim((string) $fallback) !== '' ? trim((string) $fallback) : '-';
    }

    private static function kartonSheet(Spreadsheet $spreadsheet): ?Worksheet
    {
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            if (trim((string) $sheet->getTitle()) === 'cek by karton') {
                return $sheet;
            }
        }

        return null;
    }

    private static function dash(string $value): string
    {
        $value = trim($value);

        return $value !== '' && strtolower($value) !== 'false' ? $value : '-';
    }
}
