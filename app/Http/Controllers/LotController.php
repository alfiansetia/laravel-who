<?php

namespace App\Http\Controllers;

use App\Services\LotServices;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class LotController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $perPage = max((int) $request->input('per_page', 10), 1);
            $page = max((int) $request->input('page', 1), 1);
            $offset = ($page - 1) * $perPage;
            $search = (string) ($request->input('search') ?? '');
            $product = $request->filled('product') ? $request->input('product') : null;

            $response = LotServices::getAll($search, $perPage, $offset, $product);
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

        return Inertia::render('Lot/Index', [
            'title' => 'Data Lot',
            'filters' => $request->only(['search', 'page', 'product']),
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

        if ($request->wantsJson()) {
            return $this->sendResponse($data);
        }

        return redirect()->back()->with('success', 'Success!');
    }
}
