<?php

namespace App\Http\Controllers;

use App\Services\GudangServices;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class GudangController extends Controller
{
    public function index(Request $request, GudangServices $gudang)
    {
        if ($request->wantsJson()) {
            $perPage = max((int) $request->input('per_page', 24), 1);
            $page = max((int) $request->input('page', 1), 1);
            $offset = ($page - 1) * $perPage;
            $search = (string) ($request->input('search') ?? '');

            $response = $gudang->getAll($search, $perPage, $offset);
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

        return Inertia::render('Gudang/Index', [
            'title' => 'Gudang',
            'filters' => $request->only(['search', 'page']),
        ]);
    }
}
