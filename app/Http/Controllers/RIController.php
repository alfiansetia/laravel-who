<?php

namespace App\Http\Controllers;

use App\Services\Import\RIServices;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class RIController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $perPage = max((int) $request->input('per_page', 10), 1);
            $page = max((int) $request->input('page', 1), 1);
            $offset = ($page - 1) * $perPage;
            $search = (string) ($request->input('search') ?? '');

            $response = RIServices::getAll($search, $perPage, $offset);
            $total = Arr::get($response, 'length', 0);
            $data = Arr::get($response, 'records', []);

            return response()->json([
                'data' => $data,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => $perPage > 0 ? (int) ceil($total / $perPage) : 0,
            ]);
        }

        return Inertia::render('Ri/Index', [
            'title' => 'RI',
            'filters' => $request->only(['search', 'page']),
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

        if ($request->wantsJson()) {
            return $this->sendResponse(['data' => collect($res['result'])->map(function ($item) {
                $p = pecah_code($item['product_id']);
                $item['p_id'] = $p[0];
                $item['p_code'] = $p[1];
                $item['p_name'] = $p[2];
                $item['akl'] = get_name($item['akl_id']);

                return $item;
            })]);
        }

        return redirect()->back()->with('success', 'Success!');
    }
}
