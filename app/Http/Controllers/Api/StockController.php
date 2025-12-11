<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LotResource;
use App\Http\Resources\StockResource;
use App\Services\Odoo;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class StockController extends Controller
{
    public function index(Request $request)
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
        for ($i = 0; $i < (count($request->location ?? []) - 1); $i++) {
            array_push($param['params']['kwargs']['domain'], '|');
        }

        foreach ($request->location ?? [] as $item) {
            array_push($param['params']['kwargs']['domain'], ['location_id', 'ilike', $item]);
        }
        $url_param = '/web/dataset/call_kw/stock.quant/read_group';
        $data = Odoo::asJson()
            ->withData($param)
            ->withUrlParam($url_param)
            ->method('POST')
            ->get();
        return $this->sendResponse(['data' => StockResource::collection(Arr::get($data, 'result', []) ?? [])]);
    }

    public function lot(Request $request, int $id)
    {
        $limit = 10;
        if ($request->filled('limit')) {
            $limit = intval($request->limit);
        }
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
                            intval($id),
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
                    "tz" => "GMT",
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
        for ($i = 0; $i < (count($request->location ?? []) - 1); $i++) {
            array_push($param['params']['domain'], '|');
        }

        foreach ($request->location ?? [] as $item) {
            array_push($param['params']['domain'], ['location_id', 'ilike', $item]);
        }
        $url_param = '/web/dataset/search_read';
        $data = Odoo::asJson()
            ->withData($param)
            ->withUrlParam($url_param)
            ->method('POST')
            ->get();
        // return response()->json($data);
        return $this->sendResponse(LotResource::collection(Arr::get($data, 'result.records', [])));
    }
}
