<?php

namespace App\Services\Import;

use App\Exceptions\OdooException;
use App\Services\Odoo;
use Exception;

class ItrServices extends Odoo
{
    public function __construct() {}

    /** @return array{fields: string[]} */
    private static function fields(): array
    {
        return [
            'name',
            'tanggal',
            'ref',
            'state_picking',
            'state_picking_terima',
            'warehouse',
            'state',
            'user_id',
        ];
    }

    /** @return string[] */
    private static function detailFields(): array
    {
        return [
            'state',
            'name',
            'tanggal',
            'ref',
            'warehouse_src',
            'locs_src',
            'warehouse',
            'locs',
            'ket',
            'picking',
            'state_picking',
            'picking_terima',
            'state_picking_terima',
            'user_id',
            'detail',
            'display_name',
        ];
    }

    public static function getAll($query = '', $limit = 80, $offset = 0)
    {
        $query = trim((string) $query);
        $domain = $query === ''
            ? []
            : [
                [
                    'name',
                    'ilike',
                    $query,
                ],
            ];
        $url_param = '/web/dataset/search_read';
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'id' => 115013929,
            'params' => [
                'model' => 'tbl_it_request',
                'domain' => $domain,
                'fields' => self::fields(),
                'limit' => intval($limit) ?: 80,
                'offset' => intval($offset),
                'sort' => 'name DESC',
                'context' => [
                    'lang' => 'en_US',
                    'tz' => 'Asia/Jakarta',
                    'uid' => 192,
                ],
            ],
        ];
        $response = parent::asJson()
            ->withUrlParam($url_param)
            ->method('POST')
            ->withData($data)
            ->get();
        if (! isset($response['result'])) {
            throw new Exception('Odoo Error');
        }

        return $response['result'];
    }

    public static function detail(int $id)
    {
        $url_param = '/web/dataset/call_kw/tbl_it_request/read';
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'id' => 235531685,
            'params' => [
                'args' => [
                    [
                        intval($id),
                    ],
                    self::detailFields(),
                ],
                'model' => 'tbl_it_request',
                'method' => 'read',
                'kwargs' => [
                    'context' => [
                        'lang' => 'en_US',
                        'tz' => 'Asia/Jakarta',
                        'uid' => 192,
                        'bin_size' => true,
                    ],
                ],
            ],
        ];
        $response = parent::asJson()
            ->withUrlParam($url_param)
            ->method('POST')
            ->withData($data)
            ->get();
        $res = $response['result'][0] ?? null;
        if (! $res) {
            throw new OdooException('Data Not Found!', 404);
        }
        try {
            $lineIds = array_map('intval', array_filter((array) ($res['detail'] ?? []), 'is_numeric'));
            $res['detail_lines'] = $lineIds !== []
                ? (static::getDetails($lineIds)['result'] ?? [])
                : [];
        } catch (\Throwable $th) {
            $res['detail_lines'] = [];
        }

        return $res;
    }

    public static function getDetails(array $ids)
    {
        $lineIds = array_map('intval', array_filter($ids, 'is_numeric'));
        $url_param = '/web/dataset/call_kw/tbl_it_request_detail/read';
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'id' => 27699387,
            'params' => [
                'args' => [
                    $lineIds,
                    [
                        'nama_produk',
                        'qty',
                        'satuan',
                    ],
                ],
                'model' => 'tbl_it_request_detail',
                'method' => 'read',
                'kwargs' => [
                    'context' => [
                        'lang' => 'en_US',
                        'tz' => 'Asia/Jakarta',
                        'uid' => 192,
                    ],
                ],
            ],
        ];

        return parent::asJson()
            ->withUrlParam($url_param)
            ->method('POST')
            ->withData($data)
            ->get();
    }
}
