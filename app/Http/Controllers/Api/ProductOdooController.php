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
        $perPage = max((int) $request->input('per_page', 10), 1);
        $page    = max((int) $request->input('page', 1), 1);
        $offset  = ($page - 1) * $perPage;
        $search  = (string) ($request->input('search') ?? '');

        $response    = ProductOdooServices::getAll($search, $perPage, $offset);
        $total       = Arr::get($response, 'length', 0);
        $data        = Arr::get($response, 'records', []);

        return response()->json([
            'data'        => $data,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => $perPage > 0 ? (int) ceil($total / $perPage) : 0,
        ]);
    }

    public function on_hand(Request $request, int $id, int $variant)
    {
        $res = ProductOdooServices::onHand($id, $variant);
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
        $response = ProductOdooServices::detail((int) $id);
        return $this->sendResponse($response);
    }

    public function move(Request $request, int $id, int $variant)
    {
        $res = ProductOdooServices::move($id, $variant);
        $total = Arr::get($res, 'result.length');
        return response()->json([
            'draw'            => $request->draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'data'            => Arr::get($res, 'result.records')
        ]);
    }
}
