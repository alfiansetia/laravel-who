<?php

namespace App\Services;

use App\Exceptions\OdooException;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Arr;

class SoPickingServices extends Odoo
{
    public function __construct() {}

    public static function getAll(string $search = '', int $limit = 80, int $offset = 0, string $state = '', string $productSearch = '', string $date = '', int $locationId = 0): array
    {
        $domain = [
            [
                'picking_id.picking_type_id.code',
                '=',
                'outgoing',
            ],
            [
                'picking_id.state',
                'in',
                [
                    'waiting',
                    'confirmed',
                    'assigned',
                    'done',
                ],
            ],
            [
                'picking_id.move_ids_without_package',
                '!=',
                false,
            ],
        ];

        [$from, $to] = static::resolveDayRange($date);
        if ($from !== null) {
            $domain[] = [
                'confirmation_date_so',
                '>=',
                $from,
            ];
        }
        if ($to !== null) {
            $domain[] = [
                'confirmation_date_so',
                '<',
                $to,
            ];
        }

        if ($locationId > 0) {
            $domain[] = [
                'location_id',
                '=',
                $locationId,
            ];
        }

        $search = trim($search);
        if ($search !== '') {
            $domain[] = '|';
            $domain[] = '|';
            $domain[] = [
                'picking_id',
                'ilike',
                $search,
            ];
            $domain[] = [
                'origin',
                'ilike',
                $search,
            ];
            $domain[] = [
                'product_id',
                'ilike',
                $search,
            ];
        }

        $state = trim($state);
        if ($state !== '' && in_array($state, ['waiting', 'confirmed', 'assigned', 'done'], true)) {
            $domain[] = [
                'state',
                '=',
                $state,
            ];
        }

        $productSearch = trim($productSearch);
        if ($productSearch !== '') {
            $domain[] = [
                'product_id',
                'ilike',
                $productSearch,
            ];
        }

        $data = [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => [
                'model' => 'stock.move',
                'domain' => $domain,
                'fields' => [
                    'confirmation_date_so',
                    'picking_id',
                    'origin',
                    'partner_id',
                    'location_id',
                    'product_id',
                    'name',
                    'exp_date',
                    'product_uom_qty',
                    'quantity_done',
                    'product_uom',
                    'state',
                ],
                'limit' => intval($limit),
                'offset' => intval($offset),
                'sort' => 'confirmation_date_so DESC, create_date DESC, date_expected DESC',
                'context' => [
                    'lang' => 'en_US',
                    'tz' => 'Asia/Jakarta',
                    'uid' => 192,
                ],
            ],
        ];

        $response = parent::asJson()
            ->withUrlParam('/web/dataset/search_read')
            ->method('POST')
            ->withData($data)
            ->get();

        $res = Arr::get($response, 'result', null);
        if (! $res) {
            throw new OdooException('Data Not Found!', 404);
        }

        return $res;
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
