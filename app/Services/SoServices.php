<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Arr;

class SoServices extends Odoo
{
    public function __construct() {}

    public static function getAll(string $query = '', int $limit = 80, int $offset = 0)
    {
        $url_param = '/web/dataset/search_read';
        $data =         [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => [
                'model' => "sale.order",
                'domain' => [
                    "|",
                    "|",
                    [
                        "name",
                        "ilike",
                        $query
                    ],
                    [
                        "client_order_ref",
                        "ilike",
                        $query
                    ],
                    [
                        "partner_id",
                        "child_of",
                        $query
                    ]
                ],
                'fields' => [
                    "message_needaction",
                    "name",
                    "date_order",
                    "commitment_date",
                    "expected_date",
                    "partner_id",
                    "partner_invoice_id",
                    "user_id",
                    "no_aks",
                    "no_po",
                    "delivery_count",
                    "invoice_count",
                    "amount_untaxed",
                    "amount_discount",
                    "amount_tax",
                    "amount_total",
                    "currency_id",
                    "state",
                    "approve_nego",
                    "tingkat_approve",
                    "approve_id",
                    "option",
                    "total_nego_order",
                    "total_nego_item",
                    "total_dpk",
                    "invoice_status",
                    "no_invoice",
                    "no_delivery_order",
                    "warna",
                    "is_green",
                    "is_yellow",
                    "is_red",
                    "note_to_wh"
                ],
                "limit" => intval($limit),
                "offset" => intval($offset),
                "sort" => "",
                'context' => [
                    'lang' => 'en_US',
                    'tz' => 'Asia/Jakarta',
                    'search_default_my_quotation' => 1
                ]
            ],
        ];
        $response  = parent::asJson()
            ->withUrlParam($url_param)
            ->method('POST')
            ->withData($data)
            ->get();
        if (!isset($response['result'])) {
            throw new Exception('Odoo Error');
        }
        return $response['result'];
    }

    public static function detail(int $id)
    {
        $url_param = '/web/dataset/call_kw/sale.order/read';
        $data = [
            'jsonrpc'   => '2.0',
            'method'    => 'call',
            'params'    => [
                'args'  => [
                    [intval($id)],
                    [
                        "authorized_transaction_ids",
                        "state",
                        "cancel_done_picking",
                        "customer_receivable_amount",
                        "picking_ids",
                        "delivery_count",
                        "expense_count",
                        "invoice_count",
                        "name",
                        "no_ipak",
                        "partner_id",
                        "print_total_nego_order",
                        "print_total_nego_include",
                        "print_total_nego_item",
                        "print_total_dpk",
                        "print_distrik",
                        "red_flag_partner",
                        "colour_red_flag_partner",
                        "partner_invoice_id",
                        "red_flag_partner_invoice",
                        "colour_red_flag_partner_invoice",
                        "partner_shipping_id",
                        "colour_red_flag_shipping",
                        "sold_to",
                        "bill_to",
                        "delivery_manual",
                        "nama_ekspedisi",
                        "pp",
                        "ppk",
                        "related_customer_universal",
                        "red_flag_partner_shipping",
                        "sale_order_template_id",
                        "validity_date",
                        "confirmation_date",
                        "pricelist_id",
                        "currency_id",
                        "payment_term_id",
                        "lembar_negosiasi",
                        "achievement_ex_related",
                        "cancel_reason",
                        "customer_available_amount",
                        "customer_credit_limit",
                        "is_warning",
                        "sale_manual_currency_rate_active",
                        "sale_manual_currency_rate",
                        "no_aks",
                        "x_studio_vendor_1",
                        "no_po",
                        "no_ska",
                        "no_sph",
                        "no_si",
                        "tgl_akhir_kontrak",
                        "instansi",
                        "distributor_so",
                        "rekanan_so",
                        "sumber_dana",
                        "sistem",
                        "note_to_wh",
                        "payment_reference",
                        "order_line",
                        "amount_untaxed",
                        "amount_discount",
                        "amount_tax",
                        "ongkir_amount",
                        "amount_total",
                        "enable_discount",
                        "global_discount_type",
                        "global_discount_rate",
                        "plus_disc",
                        "note",
                        "disc_tax_id",
                        "disc_tax_id1",
                        "sale_order_option_ids",
                        "warehouse_id",
                        "incoterm",
                        "picking_policy",
                        "expected_date",
                        "commitment_date",
                        "effective_date",
                        "user_id",
                        "department_id",
                        "level_komisi",
                        "tag_dept",
                        "tag_area",
                        "tag_kota",
                        "tag_area_new",
                        "tag_dept_new1",
                        "tag_kota_new",
                        "detail_kota",
                        "tag_ids",
                        "team_id",
                        "client_order_ref",
                        "require_signature",
                        "require_payment",
                        "reference",
                        "company_id",
                        "analytic_account_id",
                        "date_order",
                        "fiscal_position_id",
                        "invoice_status",
                        "invoice_policy",
                        "origin",
                        "opportunity_id",
                        "campaign_id",
                        "medium_id",
                        "source_id",
                        "detail_order",
                        "total_nego_order",
                        "total_nego_include",
                        "total_cogs",
                        "margin_persen",
                        "total_m",
                        "total_rsm",
                        "total_hm",
                        "total_d",
                        "total_mxd",
                        "total_rsmxd",
                        "total_hmxd",
                        "total_dxd",
                        "total_xd",
                        "total_m_xd",
                        "total_rsm_xd",
                        "total_hm_xd",
                        "total_d_xd",
                        "nilai_xd_m",
                        "nilai_xd_rsm",
                        "nilai_xd_hm",
                        "nilai_xd_d",
                        "approve_nego",
                        "tingkat_approve",
                        "approve_id",
                        "no_approve_id",
                        "alasan_tidak_setuju",
                        "option",
                        "detail_item_nego",
                        "total_nego_item",
                        "total_pengurang_mhd",
                        "detail_ska",
                        "total_dpk",
                        "detail_historical",
                        "negosiasi_ids",
                        "nego_so_id",
                        "nego_finansial_id",
                        "print_layout_id",
                        "amount_untaxed_layout",
                        "amount_discount_layout",
                        "amount_tax_layout",
                        "ongkir_amount_layout",
                        "amount_total_layout",
                        "message_follower_ids",
                        "activity_ids",
                        "message_ids",
                        "message_attachment_count",
                        "display_name"
                    ]
                ],
                'model' => 'sale.order',
                'method' => 'read',
                'kwargs' => [
                    'context' => [
                        "lang" => "en_US",
                        "tz" => "Asia/Jakarta",
                        "uid" => 192,
                        "search_default_my_quotation" => 1,
                        "bin_size" => true
                    ]
                ]
            ],
        ];
        $response  = parent::asJson()
            ->withUrlParam($url_param)
            ->method('POST')
            ->withData($data)
            ->get();
        try {
            $order_ids = static::getOrderLines($response['result'][0]['order_line'] ?? []);
            $response['result'][0]['order_line_detail'] = $order_ids['result'] ?? [];
        } catch (\Throwable $th) {
            $response['result'][0]['order_line_detail'] = [];
        }
        try {
            $partner_ids = array_unique(array_filter([
                Arr::get($response, 'result.0.partner_id.0'),
                Arr::get($response, 'result.0.partner_invoice_id.0'),
                Arr::get($response, 'result.0.partner_shipping_id.0'),
            ]));
            $partners = static::getPartners(array_values($partner_ids));
            $part_res = $partners['result'] ?? [];
            $response['result'][0]['partner_id_detail'] = collect($part_res)
                ->where('id', Arr::get($response, 'result.0.partner_id.0'))
                ->first();
            $response['result'][0]['partner_invoice_id_detail'] = collect($part_res)
                ->where('id', Arr::get($response, 'result.0.partner_invoice_id.0'))
                ->first();
            $response['result'][0]['partner_shipping_id_detail'] = collect($part_res)
                ->where('id', Arr::get($response, 'result.0.partner_shipping_id.0'))
                ->first();
            $response['result'][0]['partner_details'] = $part_res;
        } catch (\Throwable $th) {
            $response['result'][0]['partner_id_detail'] = [];
            $response['result'][0]['partner_invoice_id_detail'] = [];
            $response['result'][0]['partner_shipping_id_detail'] = [];
            $response['result'][0]['partner_details'] = [];
        }
        return Arr::get($response, 'result.0', []);
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
                        "sequence",
                        "display_type",
                        "product_updatable",
                        "product_id",
                        "product_custom_attribute_value_ids",
                        "product_no_variant_attribute_value_ids",
                        "name",
                        "can_invoice",
                        "default_code",
                        "product_uom_qty",
                        "qty_delivered",
                        "qty_delivered_manual",
                        "qty_delivered_method",
                        "stat_inv",
                        "qty_invoiced",
                        "qty_to_invoice",
                        "satuan_fisik_id",
                        "product_uom",
                        "analytic_tag_ids",
                        "analytic_account_ids",
                        "price_unit",
                        "unit_price1",
                        "price_subtotal1",
                        "plus_disc",
                        "tax_id",
                        "discount",
                        "sale_line_ppn",
                        "price_subtotal",
                        "price_total",
                        "state",
                        "invoice_status",
                        "customer_lead",
                        "currency_id",
                        "price_tax"
                    ]
                ],
                'model' => 'sale.order.line',
                'method' => 'read',
                'kwargs' => [
                    'context' => [
                        "lang" => "en_US",
                        "tz" => "Asia/Jakarta",
                        "uid" => 192,
                        "search_default_my_quotation" => 1
                    ]
                ]
            ],
        ];
        $order_line = parent::asJson()
            ->method('POST')
            ->withUrlParam('/web/dataset/call_kw/sale.order.line/read')
            ->withData($data_line)
            ->get();
        try {
            $tax_ids = [];
            foreach ($order_line['result'] as $value) {
                $line_taxes = Arr::get($value, 'tax_id', []);
                if (is_array($line_taxes)) {
                    $tax_ids = array_merge($tax_ids, $line_taxes);
                }
            }
            $tax_ids = array_values(array_unique(array_filter($tax_ids)));

            $tax_response = static::getTaxes($tax_ids);
            $tax_res = $tax_response['result'] ?? [];

            foreach ($order_line['result'] as $key => $value) {
                $first_tax_id = Arr::get($value, 'tax_id.0');
                $order_line['result'][$key]['tax_id_detail'] = collect($tax_res)
                    ->where('id', $first_tax_id)
                    ->first();
            }
        } catch (\Throwable $th) {
            foreach ($order_line['result'] as $key => $value) {
                $order_line['result'][$key]['tax_id_detail'] = [];
            }
        }
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

    public static function getTaxes(array $ids)
    {
        $id_tax =  array_map('intval', array_filter($ids, 'is_numeric'));
        $data_tax = [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => [
                'args' => [
                    $id_tax,
                    [
                        "display_name"
                    ]
                ],
                'model' => 'account.tax',
                'method' => 'read',
                'kwargs' => [
                    'context' => [
                        "lang" => "en_US",
                        "tz" => "Asia/Jakarta",
                        "uid" => 192,
                        "search_default_my_quotation" => 1
                    ]
                ]
            ],
        ];
        $order_line = parent::asJson()
            ->method('POST')
            ->withUrlParam('/web/dataset/call_kw/account.tax/read')
            ->withData($data_tax)
            ->get();
        return $order_line;
    }
}
