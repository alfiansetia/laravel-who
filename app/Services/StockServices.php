<?php

namespace App\Services;

use App\Services\Odoo;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class StockServices extends Odoo
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public static function getAll(array $locations = [])
    {
        $param = [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => [
                'args' => [],
                'model' => 'stock.quant',
                'method' => 'read_group',
                'kwargs' => [
                    'context' => [
                        'lang'          => 'en_US',
                        'tz'            => 'GMT',
                        'uid'           => 192,
                        'active_model'  => 'stock.quantity.history',
                        'active_id'     => 1106,
                        'active_ids'    => [1106],
                        'search_default_internal_loc' => 1,
                        'group_by'      => ['product_id', 'location_id'],
                        'search_disable_custom_filters' => true
                    ],
                    'domain' => [
                        ['location_id.usage', '=', 'internal'],
                        // ['location_id', 'ilike', 'center']
                    ],
                    'fields' => [
                        'product_id',
                        'location_id',
                        'lot_id',
                        'itds_expired',
                        'package_id',
                        'owner_id',
                        'reserved_quantity',
                        'quantity',
                        'product_uom_id',
                        'write_date',
                        'company_id'
                    ],
                    'groupby' => ['product_id', 'location_id'],
                    'orderby' => '',
                    'lazy' => true
                ]
            ],
            'id' => 432008837
        ];
        for ($i = 0; $i < (count($locations ?? []) - 1); $i++) {
            array_push($param['params']['kwargs']['domain'], '|');
        }

        foreach ($locations ?? [] as $item) {
            array_push($param['params']['kwargs']['domain'], ['location_id', 'ilike', $item]);
        }
        $url_param = '/web/dataset/call_kw/stock.quant/read_group';
        $response = parent::asJson()
            ->withData($param)
            ->withUrlParam($url_param)
            ->method('POST')
            ->get();
        $data = collect(Arr::get($response, 'result', []) ?? []);
        return $data->map(function ($item) {
            $pecah_code = pecah_code($item['product_id']);
            $id = $pecah_code[0];
            $code = $pecah_code[1];
            $name = $pecah_code[2];
            return [
                'id'        => $id,
                'quantity'  => $item['quantity'] ?? 0,
                'code'      => $code,
                'name'      => $name,
                'original'  => $item,
            ];
        });
    }

    public static function lot(int $id, $locations = [], int $limit = 10)
    {
        $param = [
            "jsonrpc" => "2.0",
            "method" => "call",
            "params" => [
                "model" => "stock.quant",
                "domain" => [
                    [
                        "product_id",
                        "in",
                        [
                            $id,
                        ]
                    ],
                ],
                "fields" => [
                    "product_id",
                    "location_id",
                    "lot_id",
                    "itds_expired",
                    "package_id",
                    "owner_id",
                    "reserved_quantity",
                    "quantity",
                    "product_uom_id",
                    "write_date",
                    "company_id"
                ],
                "limit" => $limit,
                "sort" => "",
                "context" => [
                    "lang" => "en_US",
                    "tz" => "Asia/Jakarta",
                    "uid" => 192,
                    "active_model" => "product.template",
                    "active_id" => 21418,
                    "active_ids" => [
                        21418
                    ],
                    "search_default_internal_loc" => 1,
                    "search_disable_custom_filters" => true
                ]
            ],
            "id" => 520742991
        ];
        for ($i = 0; $i < (count($locations ?? []) - 1); $i++) {
            array_push($param['params']['domain'], '|');
        }

        foreach ($locations ?? [] as $item) {
            array_push($param['params']['domain'], ['location_id', 'ilike', $item]);
        }
        $url_param = '/web/dataset/search_read';
        $response = parent::asJson()
            ->withData($param)
            ->withUrlParam($url_param)
            ->method('POST')
            ->get();
        $data = collect(Arr::get($response, 'result.records', []) ?? []);
        return $data->map(function ($item) {
            $loc = get_name($item['location_id'] ?? null);
            $lot = get_name($item['lot_id'] ?? null);
            $pecah_code = pecah_code($item['product_id'] ?? null);
            $id = $pecah_code[0];
            $code = $pecah_code[1];
            $name = $pecah_code[2];
            $expired = $item['itds_expired'] ?? null;
            $expired_ori = $expired;
            try {
                $expired = Carbon::createFromFormat('Y-m-d H:i:s', $expired, 'UTC')
                    ->setTimezone(config('app.timezone'))
                    ->format('Y.m.d');
            } catch (\Throwable $th) {
                //throw $th;
            }
            return [
                'id'            => $id,
                'quantity'      => $item['quantity'] ?? 0,
                'code'          => $code,
                'name'          => $name,
                'location'      => $loc,
                'lot'           => $lot,
                'expired'       => $expired,
                'expired_ori'   => $expired_ori,
                'original'      => $item,
            ];
        });
    }

    public static function getLocationAlias(string $code)
    {
        return match (strtolower($code)) {
            'center'    => 'CENTER',
            'cbb'       => 'CIBUBUR',
            'krtn'      => 'KARANTINA',
            'badstock'  => 'BADSTOCK',
            'demo'      => 'DEMO',
            default     => $code,
        };
    }
}
