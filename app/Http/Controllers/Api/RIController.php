<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PoResource;
use App\Services\Import\RIServices;
use Illuminate\Http\Request;

class RIController extends Controller
{
    public function index(Request $request)
    {
        try {
            $res = RIServices::getAll($request->search, $request->limit, $request->offset);
            // return response()->json($res);
            return response()->json([
                'length' => $res['length'] ?? 0,
                'data' => collect($res['records'])->map(function ($item) {
                    $item['vendor'] = get_name($item['partner_id']);
                    // $item['user'] = get_name($item['user_id']);
                    return $item;
                })
            ]);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function detail(Request $request, string $id)
    {
        try {
            $res = RIServices::detail(intval($id));
            return response()->json($res);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function order_line(Request $request)
    {
        try {
            $res = RIServices::move_line_without($request->lines);
            return response()->json(['data' => collect($res['result'])->map(function ($item) {
                $p = pecah_code($item['product_id']);
                $item['p_id'] = $p[0];
                $item['p_code'] = $p[1];
                $item['p_name'] = $p[2];
                $item['akl'] = get_name($item['akl_id']);
                return $item;
            })]);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
}
