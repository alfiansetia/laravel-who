<?php

namespace App\Services;

class DoService extends OdooService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function search(string $search)
    {
        return $this->url_param('/web/dataset/search_read')->method('POST')
            ->data([
                'jsonrpc' => '2.0',
                'method' => 'call',
                'params' => [
                    'model' => 'stock.picking',
                    'domain' => [
                        [
                            'picking_type_id',
                            '=',
                            2
                        ],
                        '|',
                        [
                            'name',
                            'ilike',
                            $search
                        ],
                        [
                            'origin',
                            'ilike',
                            $search
                        ]
                    ],
                    'fields' => [
                        'name',
                        'x_studio_no_do_manual',
                        'date',
                        'scheduled_date',
                        'force_date',
                        'partner_id',
                        'location_dest_id',
                        'location_id',
                        'origin',
                        'x_studio_tags',
                        'x_studio_field_0rSR5',
                        'priority_so',
                        'note_to_wh',
                        'note_itr',
                        'invoice_state',
                        'group_id',
                        'backorder_id',
                        'state',
                        'priority',
                        'picking_type_id'
                    ],
                    'limit' => 80,
                    'sort' => '',
                    'context' => [
                        'lang' => 'en_US',
                        'tz' => 'GMT',
                        'uid' => 192,
                        'active_model' => 'stock.picking.type',
                        'active_id' => 2,
                        'active_ids' => [
                            2
                        ],
                        'search_default_picking_type_id' => [
                            2
                        ],
                        'default_picking_type_id' => 2,
                        'contact_display' => 'partner_address',
                        'search_default_available' => 1,
                        'search_disable_custom_filters' => true
                    ]
                ],
                'id' => 654266576
            ])->get();
    }

    public function detail(int $id)
    {
        return $this->url_param('/web/dataset/call_kw/stock.picking/read')->method('POST')
            ->data([
                'jsonrpc'   => '2.0',
                'method'    => 'call',
                'params'    => [
                    'args'  => [
                        [$id],
                        [
                            "id",
                            "cancel_done_picking",
                            "picking_type_code",
                            "show_operations",
                            "move_line_exist",
                            "state",
                            "picking_type_entire_packs",
                            "has_scrap_move",
                            "has_tracking",
                            "name",
                            "x_studio_field_2kd16",
                            "x_studio_no_do_manual",
                            "partner_id",
                            "partner_address",
                            "partner_address2",
                            "partner_address3",
                            "partner_address4",
                            "x_studio_customer",
                            "bill_to",
                            "delivery_manual",
                            "ekspedisi_id",
                            "x_studio_update_eta",
                            "pp",
                            "ppk",
                            "location_id",
                            "kurir_id",
                            "kurir_state",
                            "tanggal_sampai",
                            "tanggal_persetujuan",
                            "location_dest_id",
                            "picking_type_id",
                            "backorder_id",
                            "date",
                            "force_date",
                            "x_studio_date",
                            "date_done",
                            "confirmation_date_so",
                            "print_x_studio_date",
                            "print_tanggal_sampai",
                            "tgl_bast",
                            "origin",
                            "x_studio_id_paket",
                            "x_studio_no_po",
                            "x_studio_no_ska",
                            "itr_id",
                            "invoice_state",
                            "cancel_reason",
                            "note_itr",
                            "receipts_date",
                            "no_po",
                            "no_aks",
                            "no_ska",
                            "no_sph",
                            "no_si",
                            "tgl_akhir_kontrak",
                            "note_to_wh",
                            "priority_so",
                            "owner_id",
                            "penanggung_jawab",
                            "jumlah_hari",
                            "partner",
                            "schedule_id",
                            "move_line_ids_without_package",
                            "package_level_ids_details",
                            "immediate_transfer",
                            "move_ids_without_package",
                            "package_level_ids",
                            "move_type",
                            "sale_id",
                            "company_id",
                            "group_id",
                            "scheduled_date",
                            "priority",
                            "note",
                            "move_temp_id",
                            "message_follower_ids",
                            "activity_ids",
                            "display_name"
                        ]
                    ],
                    'model' => 'stock.picking',
                    'method' => 'read',
                    'kwargs' => [
                        'context' => [
                            'lang' => 'en_US',
                            'tz' => 'GMT',
                            'uid' => 192,
                            'active_model' => 'stock.picking.type',
                            'active_id' => 2,
                            'active_ids' => [2],
                            'search_default_picking_type_id' => [2],
                            'default_picking_type_id' => 2,
                            'contact_display' => 'partner_address',
                            'search_default_available' => 1,
                            'search_disable_custom_filters' => true,
                            'bin_size' => true
                        ]
                    ]
                ],
                'id' => 334664946
            ])->get();
    }
}
