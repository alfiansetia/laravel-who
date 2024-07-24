<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class FileDownloaderController extends Controller
{
    use ResponseTrait;
    private $headers_file = [];

    public function __construct()
    {
        $setting = Setting::first();
        $this->headers['Cookie'] = 'session_id=' . $setting->odoo_session ?? '';
        $this->headers_file['Cookie'] = 'session_id=' . $setting->odoo_session ?? '';
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
            $data_url = $odoo_domain . $json['result']['url'];
            $fileResponse = Http::withHeaders($this->headers_file)->get($data_url);
            $full = $fileResponse->header('Content-Disposition');
            $rep = str_replace("attachment; filename*=UTF-8''", '', $full);
            $jadi = urldecode($rep);
            $filePath = public_path('files/' . $jadi);
            if (!file_exists(public_path('files/'))) {
                File::makeDirectory(public_path('files/'), 755, true);
            }
            file_put_contents($filePath, $fileResponse->body());
            return response()->download($filePath)->deleteFileAfterSend();
        } catch (\Throwable $th) {
            abort(404, $th->getMessage());
        }
    }
}
