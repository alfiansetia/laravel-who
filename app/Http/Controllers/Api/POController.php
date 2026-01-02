<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PoResource;
use App\Services\Import\POServices;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class POController extends Controller
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
        $response = POServices::getAll($search, $length, $start);
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
        $res = POServices::detail($id);
        return $this->sendResponse($res);
    }

    public function order_line(Request $request)
    {
        $res = POServices::getOrderLines($request->lines);
        return $this->sendResponse(['data' => PoResource::collection($res['result'])]);
    }
}
