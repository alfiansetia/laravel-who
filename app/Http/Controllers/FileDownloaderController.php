<?php

namespace App\Http\Controllers;

use App\Services\OdooService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class FileDownloaderController extends Controller
{

    public function download(string $id_so = null)
    {
        if (!$id_so) {
            abort(404);
        }
        $data = [
            "jsonrpc" => "2.0",
            "method" => "call",
            "params" => [
                "args" => [
                    [
                        intval($id_so)
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
            $url_param =  '/web/dataset/call_button';
            $service = new OdooService();
            $json = $service->method('POST')->url_param($url_param)->data($data)->get();
            $data_url = $json['result']['url'];
            $fileResponse = $service->as_file()->url_param($data_url)->method('GET')->get();
            $full = $fileResponse->header('Content-Disposition');
            if (!$full) {
                throw new Exception('File Not Found');
            }
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
