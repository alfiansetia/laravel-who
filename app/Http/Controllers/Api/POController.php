<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PoResource;
use App\Services\Import\POServices;
use Illuminate\Http\Request;

class POController extends Controller
{
    public function index(Request $request)
    {
        $res = POServices::getAll($request->search, $request->limit, $request->offset);
        return  $this->sendResponse(['data' => collect($res['records'])->map(function ($item) {
            $item['vendor'] = get_name($item['partner_id']);
            $item['user'] = get_name($item['user_id']);
            return $item;
        })]);
    }

    public function detail(Request $request, string $id)
    {
        $res = POServices::detail(intval($id));
        return $this->sendResponse($res);
    }

    public function order_line(Request $request)
    {
        $res = POServices::getOrderLines($request->lines);
        return $this->sendResponse(['data' => PoResource::collection($res['result'])]);
    }
}
