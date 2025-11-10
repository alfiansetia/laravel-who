<?php

namespace App\Services;

use Illuminate\Support\Arr;

class ProductServices extends Odoo
{
    public function __construct() {}

    public static function getAll($query = '', $limit = 7000, $offset = null)
    {
        $domains = [[
            'type',
            'in',
            [
                'consu',
                'product'
            ]
        ]];
        if (!empty($query)) {
            $domains =     [
                [
                    "type",
                    "in",
                    [
                        "consu",
                        "product"
                    ]
                ],
                "|",
                "|",
                "|",
                [
                    "default_code",
                    "ilike",
                    "bdf"
                ],
                [
                    "product_variant_ids.default_code",
                    "ilike",
                    "bdf"
                ],
                [
                    "name",
                    "ilike",
                    "bdf"
                ],
                [
                    "barcode",
                    "ilike",
                    "bdf"
                ]
            ];
        }
        $url_param = '/web/dataset/search_read';
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => [
                'model'     => 'product.template',
                "limit"     => intval($limit),
                "offset"    => intval($offset),
                'sort'      => 'default_code ASC',
                'domain' => $domains,
                'fields' => [
                    'id',
                    'img_small',
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
            ],
        ];
        $response  = parent::asJson()
            ->withUrlParam($url_param)
            ->method('POST')
            ->withData($data)
            ->get();
        return Arr::get($response, 'result.records', []);
    }
}
