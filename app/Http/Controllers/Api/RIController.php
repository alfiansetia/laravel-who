<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Import\RIServices;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class RIController extends Controller
{
    public function index(Request $request)
    {
        $draw   = $request->draw;
        $start  = $request->start ?? 0;
        $length = $request->length ?? 10;
        $search = $request->input('search');
        if (is_array($search)) {
            $search = $search['value'] ?? '';
        }
        $search = (string) ($search ?? '');
        $response = RIServices::getAll($search, $length, $start);
        $totalRecords = Arr::get($response, 'length', 0);
        $data = Arr::get($response, 'records', []);

        return response()->json([
            'draw'            => intval($draw),
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data'            => $data
        ]);
    }

    public function detail(Request $request, string $id)
    {
        $res = RIServices::detail($id);
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
