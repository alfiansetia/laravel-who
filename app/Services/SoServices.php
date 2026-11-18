<?php

namespace App\Services;

use App\Services\Odoo\InteractsWithPicking;
use App\Services\Odoo\OdooClient;
use App\Services\Odoo\OdooDomain;
use Illuminate\Support\Arr;

class SoServices
{
    use InteractsWithPicking;

    private const LIST_FIELDS = [
        'message_needaction', 'name', 'date_order', 'commitment_date',
        'expected_date', 'partner_id', 'partner_invoice_id', 'user_id',
        'no_aks', 'no_po', 'delivery_count', 'invoice_count',
        'amount_untaxed', 'amount_discount', 'amount_tax', 'amount_total',
        'currency_id', 'state', 'approve_nego', 'tingkat_approve',
        'approve_id', 'option', 'total_nego_order', 'total_nego_item',
        'total_dpk', 'invoice_status', 'no_invoice', 'no_delivery_order',
        'warna', 'is_green', 'is_yellow', 'is_red', 'note_to_wh', 'sistem',
    ];

    public function __construct(private OdooClient $odoo) {}

    public function getAll(string $query = '', int $limit = 80, int $offset = 0, array $filter = [], string $product_search = ''): array
    {
        $domain = OdooDomain::and(
            OdooDomain::or(
                OdooDomain::ilike('name', $query),
                OdooDomain::ilike('client_order_ref', $query),
                ['partner_id', 'child_of', $query],
            ),
            self::sanitizeFilter($filter),
        );

        if (trim($product_search) !== '') {
            $domain[] = ['order_line.product_id', 'ilike', trim($product_search)];
        }

        return $this->odoo->searchRead('sale.order', $domain, self::LIST_FIELDS, $limit, $offset, '', [
            'search_default_my_quotation' => 1,
        ]);
    }

    public function detail(int $id): array
    {
        $res = $this->odoo->readOne('sale.order', $id, [
            'authorized_transaction_ids', 'state', 'cancel_done_picking',
            'customer_receivable_amount', 'picking_ids', 'delivery_count',
            'expense_count', 'invoice_count', 'name', 'no_ipak',
            'partner_id', 'print_total_nego_order',
            'print_total_nego_include', 'print_total_nego_item',
            'print_total_dpk', 'print_distrik', 'red_flag_partner',
            'colour_red_flag_partner', 'partner_invoice_id',
            'red_flag_partner_invoice', 'colour_red_flag_partner_invoice',
            'partner_shipping_id',
            'colour_red_flag_shipping', 'sold_to', 'bill_to',
            'delivery_manual', 'nama_ekspedisi', 'pp', 'ppk',
            'related_customer_universal', 'red_flag_partner_shipping',
            'sale_order_template_id', 'validity_date', 'confirmation_date',
            'pricelist_id', 'currency_id', 'payment_term_id',
            'lembar_negosiasi', 'achievement_ex_related', 'cancel_reason',
            'customer_available_amount', 'customer_credit_limit',
            'is_warning', 'sale_manual_currency_rate_active',
            'sale_manual_currency_rate', 'no_aks', 'x_studio_vendor_1',
            'no_po', 'no_ska', 'no_sph', 'no_si', 'tgl_akhir_kontrak',
            'instansi', 'distributor_so', 'rekanan_so', 'sumber_dana',
            'sistem', 'note_to_wh', 'payment_reference', 'order_line',
            'amount_untaxed', 'amount_discount', 'amount_tax',
            'ongkir_amount', 'amount_total', 'enable_discount',
            'global_discount_type', 'global_discount_rate', 'plus_disc',
            'note', 'disc_tax_id', 'disc_tax_id1', 'sale_order_option_ids',
            'warehouse_id', 'incoterm', 'picking_policy', 'expected_date',
            'commitment_date', 'effective_date', 'user_id', 'department_id',
            'level_komisi', 'tag_dept', 'tag_area', 'tag_kota',
            'tag_area_new', 'tag_dept_new1', 'tag_kota_new', 'detail_kota',
            'tag_ids', 'team_id', 'client_order_ref', 'require_signature',
            'require_payment', 'reference', 'company_id',
            'analytic_account_id', 'date_order', 'fiscal_position_id',
            'invoice_status', 'invoice_policy', 'origin', 'opportunity_id',
            'campaign_id', 'medium_id', 'source_id', 'detail_order',
            'total_nego_order', 'total_nego_include', 'total_cogs',
            'margin_persen', 'total_m', 'total_rsm', 'total_hm', 'total_d',
            'total_mxd', 'total_rsmxd', 'total_hmxd', 'total_dxd',
            'total_xd', 'total_m_xd', 'total_rsm_xd', 'total_hm_xd',
            'total_d_xd', 'nilai_xd_m', 'nilai_xd_rsm', 'nilai_xd_hm',
            'nilai_xd_d', 'approve_nego', 'tingkat_approve', 'approve_id',
            'no_approve_id', 'alasan_tidak_setuju', 'option',
            'detail_item_nego', 'total_nego_item', 'total_pengurang_mhd',
            'detail_ska', 'total_dpk', 'detail_historical', 'negosiasi_ids',
            'nego_so_id', 'nego_finansial_id', 'print_layout_id',
            'amount_untaxed_layout', 'amount_discount_layout',
            'amount_tax_layout', 'ongkir_amount_layout',
            'amount_total_layout', 'message_follower_ids', 'activity_ids',
            'message_ids', 'message_attachment_count', 'display_name',
        ], [
            'search_default_my_quotation' => 1,
            'bin_size' => true,
        ]);

        try {
            $res['order_line_detail'] = $this->getSaleOrderLines($res['order_line'] ?? []);
        } catch (\Throwable) {
            $res['order_line_detail'] = [];
        }

        try {
            $partnerIds = array_values(array_unique(array_filter([
                Arr::get($res, 'partner_id.0'),
                Arr::get($res, 'partner_invoice_id.0'),
                Arr::get($res, 'partner_shipping_id.0'),
            ])));
            $partners = $this->getPartners($partnerIds);
            $res['partner_id_detail'] = collect($partners)->where('id', Arr::get($res, 'partner_id.0'))->first();
            $res['partner_invoice_id_detail'] = collect($partners)->where('id', Arr::get($res, 'partner_invoice_id.0'))->first();
            $res['partner_shipping_id_detail'] = collect($partners)->where('id', Arr::get($res, 'partner_shipping_id.0'))->first();
            $res['partner_details'] = $partners;
        } catch (\Throwable) {
            $res['partner_id_detail'] = [];
            $res['partner_invoice_id_detail'] = [];
            $res['partner_shipping_id_detail'] = [];
            $res['partner_details'] = [];
        }

        return $res;
    }

    /** @return array<int, array> */
    public function getSaleOrderLines(array $line): array
    {
        $lines = $this->odoo->read('sale.order.line', $line, [
            'sequence', 'display_type', 'product_updatable', 'product_id',
            'product_custom_attribute_value_ids',
            'product_no_variant_attribute_value_ids', 'name', 'can_invoice',
            'default_code', 'product_uom_qty', 'qty_delivered',
            'qty_delivered_manual', 'qty_delivered_method', 'stat_inv',
            'qty_invoiced', 'qty_to_invoice', 'satuan_fisik_id',
            'product_uom', 'analytic_tag_ids', 'analytic_account_ids',
            'price_unit', 'unit_price1', 'price_subtotal1', 'plus_disc',
            'tax_id', 'discount', 'sale_line_ppn', 'price_subtotal',
            'price_total', 'state', 'invoice_status', 'customer_lead',
            'currency_id', 'price_tax',
        ], ['search_default_my_quotation' => 1]);

        try {
            $taxIds = array_values(array_unique(array_filter(
                collect($lines)->flatMap(fn ($v) => Arr::get($v, 'tax_id', []))->all(),
                'is_numeric',
            )));
            $taxes = $this->getTaxes($taxIds);

            foreach ($lines as $key => $value) {
                $lines[$key]['tax_id_detail'] = collect($taxes)
                    ->where('id', Arr::get($value, 'tax_id.0'))
                    ->first();
            }
        } catch (\Throwable) {
            foreach ($lines as $key => $value) {
                $lines[$key]['tax_id_detail'] = [];
            }
        }

        return $lines;
    }

    /** @return array<int, array> */
    public function getTaxes(array $ids): array
    {
        return $this->odoo->read('account.tax', $ids, ['display_name'], [
            'search_default_my_quotation' => 1,
        ]);
    }

    public function writeNote(int $id, string $note = ''): mixed
    {
        return $this->odoo->write('sale.order', $id, ['note_to_wh' => $note], [
            'search_default_my_quotation' => 1,
        ]);
    }

    /**
     * Odoo `ilike` sudah substring-match otomatis — kupas `%` gaya SQL
     * yang kadang dikirim caller lama agar tidak jadi literal.
     *
     * @param  array<int, array|string>  $filter
     * @return array<int, array|string>
     */
    private static function sanitizeFilter(array $filter): array
    {
        foreach ($filter as $i => $cond) {
            if (is_array($cond) && count($cond) === 3
                && in_array(strtolower((string) $cond[1]), ['ilike', 'not ilike'], true)
                && is_string($cond[2])
            ) {
                $filter[$i][2] = trim($cond[2], "% \t\n\r\0\x0B");
            }
        }

        return $filter;
    }
}
