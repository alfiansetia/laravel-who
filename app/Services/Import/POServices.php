<?php

namespace App\Services\Import;

use App\Services\Odoo\InteractsWithPicking;
use App\Services\Odoo\OdooClient;
use App\Services\Odoo\OdooDomain;
use Illuminate\Support\Arr;

class POServices
{
    use InteractsWithPicking;

    private const LIST_FIELDS = [
        'message_unread', 'name', 'date_order', 'partner_id', 'company_id',
        'date_planned', 'user_id', 'origin', 'picking_count',
        'invoice_count', 'amount_untaxed', 'amount_total', 'currency_id',
        'state', 'warna', 'is_green', 'is_yellow', 'is_red',
        'invoice_status', 'order_line', 'notes',
    ];

    public function __construct(private OdooClient $odoo) {}

    public function getAll(string $query = 'PO', int $limit = 80, int $offset = 0, string $product_search = ''): array
    {
        $query = trim($query) === '' ? 'PO' : $query;

        $domain = [OdooDomain::ilike('name', $query)];

        if (trim($product_search) !== '') {
            $domain[] = ['order_line.product_id', 'ilike', trim($product_search)];
        }

        return $this->odoo->searchRead('purchase.order', $domain, self::LIST_FIELDS, $limit, $offset, '', [
            'params' => [
                'id' => 1075, 'action' => 256, 'model' => 'purchase.order',
                'view_type' => 'form', 'menu_id' => 138,
            ],
        ]);
    }

    public function detail(int $id): array
    {
        $res = $this->odoo->readOne('purchase.order', $id, [
            'partner_address', 'cancel_done_picking', 'state',
            'invoice_count', 'invoice_ids', 'picking_count', 'picking_ids',
            'name', 'partner_id', 'partner_ref',
            'purchase_manual_currency_rate_active',
            'purchase_manual_currency_rate', 'manual_rate', 'currency_id',
            'is_shipped', 'date_order', 'ship_to', 'ship_by', 'divisi',
            'cancel_reason', 'origin', 'company_id', 'order_line',
            'amount_untaxed', 'amount_tax', 'amount_total',
            'amount_total1', 'amount_untaxed1', 'amount_tax1', 'notes',
            'terbilang', 'date_planned', 'picking_type_id',
            'dest_address_id', 'default_location_dest_id_usage',
            'incoterm_id', 'eta', 'keterangan_eta', 'user_id',
            'invoice_status', 'payment_term_id', 'fiscal_position_id',
            'date_approve', 'message_follower_ids', 'activity_ids',
            'message_ids', 'message_attachment_count', 'display_name',
        ], [
            'params' => [
                'id' => 1075, 'action' => 256, 'model' => 'purchase.order',
                'view_type' => 'form', 'menu_id' => 138,
            ],
            'bin_size' => true,
        ]);

        try {
            $res['order_line_detail'] = $this->getOrderLines($res['order_line'] ?? []);
        } catch (\Throwable) {
            $res['order_line_detail'] = [];
        }

        try {
            $partners = $this->getPartners([Arr::get($res, 'partner_id.0')]);
            $res['partner_detail'] = $partners[0] ?? null;
        } catch (\Throwable) {
            $res['partner_detail'] = null;
        }

        return $res;
    }

    /** @return array<int, array> */
    public function getOrderLines(array $line): array
    {
        return $this->odoo->read('purchase.order.line', $line, [
            'currency_id', 'state', 'product_type', 'invoice_lines',
            'sequence', 'product_id', 'name', 'date_planned',
            'move_dest_ids', 'company_id', 'account_analytic_id',
            'analytic_tag_ids', 'akl', 'hs_code', 'product_qty',
            'discount', 'qty_received', 'qty_invoiced', 'product_uom',
            'price_unit', 'price_unit1', 'price_subtotal1', 'taxes_id',
            'price_subtotal', 'purchase_request_lines',
        ], [
            'params' => [
                'id' => 1075, 'action' => 256, 'model' => 'purchase.order',
                'view_type' => 'form', 'menu_id' => 138,
            ],
        ]);
    }
}
