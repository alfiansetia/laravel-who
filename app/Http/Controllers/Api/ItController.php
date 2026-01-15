<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ItServices;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ItController extends Controller
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
        $gudang = $request->input('gudang');
        $search = (string) ($search ?? '');
        $response = ItServices::withGudang($gudang)
            ->getAll($search, $length, $start);
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
        $response = ItServices::detail($id);
        return $this->sendResponse($response);
    }
}
