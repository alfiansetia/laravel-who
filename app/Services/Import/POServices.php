<?php

namespace App\Services\Import;

use App\Services\OdooServices;
use Exception;

class POServices extends OdooServices
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
        $response  = parent::as_json()
            ->url_param($url_param)
            ->method('POST')
            ->data($data)
            ->get();
        try {
            $order_line = static::get_order_line($response['result'][0]['order_line']);
            $response['result'][0]['order_line_detail'] = $order_line['result'] ?? [];
        } catch (\Throwable $th) {
            $response['result'][0]['order_line_detail'] = [];
            //throw $th;
        }

        return $response['result'];
    }

    public static function get_order_line(array $line)
    {
        $line_int =  array_map('intval', array_filter($line, 'is_numeric'));
        $data_line = [
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
        $order_line = parent::as_json()->method('POST')
            ->url_param('/web/dataset/call_kw/purchase.order.line/read')
            ->data($data_line)->get();
        return $order_line;
    }
}
