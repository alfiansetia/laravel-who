<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alamat;
use App\Models\DetailAlamat;
use App\Models\Product;
use App\Models\Setting;
use App\Services\DoService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DOController extends Controller
{
    private $headers = [
        'accept'            => 'application/json, text/javascript, */*; q=0.01',
        'accept-language'   => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
        'content-type'      => 'application/json',
        'x-requested-with'  => 'XMLHttpRequest',
        'Cookie'            => '',
    ];

    public function __construct()
    {
        $setting = Setting::first();
        $this->headers['Cookie'] = 'session_id=' . $setting->odoo_session ?? '';
    }

    public function index(Request $request)
    {
        $search = $request->param ?? 'CENT/OUT/';
        try {
            $service = new DoService();
            $response = $service->search($search);
            return response()->json(['data' => $response['result'] ?? []]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage(), 'data' => []], 500);
        }
    }

    public function detail(int $id)
    {
        $id = intval($id);
        try {
            $service = new DoService();
            $response = $service->detail($id);
            return response()->json(['data' => $response['result'] ?? []]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage(), 'data' => []], 500);
        }
    }
}
