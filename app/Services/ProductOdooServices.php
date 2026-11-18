<?php

namespace App\Services;

use App\Services\Odoo\OdooClient;
use App\Services\Odoo\OdooDomain;

class ProductOdooServices
{
    /**
     * Cerminan field yang dibaca form produk Odoo saat klik detail
     * (payload `read` product.template + sub-read pricelist/diskon).
     */
    private const TEMPLATE_DETAIL_FIELDS = [
        'id', 'name', 'default_code', 'barcode', 'active', 'type', 'categ_id',
        'list_price', 'standard_price', 'taxes_id', 'uom_id', 'uom_po_id',
        'currency_id', 'qty_available', 'virtual_available',
        'outgoing_qty', 'incoming_qty',
        'hs_code', 'akl_id', 'desc_akl', 'x_studio_valid_to_akl',
        'tracking', 'sale_ok', 'purchase_ok',
        'seller_ids', 'item_ids', 'detail_disk',
        'product_variant_id', 'product_variant_ids', 'image_medium',
    ];

    private const PRICELIST_ITEM_FIELDS = [
        'pricelist_id', 'currency_id', 'fixed_price', 'min_quantity',
        'date_start', 'date_end', 'bc_persen', 'applied_on',
    ];

    private const DISK_DETAIL_FIELDS = [
        'name', 'metode_disk', 'disk_m', 'disk_rsm', 'disk_hm',
        'disk_d', 'disk_xd', 'disk_hm_dd', 'disk_d_dd',
    ];

    private const QUANT_FIELDS = [
        'product_id', 'location_id', 'lot_id', 'itds_expired', 'package_id',
        'owner_id', 'reserved_quantity', 'quantity', 'product_uom_id',
        'write_date', 'company_id',
    ];

    public function __construct(private OdooClient $odoo) {}

    public function getAll(string $query = '', int $limit = 80, int $offset = 0, array|string $type = []): array
    {
        $types = array_values(array_intersect((array) $type, ['consu', 'product']));
        if ($types === []) {
            $types = ['consu', 'product'];
        }

        $domain = [
            count($types) === 1 ? ['type', '=', $types[0]] : ['type', 'in', $types],
            ...OdooDomain::or(
                OdooDomain::ilike('default_code', $query),
                OdooDomain::ilike('product_variant_ids.default_code', $query),
                OdooDomain::ilike('name', $query),
                OdooDomain::ilike('barcode', $query),
            ),
        ];

        return $this->odoo->searchRead(
            'product.template',
            $domain,
            ['id', 'image_small', 'lst_price', 'type', 'product_variant_count',
                'currency_id', 'name', 'default_code', 'qty_available',
                'uom_id', 'akl_id', 'x_studio_valid_to_akl', 'product_variant_id'],
            $limit,
            $offset,
            'default_code ASC',
            ['search_default_consumable' => 1, 'default_type' => 'product'],
        );
    }

    public function detail(int $id): array
    {
        $res = $this->odoo->readOne('product.template', $id, self::TEMPLATE_DETAIL_FIELDS, [
            'search_default_consumable' => 1,
            'default_type' => 'product',
            'search_disable_custom_filters' => true,
            'bin_size' => true,
        ]);

        $res['variant_id'] = $this->resolveVariant($res);

        try {
            $res['on_hand'] = $this->onHand($id, (int) $res['variant_id'])['records'] ?? [];
        } catch (\Throwable) {
            $res['on_hand'] = [];
        }

        try {
            $res['pricelist_items'] = $this->odoo->read(
                'product.pricelist.item',
                self::ids($res['item_ids'] ?? []),
                self::PRICELIST_ITEM_FIELDS,
                [
                    'search_default_consumable' => 1,
                    'default_type' => 'product',
                    'default_applied_on' => '1_product',
                ]
            );
        } catch (\Throwable) {
            $res['pricelist_items'] = [];
        }

        try {
            $res['disk_details'] = $this->odoo->read(
                'tbl_detail_disk',
                self::ids($res['detail_disk'] ?? []),
                self::DISK_DETAIL_FIELDS,
                [
                    'search_default_consumable' => 1,
                    'default_type' => 'product',
                ]
            );
        } catch (\Throwable) {
            $res['disk_details'] = [];
        }

        return $res;
    }

    /** @return array<int> */
    private static function ids(mixed $value): array
    {
        $ids = is_array($value) ? $value : [$value];

        return array_values(array_unique(array_map(
            'intval',
            array_filter($ids, 'is_numeric'),
        )));
    }

    private function resolveVariant(array $product): int
    {
        $variant = $product['product_variant_id'] ?? false;
        if (is_array($variant) && isset($variant[0]) && (int) $variant[0] > 0) {
            return (int) $variant[0];
        }

        $variants = $product['product_variant_ids'] ?? [];
        if (is_array($variants)) {
            foreach ($variants as $variantId) {
                if (is_numeric($variantId) && (int) $variantId > 0) {
                    return (int) $variantId;
                }
            }
        }

        return 0;
    }

    public function onHand(int $id, int $variant): array
    {
        return $this->odoo->searchRead('stock.quant', [
            ['product_id', 'in', [$variant]],
            ['location_id.usage', '=', 'internal'],
        ], self::QUANT_FIELDS, 80, 0, '', [
            'active_model' => 'product.template',
            'active_id' => $id,
            'active_ids' => [$id],
            'search_default_internal_loc' => 1,
            'search_disable_custom_filters' => true,
        ]);
    }

    public function move(int $id, int $variant): array
    {
        return $this->odoo->searchRead('stock.move.line', [
            '&', ['product_id', '=', $variant],
            '&', ['product_id.product_tmpl_id', 'in', [$id]],
            ['state', '=', 'done'],
        ], [
            'reference', 'date', 'lot_id', 'lot_id2', 'lot_name',
            'x_studio_no_so', 'x_studio_tgl_so', 'product_id',
            'x_studio_id_paket', 'x_studio_customer',
            'product_category_related', 'location_id', 'location_dest_id',
            'qty_done', 'state', 'product_uom_id',
        ], 80, 0, 'date ASC', [
            'active_model' => 'product.template',
            'active_id' => $id,
            'active_ids' => [$id],
            'search_default_done' => 1,
            'search_default_groupby_product_id' => 1,
            'search_disable_custom_filters' => true,
            'group_by' => 'product_id',
        ]);
    }
}
