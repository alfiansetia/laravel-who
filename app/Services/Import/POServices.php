<?php

namespace App\Services\Import;

use App\Exceptions\OdooException;
use App\Services\Odoo;
use Illuminate\Support\Arr;

class POServices extends Odoo
{
    public function __construct() {}

    public static function getAll($query = 'PO', $limit = 80, $offset = null)
    {
        if (empty($query)) {
            $query = 'PO';
        }
        if (empty($limit)) {
            $limit = 80;
        }
        $url_param = '/web/dataset/search_read';
        $data = [
            "jsonrpc" => "2.0",
            "method" => "call",
            'id' => 392626529,
            "params" => [
                "model" => "purchase.order",
                "domain" => [
                    [
                        "name",
                        "ilike",
                        $query
                    ]
                ],
                "fields" => [
                    "message_unread",
                    "name",
                    "date_order",
                    "partner_id",
                    "company_id",
                    "date_planned",
                    "user_id",
                    "origin",
                    "picking_count",
                    "invoice_count",
                    "amount_untaxed",
                    "amount_total",
                    "currency_id",
                    "state",
                    "warna",
                    "is_green",
                    "is_yellow",
                    "is_red",
                    "invoice_status",
                    'order_line',
                    'notes'
                ],
                "limit" => intval($limit),
                "offset" => intval($offset),
                "sort" => "",
                "context" => [
                    "lang" => "en_US",
                    "tz" => "Asia/Jakarta",
                    "uid" => 192,
                    "params" => [
                        "id" => 1075,
                        "action" => 256,
                        "model" => "purchase.order",
                        "view_type" => "form",
                        "menu_id" => 138
                    ]
                ]
            ],
        ];
        $response  = parent::asJson()
            ->withUrlParam($url_param)
            ->method('POST')
            ->withData($data)
            ->get();
        if (!Arr::exists($response, 'result')) {
            throw new OdooException('Data Not Found!', 404, $response);
        }
        return Arr::get($response, 'result');
    }

    public static function detail(int $id)
    {
        $url_param = '/web/dataset/call_kw/purchase.order/read';
        $data = [
            "jsonrpc" => "2.0",
            "method" => "call",
            "params" => [
                "args" => [
                    [
                        $id
                    ],
                    [
                        'partner_address',
                        "cancel_done_picking",
                        "state",
                        "invoice_count",
                        "invoice_ids",
                        "picking_count",
                        "picking_ids",
                        "name",
                        "partner_id",
                        "partner_ref",
                        "purchase_manual_currency_rate_active",
                        "purchase_manual_currency_rate",
                        "manual_rate",
                        "currency_id",
                        "is_shipped",
                        "date_order",
                        "ship_to",
                        "ship_by",
                        "divisi",
                        "cancel_reason",
                        "origin",
                        "company_id",
                        "order_line",
                        "amount_untaxed",
                        "amount_tax",
                        "amount_total",
                        "amount_total1",
                        "amount_untaxed1",
                        "amount_tax1",
                        "notes",
                        "terbilang",
                        "date_planned",
                        "picking_type_id",
                        "dest_address_id",
                        "default_location_dest_id_usage",
                        "incoterm_id",
                        "eta",
                        "keterangan_eta",
                        "user_id",
                        "invoice_status",
                        "payment_term_id",
                        "fiscal_position_id",
                        "date_approve",
                        "message_follower_ids",
                        "activity_ids",
                        "message_ids",
                        "message_attachment_count",
                        "display_name",
                    ]
                ],
                "model" => "purchase.order",
                "method" => "read",
                "kwargs" => [
                    "context" => [
                        "lang" => "en_US",
                        "tz" => "Asia/Jakarta",
                        "uid" => 192,
                        "params" => [
                            "id" => 1075,
                            "action" => 256,
                            "model" => "purchase.order",
                            "view_type" => "form",
                            "menu_id" => 138
                        ],
                        "bin_size" => true
                    ]
                ]
            ],
            "id" => 181837902
        ];
        $response  = parent::asJson()
            ->withUrlParam($url_param)
            ->method('POST')
            ->withData($data)
            ->get();
        $res = Arr::get($response, 'result.0', null);
        if (!$res) {
            throw new OdooException('Data Not Found!', 404, $response);
        }
        try {
            $order_line = static::getOrderLines($res['order_line']);
            $res['order_line_detail'] = Arr::get($order_line, 'result', []);
        } catch (\Throwable $th) {
            $res['order_line_detail'] = [];
        }

        try {
            $partner_id = Arr::get($res, 'partner_id.0');
            $partner = static::getPartners([$partner_id]);
            $res['partner_detail'] = Arr::get($partner, 'result.0', null);
        } catch (\Throwable $th) {
            $res['partner_detail'] = null;
        }

        return $res;
    }

    public static function getOrderLines(array $line)
    {
        $line_int =  array_map('intval', array_filter($line, 'is_numeric'));
        $data_lines = [
            "jsonrpc" => "2.0",
            "method" => "call",
            "params" => [
                "args" => [
                    $line_int,
                    [
                        "currency_id",
                        "state",
                        "product_type",
                        "invoice_lines",
                        "sequence",
                        "product_id",
                        "name",
                        "date_planned",
                        "move_dest_ids",
                        "company_id",
                        "account_analytic_id",
                        "analytic_tag_ids",
                        "akl",
                        "hs_code",
                        "product_qty",
                        "discount",
                        "qty_received",
                        "qty_invoiced",
                        "product_uom",
                        "price_unit",
                        "price_unit1",
                        "price_subtotal1",
                        "taxes_id",
                        "price_subtotal",
                        "purchase_request_lines"
                    ]
                ],
                "model" => "purchase.order.line",
                "method" => "read",
                "kwargs" => [
                    "context" => [
                        "lang" => "en_US",
                        "tz" => "Asia/Jakarta",
                        "uid" => 192,
                        "params" => [
                            "id" => 1075,
                            "action" => 256,
                            "model" => "purchase.order",
                            "view_type" => "form",
                            "menu_id" => 138
                        ]
                    ]
                ]
            ],
            "id" => 75135116
        ];
        $order_lines = parent::asJson()
            ->method('POST')
            ->withUrlParam('/web/dataset/call_kw/purchase.order.line/read')
            ->withData($data_lines)
            ->get();
        return $order_lines;
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
