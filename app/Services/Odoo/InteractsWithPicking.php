<?php

namespace App\Services\Odoo;

/**
 * Shared reader untuk stock.picking relations.
 *
 * getOrderLines / getOrderLinesDetail / getPartners sebelumnya
 * copy-paste identik di Do/So/It/RI/ProductOdoo. Trait ini satu-satunya
 * sumber field list + context default-nya.
 */
trait InteractsWithPicking
{
    /** @return array<int, array> */
    protected function getOrderLines(array $ids): array
    {
        return $this->odoo->read('stock.move', $ids, [
            'product_id', 'name', 'date_expected', 'state', 'picking_type_id',
            'location_id', 'location_dest_id', 'scrapped', 'picking_code',
            'product_type', 'show_details_visible', 'show_reserved_availability',
            'show_operations', 'additional', 'has_move_lines', 'is_locked',
            'x_studio_lot', 'x_studio_field_X7gbX', 'hs_code', 'akl_id',
            'exp_date', 'is_initial_demand_editable',
            'is_quantity_done_editable', 'product_uom_qty',
            'reserved_availability', 'quantity_done', 'product_uom',
        ], [
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
    protected function getOrderLinesDetail(array $ids): array
    {
        return $this->odoo->read('stock.move.line', $ids, [
            'picking_id', 'product_id', 'package_level_id', 'location_id',
            'location_dest_id', 'lot_id', 'expired_date_do', 'lot_name',
            'expired_date', 'lot_id2', 'package_id', 'result_package_id',
            'owner_id', 'is_initial_demand_editable', 'product_uom_qty',
            'qty_availa', 'state', 'is_locked', 'qty_done', 'product_uom_id',
        ], [
            'active_model' => 'stock.move',
            'search_default_available' => 1,
            'search_disable_custom_filters' => true,
            'show_lots_m2o' => true,
            'show_lots_text' => false,
        ]);
    }

    /** @return array<int, array> */
    protected function getPartners(array $ids, array $context = []): array
    {
        return $this->odoo->read('res.partner', $ids, [
            'partner_gid', 'additional_info', 'partner_ledger_label', 'active',
            'is_company', 'company_type', 'name', 'parent_id', 'company_name',
            'nomor_partner', 'type', 'street', 'street2', 'city', 'kota_id',
            'kecamatan_id', 'kelurahan_id', 'state_id', 'zip', 'country_id',
            'vat', 'is_driver', 'no_ktp', 'is_npwp_pribadi', 'nama_npwp',
            'alamat_npwp', 'kategori_pajak', 'is_ekspedisi', 'date',
            'create_date', 'crm_tag_ids', 'is_shipper', 'is_consignee',
            'is_buyer', 'is_seller', 'is_forwarding_agent', 'agency_type',
            'function', 'phone', 'mobile', 'user_ids', 'is_blacklisted',
            'email', 'insurance', 'website', 'x_studio_fax', 'd_id',
            'fax_msi', 'title', 'lang', 'category_id', 'kode',
            'detail_lokasi', 'red_colour', 'red_flag', 'colour', 'note',
            'comment', 'customer', 'user_id', 'message_bounce', 'supplier',
            'credit_check', 'credit_limit_custom', 'blocking_limit',
            'is_hold', 'active_date', 'deactive_date', 'ref', 'barcode',
            'company_id', 'website_id', 'industry_id', 'is_dist',
            'exp_date', 'idak_exp', 'status_expired', 'disk_transaksi',
            'add_exp_datetime', 'allowed_city_ids', 'allowed_products_ids',
            'add_allowed_city_ids', 'add_allowed_products_ids',
            'is_rel_partner', 'add_rel_exp_datetime',
            'rel_allowed_city_ids', 'rel_allowed_products_ids',
            'add_rel_allowed_city_ids', 'add_rel_allowed_products_ids',
            'currency_id', 'nama_npwp_new', 'kode_pajak', 'nama_faktur',
            'npwp', 'alamat_lengkap', 'blok', 'nomor', 'rt', 'rw',
            'is_efaktur_exported', 'date_efaktur_exported', 'is_berikat',
            'display_name',
        ], array_merge([
            'search_default_customer' => 1,
            'show_address' => 1,
            'show_vat' => true,
            'default_type' => 'delivery',
            'bin_size' => true,
        ], $context));
    }
}
