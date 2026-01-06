<?php

namespace App\Services;

use App\Exceptions\OdooException;
use Illuminate\Support\Arr;

class DoServices extends Odoo
{
    public function __construct() {}

    public static function getAll($query = 'CENT/OUT', $limit = 80, $offset = null)
    {
        if (empty($query)) {
            $query = 'CENT/OUT';
        }
        if (empty($limit)) {
            $limit = 80;
        }
        $url_param = '/web/dataset/search_read';
        $data =         [
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
                        $query
                    ],
                    [
                        'origin',
                        'ilike',
                        $query
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
                    'picking_type_id',
                    'sale_id',
                ],
                "limit" => intval($limit),
                "offset" => intval($offset),
                "sort" => "",
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
        ];
        $response  = parent::asJson()
            ->withUrlParam($url_param)
            ->method('POST')
            ->withData($data)
            ->get();
        $res = Arr::get($response, 'result', null);
        if (!$res) {
            throw new OdooException('Data Not Found!', 404);
        }
        return $res;
    }

    public static function detail(int $id)
    {
        $url_param = '/web/dataset/call_kw/stock.picking/read';
        $data = [
            'jsonrpc'   => '2.0',
            'method'    => 'call',
            'params'    => [
                'args'  => [
                    [intval($id)],
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
                        "display_name",
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
        ];
        $response  = parent::asJson()
            ->withUrlParam($url_param)
            ->method('POST')
            ->withData($data)
            ->get();
        $res = Arr::get($response, 'result.0', null);
        if (!$res) {
            throw new OdooException('Data Not Found!', 404);
        }
        try {
            $order_ids = static::getOrderLines($res['move_ids_without_package'] ?? []);
            $order_line = static::getOrderLinesDetail($res['move_line_ids_without_package'] ?? []);
            $res['move_ids_detail'] = $order_ids['result'] ?? [];
            $res['move_line_detail'] = $order_line['result'] ?? [];
        } catch (\Throwable $th) {
            $res['move_ids_detail'] = [];
            $res['move_line_detail'] = [];
        }
        try {
            $partner_ids = array_unique(array_filter([
                Arr::get($res, 'bill_to.0'),
                Arr::get($res, 'partner_id.0'),
            ]));
            $partners = static::getPartners(array_values($partner_ids));
            $part_res = $partners['result'] ?? [];
            $res['bill_to_detail'] = collect($part_res)
                ->where('id', Arr::get($res, 'bill_to.0'))
                ->first();
            $res['partner_detail'] = collect($part_res)
                ->where('id', Arr::get($res, 'partner_id.0'))
                ->first();
            $res['partner_details'] = $part_res;
        } catch (\Throwable $th) {
            $res['bill_to_detail'] = [];
            $res['partner_detail'] = [];
            $res['partner_details'] = [];
        }
        try {
            $so_id = Arr::get($res, 'sale_id.0');
            $so = SoServices::detail($so_id);
            $res['so_detail'] = $so;
        } catch (\Throwable $th) {
            $res['so_detail'] = [];
        }
        return $res;
    }

    public static function getOrderLines(array $line)
    {
        $id_prod =  array_map('intval', array_filter($line, 'is_numeric'));
        $data_line = [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => [
                'args' => [
                    $id_prod,
                    [
                        "product_id",
                        "name",
                        "location_id",
                        "x_studio_lot",
                        "x_studio_field_X7gbX",
                        "akl_id",
                        "exp_date",
                        "product_uom_qty",
                        "quantity_done",
                    ]
                ],
                'model' => 'stock.move',
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
                        'picking_type_code' => 'outgoing',
                        'default_picking_id' => 2309,
                        'form_view_ref' => 'stock.view_move_picking_form',
                        'address_in_id' => 25823,
                        'default_location_id' => 12,
                        'default_location_dest_id' => 9
                    ]
                ]
            ],
            'id' => 555446768
        ];
        $order_line = parent::asJson()
            ->method('POST')
            ->withUrlParam('/web/dataset/call_kw/stock.move/read')
            ->withData($data_line)
            ->get();
        return $order_line;
    }

    public static function getOrderLinesDetail(array $line)
    {
        $id_prod =  array_map('intval', array_filter($line, 'is_numeric'));
        $data_line = [
            "jsonrpc" => "2.0",
            "method" => "call",
            "params" => [
                "args" => [
                    $id_prod,
                    [
                        "picking_id",
                        "product_id",
                        "package_level_id",
                        "location_id",
                        "location_dest_id",
                        "lot_id",
                        "expired_date_do",
                        "lot_name",
                        "expired_date",
                        "lot_id2",
                        "package_id",
                        "result_package_id",
                        "owner_id",
                        "is_initial_demand_editable",
                        "product_uom_qty",
                        "qty_availa",
                        "state",
                        "is_locked",
                        "qty_done",
                        "product_uom_id"
                    ]
                ],
                "model" => "stock.move.line",
                "method" => "read",
                "kwargs" => [
                    "context" => [
                        "lang" => "en_US",
                        "tz" => "Asia/Jakarta",
                        "uid" => 192,
                        "params" => [
                            "action" => 384,
                            "active_id" => 2,
                            "model" => "stock.picking",
                            "view_type" => "list",
                            "menu_id" => 241
                        ],
                        "contact_display" => "partner_address",
                        "search_disable_custom_filters" => true,
                        "active_model" => "stock.move",
                        "active_id" => 2,
                        "active_ids" => [
                            2
                        ],
                        "search_default_picking_type_id" => [
                            2
                        ],
                        "default_picking_type_id" => 2,
                        "search_default_available" => 1,
                        "show_lots_m2o" => true,
                        "show_lots_text" => false,
                        "show_source_location" => "stock.location()",
                        "show_destination_location" => "stock.location()",
                        "show_package" => true,
                        "show_reserved_quantity" => false,
                        "tree_view_ref" => "stock.view_stock_move_line_operation_tree",
                        "default_product_uom_id" => 61,
                        "default_picking_id" => 20018,
                        "default_move_id" => 226881,
                        "default_product_id" => 22006,
                        "default_location_id" => 12,
                        "default_location_dest_id" => 9
                    ]
                ]
            ],
            "id" => 647906249
        ];
        $order_line = parent::asJson()
            ->method('POST')
            ->withUrlParam('/web/dataset/call_kw/stock.move/read')
            ->withData($data_line)
            ->get();
        return $order_line;
    }

    public static function getPartners(array $ids)
    {
        $id_parts =  array_map('intval', array_filter($ids, 'is_numeric'));
        $data_parts = [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => [
                'args' => [
                    $id_parts,
                    [
                        "partner_gid",
                        "additional_info",
                        "partner_ledger_label",
                        "active",
                        "is_company",
                        "company_type",
                        "name",
                        "parent_id",
                        "company_name",
                        "nomor_partner",
                        "type",
                        "street",
                        "street2",
                        "city",
                        "kota_id",
                        "kecamatan_id",
                        "kelurahan_id",
                        "state_id",
                        "zip",
                        "country_id",
                        "vat",
                        "is_driver",
                        "no_ktp",
                        "is_npwp_pribadi",
                        "nama_npwp",
                        "alamat_npwp",
                        "kategori_pajak",
                        "is_ekspedisi",
                        "date",
                        "create_date",
                        "crm_tag_ids",
                        "is_shipper",
                        "is_consignee",
                        "is_buyer",
                        "is_seller",
                        "is_forwarding_agent",
                        "agency_type",
                        "function",
                        "phone",
                        "mobile",
                        "user_ids",
                        "is_blacklisted",
                        "email",
                        "insurance",
                        "website",
                        "x_studio_fax",
                        "d_id",
                        "fax_msi",
                        "title",
                        "lang",
                        "category_id",
                        "kode",
                        "detail_lokasi",
                        "red_colour",
                        "red_flag",
                        "colour",
                        "note",
                        "comment",
                        "customer",
                        "user_id",
                        "message_bounce",
                        "supplier",
                        "credit_check",
                        "credit_limit_custom",
                        "blocking_limit",
                        "is_hold",
                        "active_date",
                        "deactive_date",
                        "ref",
                        "barcode",
                        "company_id",
                        "website_id",
                        "industry_id",
                        "is_dist",
                        "exp_date",
                        "idak_exp",
                        "status_expired",
                        "disk_transaksi",
                        "add_exp_datetime",
                        "allowed_city_ids",
                        "allowed_products_ids",
                        "add_allowed_city_ids",
                        "add_allowed_products_ids",
                        "is_rel_partner",
                        "add_rel_exp_datetime",
                        "rel_allowed_city_ids",
                        "rel_allowed_products_ids",
                        "add_rel_allowed_city_ids",
                        "add_rel_allowed_products_ids",
                        "currency_id",
                        "nama_npwp_new",
                        "kode_pajak",
                        "nama_faktur",
                        "npwp",
                        "alamat_lengkap",
                        "blok",
                        "nomor",
                        "rt",
                        "rw",
                        "is_efaktur_exported",
                        "date_efaktur_exported",
                        "is_berikat",
                        "display_name"
                    ]
                ],
                'model' => 'res.partner',
                'method' => 'read',
                'kwargs' => [
                    'context' => [
                        "lang" => "en_US",
                        "tz" => "Asia/Jakarta",
                        "uid" => 192,
                        "search_default_customer" => 1,
                        "show_address" => 1,
                        "show_vat" => true,
                        "default_type" => "delivery",
                        "bin_size" => true
                    ]
                ]
            ],
        ];
        $order_line = parent::asJson()
            ->method('POST')
            ->withUrlParam('/web/dataset/call_kw/res.partner/read')
            ->withData($data_parts)
            ->get();
        return $order_line;
    }
}
