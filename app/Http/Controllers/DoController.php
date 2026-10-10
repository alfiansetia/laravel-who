<?php

namespace App\Http\Controllers;

use App\Services\DoMonitorService;
use App\Services\DoServices;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class DoController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $perPage = max((int) $request->input('per_page', 10), 1);
            $page = max((int) $request->input('page', 1), 1);
            $offset = ($page - 1) * $perPage;
            $search = (string) ($request->input('search') ?? '');
            $note_search = (string) ($request->input('note_search') ?? '');
            $response = DoServices::getAll($search, $perPage, $offset, $note_search);
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

        return Inertia::render('Do/Index', [
            'title' => 'DO',
            'filters' => $request->only(['search', 'page', 'note_search']),
        ]);
    }

    public function detail(int $id)
    {
        $id = intval($id);
        $response = DoServices::detail($id);

        return $this->sendResponse($response);
    }

    public function monitor()
    {
        $response = DoMonitorService::getAll();

        return $this->sendResponse($response ?? []);
    }

    public function print(Request $request, $id)
    {
        $with_lot = $request->with_lot ?? false;
        $data = DoServices::detail($id);
        if (! isset($data['id'])) {
            return redirect()->route('do.index')->with('error', 'Data Tidak Ditemukan');
        }

        return view('do.print', compact('data', 'with_lot'));
    }
}
