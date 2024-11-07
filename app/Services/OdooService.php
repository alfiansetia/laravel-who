<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class OdooService
{
    protected $session;
    protected $base_url_odoo;

    protected $url_param;
    protected $data_param = [];
    protected $method = 'GET';

    protected $state_file = false;

    private $headers = [];

    public function __construct()
    {
        $setting = Setting::first();
        $session = $setting->odoo_session ?? '';
        $this->session = $session;
        $this->base_url_odoo = config('services.odoo.base_url');
        $this->as_json();
    }

    public function data($data)
    {
        $this->data_param = $data;
        return $this;
    }

    public function url_param(string $url_param)
    {
        $this->url_param = $url_param;
        return $this;
    }

    public function method(string $method)
    {
        $this->method = $method;
        return $this;
    }

    public function as_json()
    {
        $this->headers = [
            'accept'            => 'application/json, text/javascript, */*; q=0.01',
            'accept-language'   => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
            'content-type'      => 'application/json',
            'x-requested-with'  => 'XMLHttpRequest',
            'Accept-Encoding'   => 'gzip, deflate',
            'Cookie'            => 'session_id=' . $this->session,
        ];
        return $this;
    }

    public function as_file()
    {
        $this->state_file = true;
        $this->headers = [];
        $this->headers['Cookie'] = 'session_id=' . $this->session;
        return $this;
    }

    public function get_headers()
    {
        return $this->headers;
    }

    public function get()
    {
        $http = Http::withHeaders($this->headers);
        if ($this->method === 'POST') {
            $response = $http->post($this->base_url_odoo . $this->url_param, $this->data_param);
        } else {
            $response = $http->get($this->base_url_odoo . $this->url_param);
        }
        if (!$response->successful()) {
            $response->throw();
        }
        if ($this->state_file) {
            return $response;
        }
        return $response->json();
    }
}
