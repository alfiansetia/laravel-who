<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FileDownloaderController extends Controller
{
    use ResponseTrait;

    public function __construct()
    {
        $setting = Setting::first();
        $this->headers['Cookie'] = 'session_id=' . $setting->odoo_session ?? '';
    }

    public function download(string $so = null)
    {
        if (!$so) {
            abort(404);
        }
        $odoo_domain = env('ODOO_DOMAIN');
        $data = [
            "jsonrpc" => "2.0",
            "method" => "call",
            "params" => [
                "args" => [
                    [
                        intval($so)
                    ],
                    [
                        "lang" => "en_US",
                        "tz" => "GMT",
                        "uid" => 192,
                        "params" => [
                            "id" => 12508,
                            "action" => 338,
                            "model" => "sale.order",
                            "view_type" => "form",
                            "menu_id" => 211
                        ],
                        "search_default_my_quotation" => 1
                    ]
                ],
                "method" => "print_so",
                "model" => "sale.order"
            ],
            ["id" => 425211742]
        ];
        try {
            $url = $odoo_domain . '/web/dataset/call_button';
            $response = Http::withHeaders($this->headers)->post($url, $data);
            $json = $response->json();
            $data_url = $json['result']['url'];
            return redirect($odoo_domain . $data_url);
        } catch (\Throwable $th) {
            abort(404, $th->getMessage());
        }
    }
}
