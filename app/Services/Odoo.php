<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class Odoo
{
    public static array $data_param = [];
    public static array $headers = [];
    public static bool $state_file = false;
    public static string $url_param = '';
    public static string $method = 'GET';

    public static function getBaseUrl()
    {
        return config('services.odoo.base_url');
    }

    public static function getEmail()
    {
        return config('services.odoo.email');
    }

    public static function getPassword()
    {
        return config('services.odoo.password');
    }

    public static function getDB()
    {
        return config('services.odoo.db');
    }

    public static function getHeaders()
    {
        return static::$headers;
    }

    public static function getSession()
    {
        $data  = OdooSession::getCurrentSession();
        return $data['session_id'] ?? '';
    }

    public static function getUID()
    {
        $data  = OdooSession::getCurrentSession();
        return $data['uid'] ?? 0;
    }

    public static function setCookie()
    {
        static::$headers['Cookie'] = 'session_id=' .  static::getSession();
        return new static;
    }

    public static function withData(array $data)
    {
        static::$data_param = $data;
        return new static;
    }

    public static function withUrlParam(string $url_param)
    {
        static::$url_param = $url_param;
        return new static;
    }

    public static function method(string $method)
    {
        static::$method = $method;
        return new static;
    }

    public static function asJson()
    {
        static::$headers = [
            'accept'            => 'application/json, text/javascript, */*; q=0.01',
            'accept-language'   => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
            'content-type'      => 'application/json',
            'x-requested-with'  => 'XMLHttpRequest',
            'Accept-Encoding'   => 'gzip, deflate',
        ];
        static::setCookie();
        return new static;
    }

    public static function asFile()
    {
        static::$state_file = true;
        static::$headers = [];
        static::setCookie();
        return new static;
    }

    public static function get()
    {
        $base_url = static::getBaseUrl();
        $url = $base_url . static::$url_param;
        $http = Http::withHeaders(static::$headers);
        if (static::$method === 'POST') {
            $response = $http->post($url,  static::$data_param);
        } else {
            $response = $http->get($url);
        }
        if (!$response->successful()) {
            $response->throw();
        }
        if (static::$state_file) {
            return $response;
        }
        return $response->json();
    }

    public static function getProfile()
    {
        $param = [
            "jsonrpc" => "2.0",
            "method" => "call",
            "params" => [
                "args" => [
                    [
                        static::getUID()
                    ],
                    [
                        "image",
                        "__last_update",
                        "name",
                        "lang",
                        "tz",
                        "tz_offset",
                        "company_id",
                        "notification_type",
                        "odoobot_state",
                        "email",
                        "signature",
                        "display_name"
                    ]
                ],
                "model" => "res.users",
                "method" => "read",
                "kwargs" => [
                    "context" => [
                        "lang" => "en_US",
                        "tz" => "Asia/Jakarta",
                        "uid" => 192,
                        "bin_size" => true
                    ]
                ]
            ],
        ];
        $res = static::asJson()
            ->withUrlParam('/web/dataset/call_kw/res.users/read')
            ->withData($param)
            ->method('POST')
            ->get();
        return $res;
    }
}
