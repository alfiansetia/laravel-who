<?php

namespace App\Services;

class DoMonitorService extends OdooService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function search()
    {

        $tesparam = [
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
                    '|',
                    [
                        'state',
                        '=',
                        'draft'
                    ],
                    [
                        'state',
                        'in',
                        [
                            'confirmed',
                            'waiting'
                        ]
                    ],
                    [
                        'state',
                        'in',
                        [
                            'assigned',
                            'partially_available'
                        ]
                    ]
                ],
                'fields' => [
                    'name',
                    'confirmation_date_so',
                    'date',
                    'scheduled_date',
                    'x_studio_no_do_manual',
                    'force_date',
                    'origin',
                    'note_to_wh',
                    'move_ids_without_package',
                    'move_line_ids_without_package',
                    'group_id',
                    'backorder_id',
                    'partner_id',
                    'location_dest_id',
                    'location_id',
                    'x_studio_tags',
                    'x_studio_field_0rSR5',
                    'priority_so',
                    'note_itr',
                    'invoice_state',
                    'state',
                    'priority',
                    'picking_type_id',
                ],
                'limit' => 1,
                'sort' => '',
                'context' => [
                    'lang' => 'en_US',
                    'tz' => 'Asia/Jakarta',
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
        ];
        $tes =  $this->url_param('/web/dataset/search_read')->method('POST')
            ->data($tesparam)->get();
        $tesparam['params']['limit'] = intval($tes['result']['length']);

        $res =  $this->url_param('/web/dataset/search_read')->method('POST')
            ->data($tesparam)->get();

        $move_ids_without_package = collect($res['result']['records'])
            ->pluck('move_ids_without_package')
            ->flatten()
            ->all();
        $paramdetail = [
            "jsonrpc" => "2.0",
            "method" => "call",
            "params" => [
                "args" => [
                    $move_ids_without_package,
                    [
                        "product_id",
                        "name",
                        "date_expected",
                        "state",
                        "picking_type_id",
                        "location_id",
                        "location_dest_id",
                        "scrapped",
                        "picking_code",
                        "product_type",
                        "show_details_visible",
                        "show_reserved_availability",
                        "show_operations",
                        "additional",
                        "has_move_lines",
                        "is_locked",
                        "x_studio_lot",
                        "x_studio_field_X7gbX",
                        "hs_code",
                        "akl_id",
                        "exp_date",
                        "is_initial_demand_editable",
                        "is_quantity_done_editable",
                        "product_uom_qty",
                        "reserved_availability",
                        "quantity_done",
                        "product_uom"
                    ]
                ],
                "model" => "stock.move",
                "method" => "read",
                "kwargs" => [
                    "context" => [
                        "lang" => "en_US",
                        "tz" => "Asia/Jakarta",
                        "uid" => 192,
                        "active_id" => 2,
                        "active_ids" => [
                            2
                        ],
                        "params" => [
                            "action" => 384,
                            "active_id" => 2,
                            "model" => "stock.picking",
                            "view_type" => "list",
                            "menu_id" => 241
                        ],
                        "search_default_picking_type_id" => [
                            2
                        ],
                        "default_picking_type_id" => 2,
                        "contact_display" => "partner_address",
                        "search_default_available" => 1,
                        "search_disable_custom_filters" => true,
                        "picking_type_code" => "outgoing",
                        "default_picking_id" => 19887,
                        "form_view_ref" => "stock.view_move_picking_form",
                        "address_in_id" => 36348,
                        "default_location_id" => 12,
                        "default_location_dest_id" => 9
                    ]
                ]
            ],
            "id" => 748176687
        ];
        $res2 = $this->url_param('/web/dataset/call_kw/stock.move/read')->method('POST')
            ->data($paramdetail)->get();

        $details = collect($res2['result'])->keyBy('id');

        $result = collect($res['result']['records'])->map(function ($item) use ($details) {
            $item['move_ids_without_package_detail'] = collect($item['move_ids_without_package'])
                ->map(function ($id) use ($details) {
                    return $details->get($id);
                })->filter()->values()->all();
            return $item;
        })->all();
        return $result;
        // return response()->json([
        //     'a'     => count($res['result']['records']),
        //     'b'     => count($res2['result']),
        //     'res'   => $result
        // ]);
        // return count($move_ids_without_package);
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
                            'id',
                            'cancel_done_picking',
                            'picking_type_code',
                            'show_operations',
                            'move_line_exist',
                            'state',
                            'picking_type_entire_packs',
                            'has_scrap_move',
                            'has_tracking',
                            'name',
                            'x_studio_field_2kd16',
                            'x_studio_no_do_manual',
                            'partner_id',
                            'partner_address',
                            'partner_address2',
                            'partner_address3',
                            'partner_address4',
                            'x_studio_customer',
                            'bill_to',
                            'delivery_manual',
                            'ekspedisi_id',
                            'x_studio_update_eta',
                            'pp',
                            'ppk',
                            'location_id',
                            'kurir_id',
                            'kurir_state',
                            'tanggal_sampai',
                            'tanggal_persetujuan',
                            'location_dest_id',
                            'picking_type_id',
                            'backorder_id',
                            'date',
                            'force_date',
                            'x_studio_date',
                            'date_done',
                            'confirmation_date_so',
                            'print_x_studio_date',
                            'print_tanggal_sampai',
                            'tgl_bast',
                            'origin',
                            'x_studio_id_paket',
                            'x_studio_no_po',
                            'x_studio_no_ska',
                            'itr_id',
                            'invoice_state',
                            'cancel_reason',
                            'note_itr',
                            'receipts_date',
                            'no_po',
                            'no_aks',
                            'no_ska',
                            'no_sph',
                            'no_si',
                            'tgl_akhir_kontrak',
                            'note_to_wh',
                            'priority_so',
                            'owner_id',
                            'penanggung_jawab',
                            'jumlah_hari',
                            'partner',
                            'schedule_id',
                            'move_line_ids_without_package',
                            'package_level_ids_details',
                            'immediate_transfer',
                            'move_ids_without_package',
                            'package_level_ids',
                            'move_type',
                            'sale_id',
                            'company_id',
                            'group_id',
                            'scheduled_date',
                            'priority',
                            'note',
                            'move_temp_id',
                            'message_follower_ids',
                            'activity_ids',
                            'display_name'
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
