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
        try {
            $res = POServices::getAll($request->search, $request->limit, $request->offset);
            return response()->json(['data' => collect($res['records'])->map(function ($item) {
                $item['vendor'] = get_name($item['partner_id']);
                $item['user'] = get_name($item['user_id']);
                return $item;
            })]);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function detail(Request $request, string $id)
    {
        try {
            $res = POServices::detail(intval($id));
            return response()->json($res);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function order_line(Request $request)
    {
        try {
            $res = POServices::get_order_line($request->lines);
            return response()->json(['data' => PoResource::collection($res['result'])]);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
}
