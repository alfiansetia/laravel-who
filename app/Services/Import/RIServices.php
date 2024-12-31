<?php

namespace App\Services\Import;

use App\Services\OdooServices;
use Exception;

class RIServices extends OdooServices
{
    public function __construct() {}

    public static function getAll($query = 'CENT/IN', $limit = 80, $offset = null)
    {
        if (empty($query)) {
            $query = 'CENT/IN';
        }
        if (empty($limit)) {
            $limit = 80;
        }
        $url_param = '/web/dataset/search_read';
        $data = [
            "jsonrpc" => "2.0",
            "method" => "call",
            "id" => 904074976,
            "params" => [
                "model" => "stock.picking",
                "domain" => [
                    [
                        "picking_type_id",
                        "=",
                        1
                    ],
                    "|",
                    [
                        "name",
                        "ilike",
                        $query
                    ],
                    [
                        "origin",
                        "ilike",
                        $query
                    ]
                ],
                "fields" => [
                    "name",
                    "x_studio_no_do_manual",
                    "date",
                    "scheduled_date",
                    "force_date",
                    "partner_id",
                    "location_dest_id",
                    "location_id",
                    "origin",
                    "x_studio_tags",
                    "x_studio_field_0rSR5",
                    "priority_so",
                    "note_to_wh",
                    "note_itr",
                    "invoice_state",
                    "group_id",
                    "backorder_id",
                    "state",
                    "priority",
                    "picking_type_id",
                    'move_ids_without_package',
                    'move_line_ids_without_package',
                    'note_to_wh',
                ],
                "limit" => intval($limit),
                "offset" => intval($offset),
                "sort" => "",
                "context" => [
                    "lang" => "en_US",
                    "tz" => "Asia/Jakarta",
                    "uid" => 192,
                    "active_id" => 1,
                    "active_ids" => [
                        1
                    ],
                    "params" => [
                        "id" => 20468,
                        "action" => 384,
                        "active_id" => 1,
                        "model" => "stock.picking",
                        "view_type" => "form",
                        "menu_id" => 241
                    ],
                    "search_default_picking_type_id" => [
                        1
                    ],
                    "default_picking_type_id" => 1,
                    "contact_display" => "partner_address",
                    "search_default_available" => 1,
                    "search_disable_custom_filters" => true
                ]
            ],
        ];
        $response  = parent::as_json()
            ->url_param($url_param)
            ->method('POST')
            ->data($data)
            ->get();
        if (!isset($response['result'])) {
            throw new Exception('Odoo Error');
        }
        return $response['result'];
    }

    public static function detail($id)
    {
        $url_param = '/web/dataset/call_kw/stock.picking/read';
        $data = [
            "jsonrpc" => "2.0",
            "method" => "call",
            "id" => 475016332,
            "params" => [
                "args" => [
                    [
                        intval($id)
                    ],
                    [
                        "id",
                        "is_locked",
                        "show_mark_as_todo",
                        "show_check_availability",
                        "show_validate",
                        "show_lots_text",
                        "cancel_done_picking",
                        "picking_type_code",
                        "show_operations",
                        "move_line_exist",
                        "has_packages",
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
                        "message_ids",
                        "message_attachment_count",
                        "display_name"
                    ]
                ],
                "model" => "stock.picking",
                "method" => "read",
                "kwargs" => [
                    "context" => [
                        "lang" => "en_US",
                        "tz" => "Asia/Jakarta",
                        "uid" => 192,
                        "active_model" => "stock.picking.type",
                        "active_id" => 1,
                        "active_ids" => [
                            1
                        ],
                        "search_default_picking_type_id" => [
                            1
                        ],
                        "default_picking_type_id" => 1,
                        "contact_display" => "partner_address",
                        "search_default_available" => 1,
                        "search_disable_custom_filters" => true,
                        "bin_size" => true
                    ]
                ]
            ],
        ];
        $response  = parent::as_json()
            ->url_param($url_param)
            ->method('POST')
            ->data($data)
            ->get();
        try {
            $order_line = static::move_line_without($response['result'][0]['move_ids_without_package']);
            $response['result'][0]['move_ids_without_package_detail'] = $order_line['result'] ?? [];
        } catch (\Throwable $th) {
            $response['result'][0]['move_ids_without_package_detail'] = [];
        }

        return $response['result'];
    }

    public static function move_line_without(array $line)
    {
        $line_int =  array_map('intval', array_filter($line, 'is_numeric'));
        $data_line = [
            "jsonrpc" => "2.0",
            "method" => "call",
            "id" => 563164766,
            "params" => [
                "args" => [
                    $line_int,
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
                        "active_model" => "stock.picking.type",
                        "active_id" => 1,
                        "active_ids" => [
                            1
                        ],
                        "search_default_picking_type_id" => [
                            1
                        ],
                        "default_picking_type_id" => 1,
                        "contact_display" => "partner_address",
                        "search_default_available" => 1,
                        "search_disable_custom_filters" => true,
                        "picking_type_code" => "incoming",
                        "default_picking_id" => 20563,
                        "form_view_ref" => "stock.view_move_picking_form",
                        "address_in_id" => 23069,
                        "default_location_id" => 8,
                        "default_location_dest_id" => 12
                    ]
                ]
            ],
        ];
        $order_line = parent::as_json()->method('POST')
            ->url_param('/web/dataset/call_kw/stock.move/read')
            ->data($data_line)->get();
        return $order_line;
    }
}
