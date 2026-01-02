<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SoServices;
use Illuminate\Support\Arr;

class SoController extends Controller
{
    public function index(Request $request)
    {
        // Ambil parameter dari DataTables
        $draw   = $request->draw;
        $start  = $request->start ?? 0;
        $length = $request->length ?? 10;
        $search = $request->input('search');
        if (is_array($search)) {
            $search = $search['value'] ?? '';
        }
        $search = (string) ($search ?? '');

        // Panggil Service dengan limit dan offset (start)
        $response = SoServices::getAll($search, $length, $start);

        // Odoo (dataset/search_read) mengembalikan 'length' dan 'records'
        $totalRecords = Arr::get($response, 'length', 0);
        $data = Arr::get($response, 'records', []);

        return response()->json([
            'draw'            => intval($draw),
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $totalRecords, // Odoo 'length' adalah jumlah yang sudah terfilter keyword
            'data'            => $data
        ]);
    }

    public function detail(int $id)
    {
        $id = intval($id);
        $response = SoServices::detail($id);
        return $this->sendResponse($response);
    }
}
