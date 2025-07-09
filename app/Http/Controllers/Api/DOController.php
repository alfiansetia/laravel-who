<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DoMonitorService;
use App\Services\DoService;
use App\Services\DoServices;
use Illuminate\Http\Request;

class DOController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->param ?? 'CENT/OUT/';
        try {
            $response = DoServices::getAll($search);
            return response()->json(['data' => $response]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage(), 'data' => []], 500);
        }
    }

    public function detail(int $id)
    {
        $id = intval($id);
        try {
            $response = DoServices::detail($id);
            return response()->json(['data' => $response]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage(), 'data' => []], 500);
        }
    }

    public function monitor()
    {
        try {
            $response = DoMonitorService::getAll();
            return response()->json(['data' => $response ?? []]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage(), 'data' => []], 500);
        }
    }
}
