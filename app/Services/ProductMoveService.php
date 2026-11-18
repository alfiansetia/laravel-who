<?php

namespace App\Services;

use App\Services\Odoo\OdooClient;

class ProductMoveService
{
    private const FIELDS = [
        'reference', 'date', 'lot_id', 'lot_id2', 'lot_name',
        'x_studio_no_so', 'x_studio_tgl_so', 'product_id',
        'x_studio_id_paket', 'x_studio_customer',
        'product_category_related', 'location_id', 'location_dest_id',
        'qty_done', 'state', 'product_uom_id',
    ];

    public function __construct(private OdooClient $odoo) {}

    public function getAll(int $id, int $limit = 80, ?int $offset = 0): array
    {
        return $this->odoo->searchRead('stock.move.line', [
            ['product_id.product_tmpl_id', 'in', [$id]],
            ['state', '=', 'done'],
        ], self::FIELDS, $limit, $offset ?? 0, 'date DESC', [
            'active_model' => 'product.template',
            'search_default_done' => 1,
            'search_default_groupby_product_id' => 1,
            'search_disable_custom_filters' => true,
        ]);
    }
}
