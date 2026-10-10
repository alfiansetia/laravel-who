<?php

namespace App\Http\Controllers;

use App\Services\ProductOdooServices;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class ProductOdooController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $perPage = max((int) $request->input('per_page', 10), 1);
            $page = max((int) $request->input('page', 1), 1);
            $offset = ($page - 1) * $perPage;
            $search = (string) ($request->input('search') ?? '');

            $response = ProductOdooServices::getAll($search, $perPage, $offset);
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

        return Inertia::render('ProductOdoo/Index', [
            'title' => 'Product Odoo',
            'filters' => $request->only(['search', 'page']),
        ]);
    }

    public function detail(int $id)
    {
        $response = ProductOdooServices::detail((int) $id);

        return $this->sendResponse($response);
    }

    public function on_hand(Request $request, int $id, int $variant)
    {
        $res = ProductOdooServices::onHand($id, $variant);
        $total = Arr::get($res, 'result.length');

        if ($request->wantsJson()) {
            return response()->json([
                'draw' => $request->draw,
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => Arr::get($res, 'result.records'),
            ]);
        }

        return redirect()->back()->with('success', 'Success!');
    }

    public function move(Request $request, int $id, int $variant)
    {
        $res = ProductOdooServices::move($id, $variant);
        $total = Arr::get($res, 'result.length');

        if ($request->wantsJson()) {
            return response()->json([
                'draw' => $request->draw,
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => Arr::get($res, 'result.records'),
            ]);
        }

        return redirect()->back()->with('success', 'Success!');
    }
}
