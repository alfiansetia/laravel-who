<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Http;

trait ResponseTrait
{
    private $headers = [
        'accept'            => 'application/json, text/javascript, */*; q=0.01',
        'accept-language'   => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
        'content-type'      => 'application/json',
        'x-requested-with'  => 'XMLHttpRequest',
        'Cookie'            => '',
    ];

    private function handle_res($url, $data)
    {
        $response = Http::withHeaders($this->headers)->post($url, $data);
        $json = $response->json();
        if (!$response->successful()) {
            return throw new Exception('Odoo Error!');
        }
        return $json;
    }

    private function handle($url, $data, $headers)
    {
        $response = Http::withHeaders($headers)->post($url, $data);
        $json = $response->json();
        return response()->json(['status' => $response->status(), 'data' => $json['result'] ?? []], $response->status() ?? 500);
    }
}
