<?php

namespace App\Services;

class DoMonitorService extends Odoo
{

    public static function getAll()
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
        $tes =  parent::asJson()
            ->withUrlParam('/web/dataset/search_read')
            ->method('POST')
            ->withData($tesparam)
            ->get();
        $tesparam['params']['limit'] = intval($tes['result']['length']);

        $res = parent::asJson()
            ->withUrlParam('/web/dataset/search_read')
            ->method('POST')
            ->withData($tesparam)
            ->get();

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
        $res2 = parent::asJson()
            ->withUrlParam('/web/dataset/call_kw/stock.move/read')
            ->method('POST')
            ->withData($paramdetail)
            ->get();

        $details = collect($res2['result'])->keyBy('id');

        $result = collect($res['result']['records'])->map(function ($item) use ($details) {
            $item['move_ids_without_package_detail'] = collect($item['move_ids_without_package'])
                ->map(function ($id) use ($details) {
                    return $details->get($id);
                })->filter()->values()->all();
            return $item;
        })->all();
        return $result;
    }
}
