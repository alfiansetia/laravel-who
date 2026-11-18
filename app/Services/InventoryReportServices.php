<?php

namespace App\Services;

use App\Services\Odoo\OdooClient;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryReportServices
{
    private const QUANT_FIELDS = [
        'product_id', 'location_id', 'lot_id', 'itds_expired',
        'package_id', 'owner_id', 'reserved_quantity', 'quantity',
        'product_uom_id', 'write_date', 'company_id',
    ];

    public function __construct(private OdooClient $odoo) {}

    /** Cache ID lokasi GA/Stock per request (hindari lookup berulang). */
    private ?array $gaStockLocationIds = null;

    /**
     * Domain dasar: lokasi internal + opsional exclude GA/Stock.
     *
     * `not ilike` tidak mempan untuk many2one `location_id` (terbukti:
     * GA/Stock tetap muncul), jadi ID lokasinya di-resolve dulu lalu
     * dipakai `not in`.
     *
     * @return array<int, mixed>
     */
    public function baseDomain(bool $excludeGaStock = true): array
    {
        $domain = [
            ['location_id.usage', '=', 'internal'],
        ];
        if ($excludeGaStock) {
            $ids = $this->gaStockLocationIds();
            if (! empty($ids)) {
                $domain[] = ['location_id', 'not in', $ids];
            }
        }

        return $domain;
    }

    /**
     * Cari ID lokasi yang complete_name-nya mengandung GA/Stock.
     *
     * @return array<int, int>
     */
    protected function gaStockLocationIds(): array
    {
        if ($this->gaStockLocationIds !== null) {
            return $this->gaStockLocationIds;
        }

        $result = $this->odoo->searchRead('stock.location', [
            ['complete_name', 'ilike', 'GA/Stock'],
            ['usage', '=', 'internal'],
        ], ['id'], 50);

        $ids = collect($result['records'] ?? [])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        return $this->gaStockLocationIds = $ids;
    }

    /**
     * Agregat stok per produk (level 1, lazy=true).
     * Domain: internal + opsional exclude GA/Stock.
     *
     * @return array<int, array{id:int,code:string,name:string,quantity:float,reserved:float,available:float,count:int}>
     */
    public function getAll(bool $excludeGaStock = true): array
    {
        $data = $this->odoo->readGroup(
            'stock.quant',
            $this->baseDomain($excludeGaStock),
            self::QUANT_FIELDS,
            ['product_id', 'location_id'],
            ['lazy' => true],
            [
                'active_model' => 'stock.quantity.history',
                'active_id' => 2124,
                'active_ids' => [2124],
                'search_default_internal_loc' => 1,
                'group_by' => ['product_id', 'location_id'],
                'search_disable_custom_filters' => true,
            ],
        );

        return collect($data)->map(function ($item) {
            [$id, $code, $name] = pecah_code($item['product_id'] ?? null);
            $quantity = (float) ($item['quantity'] ?? 0);
            $reserved = (float) ($item['reserved_quantity'] ?? 0);

            return [
                'id' => $id,
                'code' => $code,
                'name' => $name,
                'quantity' => $quantity,
                'reserved' => $reserved,
                'available' => $quantity - $reserved,
                'count' => (int) ($item['product_id_count'] ?? 0),
            ];
        })->values()->all();
    }

    /**
     * Drilldown per lokasi untuk satu produk (level 2).
     *
     * @return array<int, array{location:string,quantity:float,reserved:float,available:float,count:int}>
     */
    public function getLocations(int $productId, bool $excludeGaStock = true): array
    {
        $domain = array_merge(
            [['product_id', '=', $productId]],
            $this->baseDomain($excludeGaStock)
        );

        $data = $this->odoo->readGroup(
            'stock.quant',
            $domain,
            ['product_id', 'location_id', 'reserved_quantity', 'quantity'],
            ['location_id'],
            ['lazy' => false],
            [
                'search_default_internal_loc' => 1,
                'group_by' => ['location_id'],
                'search_disable_custom_filters' => true,
            ],
        );

        return collect($data)->map(function ($item) {
            $quantity = (float) ($item['quantity'] ?? 0);
            $reserved = (float) ($item['reserved_quantity'] ?? 0);

            return [
                'location' => get_name($item['location_id'] ?? null) ?? '-',
                'quantity' => $quantity,
                'reserved' => $reserved,
                'available' => $quantity - $reserved,
                'count' => (int) ($item['__count'] ?? $item['location_id_count'] ?? 0),
            ];
        })->sortBy('location')->values()->all();
    }

    /**
     * Matriks produk x lokasi (pivot) untuk export.
     * Urutan lokasi dipertahankan sesuai urutan Odoo (first-seen),
     * meniru urutan kolom file pivot mentah.
     *
     * @return array{main: array<int,string>, others: array<int,string>, rows: array<int, array{id:int,code:string,name:string,qtys:array<string,float>}>}
     */
    public function getMatrix(bool $excludeGaStock = true): array
    {
        $result = $this->odoo->readGroup(
            'stock.quant',
            $this->baseDomain($excludeGaStock),
            ['product_id', 'location_id', 'quantity'],
            ['product_id', 'location_id'],
            ['lazy' => false],
            [
                'search_default_internal_loc' => 1,
                'group_by' => ['product_id', 'location_id'],
                'search_disable_custom_filters' => true,
            ],
        );

        return static::buildMatrix($result);
    }

    /**
     * Susun matriks dari hasil read_group (dipisah agar bisa diuji tanpa Odoo).
     *
     * @param  array<int, array<string, mixed>>  $result
     * @return array{main: array<int,string>, others: array<int,string>, locations: array<int,string>, rows: array<int, array{id:int,code:string,name:string,qtys:array<string,float>}>}
     */
    public static function buildMatrix(array $result): array
    {
        $main = ['BAD STOCK/Stock', 'CBB/Stock', 'CENTER/Stock', 'KRTN/Stock'];

        $locations = [];
        $products = [];
        foreach ($result as $item) {
            $loc = get_name($item['location_id'] ?? null);
            if (! $loc) {
                continue;
            }
            [$pid, $code, $name] = pecah_code($item['product_id'] ?? null);
            if (! in_array($loc, $locations, true)) {
                $locations[] = $loc;
            }
            if (! isset($products[$code])) {
                $products[$code] = ['id' => $pid, 'code' => $code, 'name' => $name, 'qtys' => []];
            }
            $products[$code]['qtys'][$loc] = ($products[$code]['qtys'][$loc] ?? 0) + (float) ($item['quantity'] ?? 0);
        }

        $others = array_values(array_filter($locations, fn ($loc) => ! in_array($loc, $main, true)));

        $rows = collect(array_values($products))->sortBy('code')->values()->all();

        return ['main' => $main, 'others' => $others, 'locations' => $locations, 'rows' => $rows];
    }

    /**
     * Bangun workbook 2 sheet: mentah ("Inventory", tiruan pivot Odoo)
     * + rapihan ("Inventory (2)"). Sheet aktif saat dibuka = rapihan.
     *
     * @param  array{main: array<int,string>, others: array<int,string>, locations: array<int,string>, rows: array<int, array{id:int,code:string,name:string,qtys:array<string,float>}>}  $matrix
     * @param  array{main: array<int,string>, others: array<int,string>, locations: array<int,string>, rows: array<int, array{id:int,code:string,name:string,qtys:array<string,float>}>}|null  $rawMatrix
     */
    public static function buildWorkbook(array $matrix, ?string $asOf = null, ?array $rawMatrix = null): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;

        if ($rawMatrix !== null) {
            static::fillRawSheet($spreadsheet->getActiveSheet(), $rawMatrix);
            $tidy = $spreadsheet->createSheet(1);
            static::fillTidySheet($tidy, $matrix, $asOf);
            $spreadsheet->setActiveSheetIndex(1);
        } else {
            static::fillTidySheet($spreadsheet->getActiveSheet(), $matrix, $asOf);
        }

        return $spreadsheet;
    }

    /**
     * Sheet mentah meniru file pivot Odoo ("Inventory"):
     * baris Total + kolom per lokasi + grand total,
     * tiap produk 2+ baris (grup `[KODE] Nama` + anak lokasi indent).
     * Tanpa kolom formula manual (pengurangan GA/Stock diatasi filter exclude).
     *
     * @param  array{main: array<int,string>, others: array<int,string>, locations: array<int,string>, rows: array<int, array{id:int,code:string,name:string,qtys:array<string,float>}>}  $matrix
     */
    public static function fillRawSheet(Worksheet $sheet, array $matrix): void
    {
        $locs = $matrix['locations'];
        $rows = $matrix['rows'];

        $letter = fn (int $col): string => Coordinate::stringFromColumnIndex($col);

        $colTotal = 2 + count($locs); // tepat setelah lokasi terakhir

        $sheet->setTitle('Inventory');

        // Baris 1-2: header pivot.
        $sheet->setCellValue('B1', 'Total');
        foreach ($locs as $i => $loc) {
            $sheet->setCellValue($letter(2 + $i).'2', $loc);
        }

        // Baris 3: Total (bold).
        $totals = [];
        foreach ($locs as $loc) {
            $totals[$loc] = 0;
        }
        foreach ($rows as $row) {
            foreach ($row['qtys'] as $loc => $qty) {
                if (array_key_exists($loc, $totals)) {
                    $totals[$loc] += $qty;
                }
            }
        }
        $grand = array_sum($totals);

        $sheet->setCellValue('A3', 'Total');
        foreach ($locs as $i => $loc) {
            static::setQty($sheet, $letter(2 + $i).'3', $totals[$loc] ?? 0);
        }
        static::setQty($sheet, $letter($colTotal).'3', $grand);
        $sheet->getStyle('B3:'.$letter($colTotal).'3')->getFont()->setBold(true);

        // Baris data: grup produk + anak lokasi.
        $r = 4;
        foreach ($rows as $row) {
            $rowTotal = array_sum($row['qtys']);
            $sheet->setCellValue('A'.$r, '     ['.$row['code'].'] '.$row['name']);
            foreach ($locs as $i => $loc) {
                static::setQty($sheet, $letter(2 + $i).$r, $row['qtys'][$loc] ?? 0);
            }
            static::setQty($sheet, $letter($colTotal).$r, $rowTotal);
            $r++;
            foreach ($locs as $i => $loc) {
                $qty = $row['qtys'][$loc] ?? 0;
                if ($qty == 0) {
                    continue;
                }
                $sheet->setCellValue('A'.$r, '          '.$loc);
                $sheet->setCellValue($letter(2 + $i).$r, $qty);
                $sheet->setCellValue($letter($colTotal).$r, $qty);
                $r++;
            }
        }
    }

    /**
     * Sheet rapihan meniru "Inventory (2)":
     * kop judul, kolom Code/Name, 4 gudang utama, Others (=SUM lokasi lain),
     * separator kosong, lokasi lain, All Stock (=SUM(C:G)).
     *
     * @param  array{main: array<int,string>, others: array<int,string>, locations: array<int,string>, rows: array<int, array{id:int,code:string,name:string,qtys:array<string,float>}>}  $matrix
     */
    public static function fillTidySheet(Worksheet $sheet, array $matrix, ?string $asOf = null): void
    {
        $main = $matrix['main'];
        $others = $matrix['others'];
        $rows = $matrix['rows'];

        $colOthers = 7; // G
        $colSep = 8; // H (kosong)
        $colFirstOther = 9; // I
        $colAllStock = $colFirstOther + count($others); // tepat setelah lokasi terakhir

        $letter = fn (int $col): string => Coordinate::stringFromColumnIndex($col);

        $sheet->setTitle('Inventory (2)');

        // Kop judul (bold 9 seperti file contoh).
        $sheet->setCellValue('A1', 'PT Mitra Asa Pratama');
        $sheet->setCellValue('A2', 'Quantity Item by Warehouse List');
        $sheet->setCellValue('A3', 'As of '.($asOf ?? date('d F Y')));
        $sheet->getStyle('A1:A3')->getFont()->setBold(true)->setSize(9);

        // Baris 5: label Total di atas area lokasi lain.
        $sheet->setCellValue($letter($colFirstOther).'5', 'Total');

        // Baris 6: header kolom.
        foreach ($main as $i => $loc) {
            $sheet->setCellValue($letter(3 + $i).'6', $loc);
        }
        $sheet->setCellValue($letter($colOthers).'6', 'Others');
        foreach ($others as $i => $loc) {
            $sheet->setCellValue($letter($colFirstOther + $i).'6', $loc);
        }
        $sheet->setCellValue($letter($colAllStock).'6', 'All Stock');

        // Total per kolom (angka; Others & All Stock pakai formula).
        $totals = [];
        foreach (array_merge($main, $others) as $loc) {
            $totals[$loc] = 0;
        }
        foreach ($rows as $row) {
            foreach ($row['qtys'] as $loc => $qty) {
                if (array_key_exists($loc, $totals)) {
                    $totals[$loc] += $qty;
                }
            }
        }

        $sheet->setCellValue('A7', 'Total');
        foreach ($main as $i => $loc) {
            static::setQty($sheet, $letter(3 + $i).'7', $totals[$loc] ?? 0);
        }
        if (count($others) > 0) {
            $lastOther = $letter($colFirstOther + count($others) - 1);
            $sheet->setCellValue(
                $letter($colOthers).'7',
                "=SUM({$letter($colFirstOther)}7:{$lastOther}7)"
            );
            foreach ($others as $i => $loc) {
                static::setQty($sheet, $letter($colFirstOther + $i).'7', $totals[$loc] ?? 0);
            }
        } else {
            $sheet->setCellValue($letter($colOthers).'7', 0);
        }
        $sheet->setCellValue(
            $letter($colAllStock).'7',
            "=SUM(C7:{$letter($colOthers)}7)"
        );
        $sheet->getStyle("A7:{$letter($colAllStock)}7")->getFont()->setBold(true);

        // Baris data: satu baris per produk, sparse (kosong bila 0).
        $r = 8;
        foreach ($rows as $row) {
            $sheet->setCellValue('A'.$r, $row['code']);
            $sheet->setCellValue('B'.$r, $row['name']);
            foreach ($main as $i => $loc) {
                static::setQty($sheet, $letter(3 + $i).$r, $row['qtys'][$loc] ?? 0);
            }
            if (count($others) > 0) {
                $lastOther = $letter($colFirstOther + count($others) - 1);
                $sheet->setCellValue(
                    $letter($colOthers).$r,
                    "=SUM({$letter($colFirstOther)}{$r}:{$lastOther}{$r})"
                );
                foreach ($others as $i => $loc) {
                    static::setQty($sheet, $letter($colFirstOther + $i).$r, $row['qtys'][$loc] ?? 0);
                }
            } else {
                $sheet->setCellValue($letter($colOthers).$r, 0);
            }
            $sheet->setCellValue(
                $letter($colAllStock).$r,
                "=SUM(C{$r}:{$letter($colOthers)}{$r})"
            );
            $r++;
        }

        // Lebar kolom seperti file contoh (kolom I sengaja TIDAK di-hidden:
        // di file contoh ia tersembunyi dan menyembunyikan data A-MDN/Stock).
        $sheet->getColumnDimension('A')->setWidth(46.57);
        $sheet->getColumnDimension('G')->setWidth(17.71);
        $sheet->getColumnDimension('H')->setWidth(5.57);
    }

    private static function setQty($sheet, string $cell, float $qty): void
    {
        if ($qty != 0) {
            $sheet->setCellValue($cell, $qty);
        }
    }
}
