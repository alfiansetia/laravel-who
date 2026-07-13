<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TikiServices;
use Illuminate\Http\Request;

class TikiController extends Controller
{
    /**
     * GET /api/tiki/track?resi=660108012346,660108011392
     *
     * Accepts one or more comma-separated connote numbers and
     * proxies the request to the TIKI tracking API.
     */
    public function track(Request $request)
    {
        $request->validate([
            'resi' => 'required|string|max:1000',
        ]);

        $resi = $request->input('resi');

        $result = TikiServices::track($resi);

        if ($result === null) {
            return $this->sendResponse(null, 'Gagal mengambil data tracking dari TIKI', 502);
        }

        return $this->sendResponse($result);
    }
}
