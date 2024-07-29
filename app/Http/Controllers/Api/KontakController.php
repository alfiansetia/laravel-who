<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OdooService;
use Illuminate\Http\Request;

class KontakController extends Controller
{
    public function index()
    {
        try {
            $data = [
                "jsonrpc" => "2.0",
                "method" => "call",
                "params" => [
                    "model" => "res.partner",
                    "domain" => [],
                    "fields" => [
                        "nomor_partner",
                        "kode",
                        "display_name",
                        "nama_faktur",
                        "parent_name",
                        "street",
                        "kota_id",
                        "function",
                        "phone",
                        "user_id",
                        "is_company",
                        "country_id",
                        "parent_id",
                        "active"
                    ],
                    "limit" => 7000,
                    "sort" => "",
                    "context" => [
                        "lang" => "en_US",
                        "tz" => "GMT",
                        "uid" => 192
                    ],
                ],
                ["id" => 288682884],
            ];
            $service = new OdooService();
            $response = $service->as_json()->url_param('/web/dataset/search_read')->data($data)->method('POST')->get();
            return response()->json(['data' => $response['result']['records']]);
        } catch (\Throwable $th) {
            return response()->json([
                'data' => [],
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
