<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Odoo;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function index()
    {
        $data = Product::orderBy('code', 'ASC')->get();
        return $this->sendResponse($data, 'Success!');
    }

    public function show($id)
    {
        $data = Product::query()->with(['packs.items', 'sop.items'])->find($id);
        if (!$data) {
            return $this->sendNotFound();
        }
        return $this->sendResponse($data, 'Success!');
    }

    public function sync()
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => [
                'model' => 'product.template',
                'domain' => [
                    [
                        'type',
                        'in',
                        [
                            'consu',
                            'product'
                        ]
                    ]
                ],
                'fields' => [
                    'sequence',
                    'default_code',
                    'name',
                    'categ_id',
                    'akl_id',
                    'x_studio_valid_to_akl',
                    'type',
                    'qty_available',
                    'virtual_available',
                    'uom_id',
                    'active',
                    'x_studio_field_i3XMM',
                    'description'
                ],
                'limit' => 7000,
                'sort' => 'default_code ASC',
            ],
            'id' => 353031512
        ];
        try {
            $url_param = '/web/dataset/search_read';
            $json = Odoo::asJson()
                ->withUrlParam($url_param)
                ->withData($data)
                ->method('POST')
                ->get();
            $records = $json['result']['records'] ?? [];
            $chunks = array_chunk($records, 100);
            foreach ($chunks as $chunk) {
                foreach ($chunk as $item) {
                    Product::query()->updateOrCreate([
                        'code' => $item['default_code'],
                    ], [
                        'odoo_id'   => $item['id'],
                        'code'      => $item['default_code'],
                        'name'      => $item['name'] ?? null,
                        'akl'       => $item['akl_id'] != false ? $item['akl_id'][1] : null,
                        'akl_exp'   => $item['x_studio_valid_to_akl'] != false ? date('Y-m-d', strtotime($item['x_studio_valid_to_akl'])) : null,
                        'desc'      => $item['description'] != false ? $item['description'] : null,
                    ]);
                }
            }
            return response()->json(['message' => 'Success!', 'data' => $json['result']],);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage(), 'data' => []], 500);
        }
    }
}
