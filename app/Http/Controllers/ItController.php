<?php

namespace App\Http\Controllers;

use App\Services\ItServices;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class ItController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $perPage = max((int) $request->input('per_page', 10), 1);
            $page = max((int) $request->input('page', 1), 1);
            $offset = ($page - 1) * $perPage;
            $search = (string) ($request->input('search') ?? '');
            $gudang = $request->input('gudang', 5);

            $response = ItServices::withGudang(intval($gudang))
                ->getAll($search, $perPage, $offset);
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

        return Inertia::render('It/Index', [
            'title' => 'IT',
            'filters' => $request->only(['search', 'page', 'gudang']),
        ]);
    }

    public function detail(int $id)
    {
        $id = intval($id);
        $response = ItServices::detail($id);

        return $this->sendResponse($response);
    }

    public function print(Request $request, $id)
    {
        $data = ItServices::detail($id);
        if (! isset($data['id'])) {
            return redirect()->route('it.index')->with('error', 'Data Tidak Ditemukan');
        }

        return view('it.print', compact('data'));
    }
}
