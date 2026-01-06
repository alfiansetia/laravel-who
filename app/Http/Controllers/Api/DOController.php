<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DoMonitorService;
use App\Services\DoServices;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class DOController extends Controller
{

    public function index(Request $request)
    {
        $draw   = $request->draw;
        $start  = $request->start ?? 0;
        $length = $request->length ?? 10;
        $search = $request->input('search');
        if (is_array($search)) {
            $search = $search['value'] ?? '';
        }
        $search = (string) ($search ?? '');
        if ($request->filled('param')) {
            $search = $request->param;
        }
        $response = DoServices::getAll($search, $length, $start);
        $totalRecords = Arr::get($response, 'length', 0);
        $data = Arr::get($response, 'records', []);
        return response()->json([
            'draw'            => intval($draw),
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data'            => $data
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
}
