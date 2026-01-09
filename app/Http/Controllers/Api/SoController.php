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
        $draw   = $request->draw;
        $start  = $request->start ?? 0;
        $length = $request->length ?? 10;
        $search = $request->input('search');
        if (is_array($search)) {
            $search = $search['value'] ?? '';
        }
        $search = (string) ($search ?? '');
        $filter = $request->input('filter');
        $filters = [];
        if ($filter == 'print_ok') {
            $filters = [
                [
                    "note_to_wh",
                    "not ilike",
                    "PRINT OK"
                ]
            ];
        }
        $response = SoServices::getAll($search, $length, $start, $filters);
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
        $response = SoServices::detail($id);
        return $this->sendResponse($response);
    }

    public function mark_as_print(Request $request, int $id)
    {
        $id = intval($id);
        $new_note = "PRINT OK\n" . ($request->note ?? '');
        $response = SoServices::writeNote($id, $new_note);
        return $this->sendResponse($response, 'Success mark as print');
    }

    public function mark_as_unprint(Request $request, int $id)
    {
        $id = intval($id);
        $note = $request->note ?? '';
        // Hapus "PRINT OK" secara case-insensitive dan bersihkan spasi/newline di sekitarnya
        $new_note = preg_replace('/PRINT OK\s*/i', '', $note);
        $new_note = trim($new_note);

        $response = SoServices::writeNote($id, $new_note);
        return $this->sendResponse($response, 'Success mark as unprint');
    }
}
