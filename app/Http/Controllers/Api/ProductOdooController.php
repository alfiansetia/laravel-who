<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ProductOdooServices;
use Illuminate\Support\Arr;

class ProductOdooController extends Controller
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
        $response = ProductOdooServices::getAll($search, $length, $start);
        $totalRecords = Arr::get($response, 'length', 0);
        $data = Arr::get($response, 'records', []);

        return response()->json([
            'draw'            => intval($draw),
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data'            => $data
        ]);
    }

    public function on_hand(Request $request, $id)
    {
        $res = ProductOdooServices::onHand($id, $request->variant);
        $total = Arr::get($res, 'result.length');
        return response()->json([
            'draw'            => $request->draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'data'            => Arr::get($res, 'result.records')
        ]);
    }

    public function detail(int $id)
    {
        return $this->sendError('Soon', 400);
    }
}
