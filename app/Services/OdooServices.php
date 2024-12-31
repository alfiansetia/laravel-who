<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class OdooServices
{

    public static string $session = '';
    public static string $base_url_odoo = '';
    public static string $url_param = '';
    public static string $method = 'GET';
    public static array $headers = [];
    public static array $data_param = [];
    public static bool $state_file = false;

    public function __construct() {}

    public static function data(array $data)
    {
        static::$data_param = $data;
        return new static;
    }

    public static function get_base_url_odoo()
    {
        $base_url = config('services.odoo.base_url');
        static::$base_url_odoo = $base_url;
        return $base_url;
    }

    public static function get_session()
    {
        $setting = Setting::first();
        $session = $setting->odoo_session ?? '';
        static::$session = $session;
        return $session;
    }

    public static function url_param(string $url_param)
    {
        static::$url_param = $url_param;
        return new static;
    }

    public static function method(string $method)
    {
        static::$method = $method;
        return new static;
    }

    public static function as_json()
    {
        static::$headers = [
            'accept'            => 'application/json, text/javascript, */*; q=0.01',
            'accept-language'   => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
            'content-type'      => 'application/json',
            'x-requested-with'  => 'XMLHttpRequest',
            'Accept-Encoding'   => 'gzip, deflate',
        ];
        static::set_cookie();
        return new static;
    }

    public static function as_file()
    {
        static::$state_file = true;
        static::$headers = [];
        static::set_cookie();
        return new static;
    }

    public static function get_headers()
    {
        return static::$headers;
    }

    public static function set_cookie()
    {
        static::$headers['Cookie'] = 'session_id=' .  static::get_session();
        return new static;
    }

    public static function get()
    {
        $base_url = static::get_base_url_odoo();
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
}
