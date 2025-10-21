<?php

namespace App\Services;

use Exception;

class ProductMoveService extends Odoo
{

    public static function getAll(int $id, $limit = 80, $offset = null)
    {
        $url_param = '/web/dataset/search_read';
        $data = [
            "jsonrpc" => "2.0",
            "method" => "call",
            "params" => [
                "model" => "stock.move.line",
                "domain" => [
                    [
                        "product_id.product_tmpl_id",
                        "in",
                        [
                            $id
                        ]
                    ],
                    [
                        "state",
                        "=",
                        "done"
                    ]
                ],
                "fields" => [
                    "reference",
                    "date",
                    "lot_id",
                    "lot_id2",
                    "lot_name",
                    "x_studio_no_so",
                    "x_studio_tgl_so",
                    "product_id",
                    "x_studio_id_paket",
                    "x_studio_customer",
                    "product_category_related",
                    "location_id",
                    "location_dest_id",
                    "qty_done",
                    "state",
                    "product_uom_id"
                ],
                "limit" => intval($limit),
                "offset" => intval($offset),
                "sort" => "date DESC",
                "context" => [
                    "lang" => "en_US",
                    "tz" => "Asia/Jakarta",
                    "uid" => 192,
                    "active_model" => "product.template",
                    "active_id" => 20564,
                    "active_ids" => [
                        20564
                    ],
                    "search_default_done" => 1,
                    "search_default_groupby_product_id" => 1,
                    "search_disable_custom_filters" => true
                ],
            ],
        ];
        $response  = parent::asJson()
            ->withUrlParam($url_param)
            ->method('POST')
            ->withData($data)
            ->get();
        if (!isset($response['result'])) {
            throw new Exception('Odoo Error');
        }
        return $response['result'];
    }
}
