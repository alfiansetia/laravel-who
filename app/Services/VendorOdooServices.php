<?php

namespace App\Services;

use App\Exceptions\OdooException;
use Illuminate\Support\Arr;

class VendorOdooServices extends Odoo
{
    public function __construct() {}

    public static function getAll(string $query = '', int $limit = 80, int $offset = 0): array
    {
        $url_param = '/web/dataset/search_read';
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => [
                'model' => 'res.partner',
                'domain' => [
                    [
                        'supplier',
                        '=',
                        true,
                    ],
                    '|',
                    '|',
                    [
                        'name',
                        'ilike',
                        $query,
                    ],
                    [
                        'email',
                        'ilike',
                        $query,
                    ],
                    [
                        'phone',
                        'ilike',
                        $query,
                    ],
                ],
                'fields' => [
                    'id',
                    'name',
                    'display_name',
                    'email',
                    'phone',
                    'mobile',
                    'website',
                    'street',
                    'city',
                    'country_id',
                    'vat',
                    'supplier',
                    'is_company',
                ],
                'limit' => $limit,
                'offset' => $offset,
                'sort' => 'name ASC',
                'context' => [
                    'lang' => 'en_US',
                    'tz' => 'Asia/Jakarta',
                    'uid' => 192,
                ],
            ],
            'id' => 75135116,
        ];
        $response = parent::asJson()
            ->withUrlParam($url_param)
            ->method('POST')
            ->withData($data)
            ->get();
        if (! isset($response['result'])) {
            throw new OdooException('Gagal mengambil data vendor');
        }

        return $response['result'];
    }

    public static function detail(int $id): array
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => [
                'args' => [
                    [$id],
                    [
                        'id',
                        'name',
                        'display_name',
                        'email',
                        'phone',
                        'mobile',
                        'website',
                        'street',
                        'street2',
                        'city',
                        'zip',
                        'state_id',
                        'country_id',
                        'vat',
                        'supplier',
                        'customer',
                        'is_company',
                        'is_seller',
                        'company_type',
                        'parent_id',
                        'active',
                    ],
                ],
                'model' => 'res.partner',
                'method' => 'read',
                'kwargs' => [
                    'context' => [
                        'lang' => 'en_US',
                        'tz' => 'Asia/Jakarta',
                        'uid' => 192,
                    ],
                ],
            ],
            'id' => 75135117,
        ];
        $response = parent::asJson()
            ->method('POST')
            ->withUrlParam('/web/dataset/call_kw/res.partner/read')
            ->withData($data)
            ->get();

        $record = Arr::get($response, 'result.0');

        if (! $record) {
            throw new OdooException('Data vendor tidak ditemukan!', 404);
        }

        return $record;
    }
}
