<?php

namespace App\Services;

use App\Services\Odoo\InteractsWithPicking;
use App\Services\Odoo\OdooClient;

class LotServices
{
    use InteractsWithPicking;

    private const LIST_FIELDS = [
        'name', 'product_qty1', 'ref', 'product_id', 'create_date',
        'use_date', 'life_date',
    ];

    public function __construct(private OdooClient $odoo) {}

    public function getAll(string $query = '', int $limit = 80, int $offset = 0, ?string $product = null): array
    {
        $domain = [];
        if (! empty($product)) {
            $domain[] = ['product_id', 'ilike', $product];
        }
        if (! empty($query)) {
            $domain[] = ['name', 'ilike', $query];
        }

        return $this->odoo->searchRead('stock.production.lot', $domain, self::LIST_FIELDS, $limit, $offset, '', [
            'search_default_group_by_product' => 1,
        ]);
    }

    public function detail(int $id): array
    {
        $res = $this->odoo->readOne('stock.picking', $id, [
            'id', 'is_locked', 'show_mark_as_todo', 'show_check_availability',
            'show_validate', 'show_lots_text', 'cancel_done_picking',
            'picking_type_code', 'show_operations', 'move_line_exist',
            'has_packages', 'state', 'picking_type_entire_packs',
            'has_scrap_move', 'has_tracking', 'name', 'x_studio_field_2kd16',
            'x_studio_no_do_manual', 'partner_id', 'partner_address',
            'partner_address2', 'partner_address3', 'partner_address4',
            'x_studio_customer', 'bill_to', 'delivery_manual',
            'ekspedisi_id', 'x_studio_update_eta', 'pp', 'ppk',
            'location_id', 'kurir_id', 'kurir_state', 'tanggal_sampai',
            'tanggal_persetujuan', 'location_dest_id', 'picking_type_id',
            'backorder_id', 'date', 'force_date', 'x_studio_date',
            'date_done', 'confirmation_date_so', 'print_x_studio_date',
            'print_tanggal_sampai', 'tgl_bast', 'origin',
            'x_studio_id_paket', 'x_studio_no_po', 'x_studio_no_ska',
            'itr_id', 'invoice_state', 'cancel_reason', 'note_itr',
            'receipts_date', 'no_po', 'no_aks', 'no_ska', 'no_sph',
            'no_si', 'tgl_akhir_kontrak', 'note_to_wh', 'priority_so',
            'system_so', 'owner_id', 'penanggung_jawab', 'jumlah_hari',
            'partner', 'schedule_id', 'move_line_ids_without_package',
            'package_level_ids_details', 'immediate_transfer',
            'move_ids_without_package', 'package_level_ids', 'move_type',
            'sale_id', 'company_id', 'group_id', 'scheduled_date',
            'priority', 'note', 'move_temp_id', 'message_follower_ids',
            'activity_ids', 'message_ids', 'message_attachment_count',
            'display_name',
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

    public function getTraceHtml(int $id): array
    {
        return $this->odoo->postJson('/web/dataset/call_kw/stock.traceability.report/get_html', [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'id' => 24584546,
            'params' => [
                'args' => [[
                    'active_id' => $id,
                    'model' => 'stock.production.lot',
                    'ttype' => false,
                    'auto_unfold' => false,
                    'lot_name' => false,
                ]],
                'model' => 'stock.traceability.report',
                'method' => 'get_html',
                'kwargs' => new \stdClass,
            ],
        ]);
    }
}
