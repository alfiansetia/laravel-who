<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DoMonitorService;
use App\Services\DoService;
use Illuminate\Http\Request;

class DOController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->param ?? 'CENT/OUT/';
        try {
            $service = new DoService();
            $response = $service->search($search);
            return response()->json(['data' => $response['result'] ?? []]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage(), 'data' => []], 500);
        }
    }

    public function detail(int $id)
    {
        $id = intval($id);
        try {
            $service = new DoService();
            $response = $service->detail($id);
            return response()->json(['data' => $response['result'] ?? []]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage(), 'data' => []], 500);
        }
    }

    public function monitor()
    {
        try {
            $service = new DoMonitorService();
            $response = $service->search();
            return response()->json(['data' => $response ?? []]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage(), 'data' => []], 500);
        }
    }
}
