<?php

namespace App\Services;

use App\Services\Odoo\OdooClient;
use App\Services\Odoo\OdooDomain;

class ProductOdooServices
{
    private const PICKING_DETAIL_FIELDS = [
        'id', 'is_locked', 'show_mark_as_todo', 'show_check_availability',
        'show_validate', 'show_lots_text', 'cancel_done_picking',
        'picking_type_code', 'show_operations', 'move_line_exist',
        'has_packages', 'state', 'picking_type_entire_packs',
        'has_scrap_move', 'has_tracking', 'name', 'x_studio_field_2kd16',
        'x_studio_no_do_manual', 'partner_id', 'partner_address',
        'partner_address2', 'partner_address3', 'partner_address4',
        'x_studio_customer', 'bill_to', 'delivery_manual', 'ekspedisi_id',
        'x_studio_update_eta', 'pp', 'ppk', 'location_id', 'kurir_id',
        'kurir_state', 'tanggal_sampai', 'tanggal_persetujuan',
        'location_dest_id', 'picking_type_id', 'backorder_id', 'date',
        'force_date', 'x_studio_date', 'date_done', 'confirmation_date_so',
        'print_x_studio_date', 'print_tanggal_sampai', 'tgl_bast',
        'origin', 'x_studio_id_paket', 'x_studio_no_po', 'x_studio_no_ska',
        'itr_id', 'invoice_state', 'cancel_reason', 'note_itr',
        'receipts_date', 'no_po', 'no_aks', 'no_ska', 'no_sph', 'no_si',
        'tgl_akhir_kontrak', 'note_to_wh', 'priority_so', 'system_so',
        'owner_id', 'penanggung_jawab', 'jumlah_hari', 'partner',
        'schedule_id', 'move_line_ids_without_package',
        'package_level_ids_details', 'immediate_transfer',
        'move_ids_without_package', 'package_level_ids', 'move_type',
        'sale_id', 'company_id', 'group_id', 'scheduled_date',
        'priority', 'note', 'move_temp_id', 'message_follower_ids',
        'activity_ids', 'message_ids', 'message_attachment_count',
        'display_name',
    ];

    private const MOVE_FIELDS = [
        'product_id', 'name', 'date_expected', 'state', 'picking_type_id',
        'location_id', 'location_dest_id', 'scrapped', 'picking_code',
        'product_type', 'show_details_visible', 'show_reserved_availability',
        'show_operations', 'additional', 'has_move_lines', 'is_locked',
        'x_studio_lot', 'x_studio_field_X7gbX', 'hs_code', 'akl_id',
        'exp_date', 'is_initial_demand_editable',
        'is_quantity_done_editable', 'product_uom_qty',
        'reserved_availability', 'quantity_done', 'product_uom',
    ];

    private const MOVE_LINE_FIELDS = [
        'picking_id', 'product_id', 'package_level_id', 'location_id',
        'location_dest_id', 'lot_id', 'expired_date_do', 'lot_name',
        'expired_date', 'lot_id2', 'package_id', 'result_package_id',
        'owner_id', 'is_initial_demand_editable', 'product_uom_qty',
        'qty_availa', 'state', 'is_locked', 'qty_done', 'product_uom_id',
    ];

    private const QUANT_FIELDS = [
        'product_id', 'location_id', 'lot_id', 'itds_expired', 'package_id',
        'owner_id', 'reserved_quantity', 'quantity', 'product_uom_id',
        'write_date', 'company_id',
    ];

    public function __construct(private OdooClient $odoo) {}

    public function getAll(string $query = '', int $limit = 80, int $offset = 0): array
    {
        $domain = [
            ['type', 'in', ['consu', 'product']],
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
        $res = $this->odoo->readOne('stock.picking', $id, self::PICKING_DETAIL_FIELDS, [
            'tz' => 'GMT',
            'active_model' => 'stock.picking.type',
            'active_id' => 2,
            'active_ids' => [2],
            'search_default_picking_type_id' => [2],
            'default_picking_type_id' => 2,
            'contact_display' => 'partner_address',
            'search_default_available' => 1,
            'search_disable_custom_filters' => true,
            'bin_size' => true,
        ]);

        try {
            $res['move_ids_detail'] = $this->getOrderLines($res['move_ids_without_package'] ?? []);
            $res['move_line_detail'] = $this->getOrderLinesDetail($res['move_line_ids_without_package'] ?? []);
        } catch (\Throwable) {
            $res['move_ids_detail'] = [];
            $res['move_line_detail'] = [];
        }

        return $res;
    }

    /** @return array<int, array> */
    public function getOrderLines(array $line): array
    {
        return $this->odoo->read('stock.move', $line, self::MOVE_FIELDS, [
            'tz' => 'GMT',
            'active_model' => 'stock.picking.type',
            'active_id' => 2,
            'active_ids' => [2],
            'search_default_picking_type_id' => [2],
            'default_picking_type_id' => 2,
            'contact_display' => 'partner_address',
            'search_default_available' => 1,
            'search_disable_custom_filters' => true,
            'picking_type_code' => 'outgoing',
        ]);
    }

    /** @return array<int, array> */
    public function getOrderLinesDetail(array $line): array
    {
        return $this->odoo->read('stock.move.line', $line, self::MOVE_LINE_FIELDS, [
            'active_model' => 'stock.move',
            'search_default_available' => 1,
            'search_disable_custom_filters' => true,
            'show_lots_m2o' => true,
            'show_lots_text' => false,
        ]);
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
        ], 80, 0, 'date DESC', [
            'active_model' => 'product.template',
            'search_default_done' => 1,
            'search_default_groupby_product_id' => 1,
            'search_disable_custom_filters' => true,
            'group_by' => 'product_id',
        ]);
    }
}
