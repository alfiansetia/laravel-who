<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class StockController extends Controller
{
    private $headers = [
        'accept'            => 'application/json, text/javascript, */*; q=0.01',
        'accept-language'   => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
        'content-type'      => 'application/json',
        'x-requested-with'  => 'XMLHttpRequest',
        'Cookie'            => '',
        'Accept-Encoding'   => 'gzip, deflate',
    ];

    public function __construct()
    {
        $setting = Setting::first();
        $this->headers['Cookie'] = 'session_id=' . $setting->odoo_session ?? '';
    }
    public function index(Request $request)
    {
        $url = 'http://map.integrasi.online:8069/web/dataset/call_kw/stock.quant/read_group';
        $data = [
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
            array_push($data['params']['kwargs']['domain'], '|');
        }

        foreach ($request->location ?? [] as $item) {
            array_push($data['params']['kwargs']['domain'], ['location_id', 'ilike', $item]);
        }
        // return response()->json(['data' => $data], 500);
        return $this->handle($url, $data, $this->headers);
    }

    private function handle($url, $data, $headers)
    {
        $response = Http::withHeaders($headers)->timeout(60)->post($url, $data);
        $json = $response->json();
        return response()->json(['status' => $response->status(), 'data' => $json['result'] ?? []], $response->status() ?? 500);
    }
}
