<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kontak;
use App\Services\Odoo;
use Illuminate\Http\Request;

class KontakController extends Controller
{
    public function __construct()
    {
        $this->middleware('env_auth')->only(['destroy', 'destroy_batch']);
    }

    public function index()
    {
        $data = Kontak::orderBy('name', 'ASC')->get();
        return $this->sendResponse($data, 'Success!');
    }

    public function store()
    {
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
                "limit" => 10000,
                "sort" => "",
                "context" => [
                    "lang" => "en_US",
                    "tz" => "GMT",
                    "uid" => 192
                ],
            ],
            ["id" => 288682884],
        ];
        try {
            $url_param = '/web/dataset/search_read';
            $json = Odoo::asJson()
                ->withUrlParam($url_param)
                ->withData($data)
                ->method('POST')
                ->get();
            $records = $json['result']['records'] ?? [];
            $chunks = array_chunk($records, 100);
            foreach ($chunks as $chunk) {
                foreach ($chunk as $item) {
                    Kontak::query()->updateOrCreate([
                        'name' => $item['display_name'],
                    ], [
                        'name'      => $item['display_name'],
                        'street'    => $item['street'] ?? '',
                        'phone'     => $item['phone'],
                    ]);
                }
            }
            return response()->json(['message' => 'Success!', 'data' => $json['result']],);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage(), 'data' => []], 500);
        }
    }
}
