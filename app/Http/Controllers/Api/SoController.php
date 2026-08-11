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
        $perPage = max((int) $request->input('per_page', 10), 1);
        $page    = max((int) $request->input('page', 1), 1);
        $offset  = ($page - 1) * $perPage;
        $search  = (string) ($request->input('search') ?? '');
        $note_search = (string) ($request->input('note_search') ?? '');
        $filter  = $request->input('filter');
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
        if (!empty($note_search)) {
            $filters[] = [
                "note_to_wh",
                "ilike",
                "%$note_search%"
            ];
        }

        $response = SoServices::getAll($search, $perPage, $offset, $filters);
        $total    = Arr::get($response, 'length', 0);
        $data     = Arr::get($response, 'records', []);

        return response()->json([
            'data'        => $data,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => $perPage > 0 ? (int) ceil($total / $perPage) : 0,
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
