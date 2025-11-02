<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Import\RIServices;
use Illuminate\Http\Request;

class RIController extends Controller
{
    public function index(Request $request)
    {
        $res = RIServices::getAll($request->search, $request->limit, $request->offset);
        return $this->sendResponse([
            'length' => $res['length'] ?? 0,
            'data' => collect($res['records'])->map(function ($item) {
                $item['vendor'] = get_name($item['partner_id']);
                // $item['user'] = get_name($item['user_id']);
                return $item;
            })
        ]);
    }

    public function detail(Request $request, string $id)
    {
        $res = RIServices::detail(intval($id));
        return $this->sendResponse($res);
    }

    public function order_line(Request $request)
    {
        $res = RIServices::getOrderLines($request->lines);
        return $this->sendResponse(['data' => collect($res['result'])->map(function ($item) {
            $p = pecah_code($item['product_id']);
            $item['p_id'] = $p[0];
            $item['p_code'] = $p[1];
            $item['p_name'] = $p[2];
            $item['akl'] = get_name($item['akl_id']);
            return $item;
        })]);
    }
}
