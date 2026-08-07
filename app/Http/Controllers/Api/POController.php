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
        $perPage = max((int) $request->input('per_page', 10), 1);
        $page    = max((int) $request->input('page', 1), 1);
        $offset  = ($page - 1) * $perPage;
        $search  = (string) ($request->input('search') ?? '');

        $response = POServices::getAll($search, $perPage, $offset);
        $total    = Arr::get($response, 'length', 0);
        $data     = Arr::get($response, 'records', []);

        return response()->json([
            'data'        => $data,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => $perPage > 0 ? (int) ceil($total / $perPage) : 0,
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
