<?php

namespace App\Services;

use App\Services\Odoo\InteractsWithPicking;
use App\Services\Odoo\OdooClient;
use App\Services\Odoo\OdooDomain;

class DoMonitorService
{
    use InteractsWithPicking;

    private const LIST_FIELDS = [
        'name', 'confirmation_date_so', 'date', 'scheduled_date',
        'x_studio_no_do_manual', 'force_date', 'origin', 'note_to_wh',
        'move_ids_without_package', 'move_line_ids_without_package',
        'group_id', 'backorder_id', 'partner_id', 'location_dest_id',
        'location_id', 'x_studio_tags', 'x_studio_field_0rSR5',
        'priority_so', 'note_itr', 'invoice_state', 'state', 'priority',
        'picking_type_id',
    ];

    public function __construct(private OdooClient $odoo) {}

    public function getAll(): array
    {
        $domain = OdooDomain::and(
            ['picking_type_id', '=', 2],
            OdooDomain::or(
                ['state', '=', 'draft'],
                ['state', 'in', ['confirmed', 'waiting']],
                ['state', 'in', ['assigned', 'partially_available']],
            ),
        );
        $context = [
            'active_model' => 'stock.picking.type',
            'active_id' => 2,
            'active_ids' => [2],
            'search_default_picking_type_id' => [2],
            'default_picking_type_id' => 2,
            'contact_display' => 'partner_address',
            'search_default_available' => 1,
            'search_disable_custom_filters' => true,
        ];

        // Tahap 1: ambil length dulu agar tahap 2 tahu total limit.
        $first = $this->odoo->searchRead('stock.picking', $domain, ['id'], 1, 0, '', $context);
        $total = (int) ($first['length'] ?? 0);
        if ($total <= 0) {
            return [];
        }

        $res = $this->odoo->searchRead('stock.picking', $domain, self::LIST_FIELDS, $total, 0, '', $context);

        $moveIds = collect($res['records'] ?? [])
            ->pluck('move_ids_without_package')
            ->flatten()
            ->all();
        $details = collect($this->getOrderLines($moveIds))->keyBy('id');

        return collect($res['records'] ?? [])->map(function ($item) use ($details) {
            $item['move_ids_without_package_detail'] = collect($item['move_ids_without_package'] ?? [])
                ->map(fn ($id) => $details->get($id))
                ->filter()->values()->all();

            return $item;
        })->all();
    }
}
