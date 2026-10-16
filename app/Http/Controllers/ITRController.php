<?php

namespace App\Http\Controllers;

use App\Services\Import\ItrServices;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class ITRController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $perPage = max((int) $request->input('per_page', 10), 1);
            $page = max((int) $request->input('page', 1), 1);
            $offset = ($page - 1) * $perPage;
            $search = (string) ($request->input('search') ?? '');

            $response = ItrServices::getAll($search, $perPage, $offset);
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

        return Inertia::render('Itr/Index', [
            'title' => 'ITR',
            'filters' => $request->only(['search', 'page']),
        ]);
    }

    public function detail(Request $request, string $id)
    {
        $res = ItrServices::detail((int) $id);

        return $this->sendResponse($res);
    }
}
