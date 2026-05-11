<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LotServices;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class LotController extends Controller
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
        $product = null;
        if ($request->filled('product')) {
            $product = $request->product;
        }
        $search = (string)($search ?? '');
        $response = LotServices::getAll($search, $length, $start, $product);
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
        $res = LotServices::detail($id);
        return $this->sendResponse($res);
    }

    public function trace(Request $request, string $id)
    {
        $res = LotServices::getTraceHtml($id);
        $data = Arr::get($res, 'result', null);
        return $this->sendResponse($data);
    }
}
