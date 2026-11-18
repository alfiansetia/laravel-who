<?php

namespace App\Services;

use App\Models\Product;
use App\Services\Odoo\OdooClient;
use Illuminate\Support\Arr;

class StockServices
{
    private const QUANT_FIELDS = [
        'product_id', 'location_id', 'lot_id', 'itds_expired',
        'package_id', 'owner_id', 'reserved_quantity', 'quantity',
        'product_uom_id', 'write_date', 'company_id',
    ];

    public function __construct(private OdooClient $odoo) {}

    public function getAll(array|string|null $locations = [])
    {
        $locations = self::normalizeLocations($locations);

        $result = $this->odoo->readGroup(
            'stock.quant',
            self::internalDomain($locations),
            self::QUANT_FIELDS,
            ['product_id', 'location_id'],
            ['lazy' => true],
            [
                'tz' => 'GMT',
                'active_model' => 'stock.quantity.history',
                'active_id' => 1106,
                'active_ids' => [1106],
                'search_default_internal_loc' => 1,
                'group_by' => ['product_id', 'location_id'],
                'search_disable_custom_filters' => true,
            ],
        );

        $data = collect($result ?? []);

        // 1. Ekstrak info dasar dan kumpulkan semua 'code' unik
        $mappedData = $data->map(function ($item) {
            [$id, $code, $name] = pecah_code($item['product_id']);

            return [
                'id' => $id,
                'quantity' => $item['quantity'] ?? 0,
                'code' => $code,
                'name' => $name,
                'original' => $item,
            ];
        });

        // 2. Query database hanya untuk kode yang ada di data (Map untuk O(1) lookup)
        $productMap = Product::whereIn('code', $mappedData->pluck('code')->unique()->filter())
            ->pluck('akl', 'code');

        // 3. Masukkan 'akl' ke hasil akhir
        return $mappedData->map(function ($item) use ($productMap) {
            $item['akl'] = $productMap[$item['code']] ?? null;

            return $item;
        });
    }

    public function lot(int $id, array|string|null $locations = [], int $limit = 10)
    {
        $locations = self::normalizeLocations($locations);

        $result = $this->odoo->searchRead('stock.quant', array_merge(
            [['product_id', 'in', [$id]]],
            self::locationOr([], $locations),
        ), self::QUANT_FIELDS, $limit, 0, '', [
            'active_model' => 'product.template',
            'active_id' => 21418,
            'active_ids' => [21418],
            'search_default_internal_loc' => 1,
            'search_disable_custom_filters' => true,
        ]);

        $data = collect(Arr::get($result, 'records', []) ?? []);

        return $data->map(fn ($item) => static::mapQuantRow($item));
    }

    /**
     * Ambil semua lot/quant untuk lokasi sekaligus (bulk, paginasi).
     *
     * Pengganti N+1 query lot($id) per produk untuk kebutuhan opname lot:
     * 1 request search_read per halaman per lokasi, lalu agregat
     * quantity per (produk, lot, expired) di PHP.
     */
    public function lotBulk(array|string|null $locations = [], int $batchSize = 2000)
    {
        $locations = self::normalizeLocations($locations);
        $domain = array_merge(
            [['location_id.usage', '=', 'internal']],
            self::locationOr([], $locations),
            [['quantity', '>', 0]],
        );

        $records = collect();
        $offset = 0;
        do {
            $page = $this->odoo->searchRead('stock.quant', $domain, self::QUANT_FIELDS, $batchSize, $offset, '', [
                'search_default_internal_loc' => 1,
                'search_disable_custom_filters' => true,
            ]);
            $rows = Arr::get($page, 'records', []) ?? [];
            $records = $records->merge($rows);
            $offset += $batchSize;
        } while (count($rows) >= $batchSize);

        return $records->map(fn ($item) => static::mapQuantRow($item))
            ->groupBy(fn ($row) => $row['id'].'|'.($row['lot'] ?? '').'|'.($row['expired'] ?? ''))
            ->map(function ($group) {
                $first = $group->first();
                $first['quantity'] = $group->sum('quantity');

                return $first;
            })
            ->values()
            ->sortBy([
                ['code', 'asc'],
                ['lot', 'asc'],
            ])
            ->values();
    }

    private static function mapQuantRow(array $item): array
    {
        $loc = get_name($item['location_id'] ?? null);
        $lot = get_name($item['lot_id'] ?? null);
        $pecah_code = pecah_code($item['product_id'] ?? null);
        $id = $pecah_code[0];
        $code = $pecah_code[1];
        $name = $pecah_code[2];
        $expired = $item['itds_expired'] ?? null;
        $expired_ori = $expired;
        try {
            $expired = odoo_datetime($expired, 'Y.m.d');
        } catch (\Throwable) {
            // biarkan format mentah bila gagal parse
        }

        return [
            'id' => $id,
            'quantity' => $item['quantity'] ?? 0,
            'code' => $code,
            'name' => $name,
            'location' => $loc,
            'lot' => $lot,
            'expired' => $expired,
            'expired_ori' => $expired_ori,
            'original' => $item,
        ];
    }

    public static function getLocationAlias(string $code): string
    {
        return match (strtolower($code)) {
            'center' => 'CENTER',
            'cbb' => 'CIBUBUR',
            'krtn' => 'KARANTINA',
            'badstock' => 'BADSTOCK',
            'demo' => 'DEMO',
            default => $code,
        };
    }

    /**
     * @return array<int, array|string>
     */
    private static function internalDomain(array $locations): array
    {
        return array_merge(
            [['location_id.usage', '=', 'internal']],
            self::locationOr([], $locations),
        );
    }

    /**
     * Susun OR `location_id ilike X` — 0 lokasi = tanpa filter,
     * 1 lokasi = satu kondisi, N lokasi = prefix N-1 `'|'`.
     *
     * @return array<int, array|string>
     */
    private static function locationOr(array $domain, array $locations): array
    {
        if ($locations === []) {
            return $domain;
        }

        $conds = array_map(fn ($loc) => ['location_id', 'ilike', $loc], $locations);
        $ors = array_fill(0, count($conds) - 1, '|');

        return array_merge($domain, $ors, $conds);
    }

    /** @return array<int, string> */
    private static function normalizeLocations(array|string|null $locations): array
    {
        if (! is_array($locations)) {
            $locations = $locations === null || $locations === '' ? [] : array_values(array_filter(array_map('trim', explode(',', (string) $locations))));
        }

        return array_values($locations);
    }
}
