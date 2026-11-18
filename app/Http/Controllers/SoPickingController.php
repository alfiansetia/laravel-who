<?php

namespace App\Http\Controllers;

use App\Services\SoPickingServices;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class SoPickingController extends Controller
{
    public function index(Request $request, SoPickingServices $picking)
    {
        if ($request->wantsJson()) {
            $perPage = max((int) $request->input('per_page', 80), 1);
            $page = max((int) $request->input('page', 1), 1);
            $offset = ($page - 1) * $perPage;
            $search = (string) ($request->input('search') ?? '');
            $state = (string) ($request->input('state') ?? '');
            $productSearch = (string) ($request->input('product_search') ?? '');
            $date = (string) ($request->input('date') ?? '');
            $locationId = (int) ($request->input('location_id') ?? 0);

            $response = $picking->getAll($search, $perPage, $offset, $state, $productSearch, $date, $locationId);
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

        return Inertia::render('SoPicking/Index', [
            'title' => 'SO Picking',
            'filters' => $request->only(['search', 'page', 'state', 'product_search', 'date', 'location_id']),
        ]);
    }
}
