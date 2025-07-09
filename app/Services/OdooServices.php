<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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


    public static function getBaseUrl()
    {
        return config('services.odoo.base_url');
    }

    public static function getSessionFile()
    {
        return storage_path('app/session.json');
    }

    public static function getCurrentSession()
    {
        $session_file = static::getSessionFile();
        if (file_exists($session_file)) {
            $json = File::get($session_file);
            $data = json_decode($json, true);
            if (empty($data['session_id']) || empty($data['uid'])) {
                return static::setDefaultSession();
            }
            return $data;
        } else {
            return static::setDefaultSession();
        }
    }

    public static function saveSession($data)
    {
        $json = json_encode($data, JSON_PRETTY_PRINT);
        $session_file = static::getSessionFile();
        File::put($session_file, $json);
    }

    public static function setDefaultSession()
    {
        $data = [
            'session_id'            => null,
            'uid'                   => 0,
            'db'                    => null,
            'name'                  => null,
            'username'              => null,
            'partner_display_name'  => null,
            'partner_id'            => 0,
        ];
        $session_file = static::getSessionFile();
        File::put($session_file, json_encode($data, JSON_PRETTY_PRINT));
        return $data;
    }

    public static function isValidSession()
    {
        $old_session = static::getCurrentSession();
        $base_url = static::getBaseUrl();
        $session = $old_session['session_id'];
        $uid = $old_session['uid'];
        if (empty($session) || $uid == 0) {
            return false;
        }
        $res = Http::withHeaders([
            'accept'            => 'application/json, text/javascript, */*; q=0.01',
            'accept-language'   => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
            'content-type'      => 'application/json',
            'x-requested-with'  => 'XMLHttpRequest',
            'Accept-Encoding'   => 'gzip, deflate',
            'Cookie'            => 'session_id=' .  $session
        ])->post(
            $base_url . '/web/dataset/call_kw/res.users/read',
            [
                "jsonrpc" => "2.0",
                "method" => "call",
                "params" => [
                    "args" => [
                        [
                            $uid
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
            ]
        );
        Log::info($res->json());
        if (strtolower($old_session['username'] ?? '') == strtolower(config('services.odoo.email'))) {
            return true;
        }
        return false;
    }
}
