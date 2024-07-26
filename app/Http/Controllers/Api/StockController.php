<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StockResource;
use App\Services\OdooService;
use Illuminate\Http\Request;

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
                        'product_id', 'location_id', 'lot_id', 'itds_expired',
                        'package_id', 'owner_id', 'reserved_quantity', 'quantity',
                        'product_uom_id', 'write_date', 'company_id'
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
        try {
            $url_param = '/web/dataset/call_kw/stock.quant/read_group';
            $odoo_service = new OdooService();
            $data = $odoo_service->data($param)->url_param($url_param)->as_json()->method('POST')->get();
            return response()->json(['data' => StockResource::collection($data['result'] ?? [])]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage(), 'data' => []], 500);
        }
    }
}
