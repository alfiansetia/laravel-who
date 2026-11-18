<?php

namespace App\Services;

use App\Services\Odoo\OdooClient;
use App\Services\Odoo\OdooDomain;
use DateTimeImmutable;
use DateTimeZone;

class SoPickingServices
{
    private const LIST_FIELDS = [
        'confirmation_date_so', 'picking_id', 'origin', 'partner_id',
        'location_id', 'product_id', 'name', 'exp_date',
        'product_uom_qty', 'quantity_done', 'product_uom', 'state',
    ];

    private const STATES = ['waiting', 'confirmed', 'assigned', 'done'];

    public function __construct(private OdooClient $odoo) {}

    public function getAll(
        string $search = '',
        int $limit = 80,
        int $offset = 0,
        string $state = '',
        string $productSearch = '',
        string $date = '',
        int $locationId = 0,
    ): array {
        $domain = [
            ['picking_id.picking_type_id.code', '=', 'outgoing'],
            ['picking_id.state', 'in', self::STATES],
            ['picking_id.move_ids_without_package', '!=', false],
        ];

        [$from, $to] = static::resolveDayRange($date);
        if ($from !== null) {
            $domain[] = ['confirmation_date_so', '>=', $from];
        }
        if ($to !== null) {
            $domain[] = ['confirmation_date_so', '<', $to];
        }

        if ($locationId > 0) {
            $domain[] = ['location_id', '=', $locationId];
        }

        $search = trim($search);
        if ($search !== '') {
            array_push($domain, ...OdooDomain::or(
                OdooDomain::ilike('picking_id', $search),
                OdooDomain::ilike('origin', $search),
                OdooDomain::ilike('product_id', $search),
            ));
        }

        $state = trim($state);
        if ($state !== '' && in_array($state, self::STATES, true)) {
            $domain[] = ['state', '=', $state];
        }

        $productSearch = trim($productSearch);
        if ($productSearch !== '') {
            $domain[] = ['product_id', 'ilike', $productSearch];
        }

        return $this->odoo->searchRead(
            'stock.move',
            $domain,
            self::LIST_FIELDS,
            $limit,
            $offset,
            'confirmation_date_so DESC, create_date DESC, date_expected DESC',
        );
    }

    /**
     * Ubah satu tanggal (Y-m-d, zona WIB) menjadi rentang sehari penuh
     * dalam format UTC Odoo: [dari 00:00 WIB, sampai 00:00 WIB besok].
     * Return [null, null] bila format tidak valid.
     *
     * @return array{0: ?string, 1: ?string}
     */
    protected static function resolveDayRange(string $date): array
    {
        $date = trim($date);
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return [null, null];
        }
        try {
            $start = (new DateTimeImmutable($date.' 00:00:00', new DateTimeZone('Asia/Jakarta')))
                ->setTimezone(new DateTimeZone('UTC'));
            $end = $start->modify('+1 day');

            return [$start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')];
        } catch (\Throwable) {
            return [null, null];
        }
    }
}
