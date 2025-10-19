<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DoMonitorService;
use App\Services\DoServices;
use Illuminate\Http\Request;

class DOController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->param ?? 'CENT/OUT/';
        try {
            $response = DoServices::getAll($search);
            return $this->sendResponse($response);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage());
        }
    }

    public function detail(int $id)
    {
        $id = intval($id);
        try {
            $response = DoServices::detail($id);
            return $this->sendResponse($response);
            return response()->json(['data' => $response]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage());
        }
    }

    public function monitor()
    {
        try {
            $response = DoMonitorService::getAll();
            return $this->sendResponse($response ?? []);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage());
        }
    }
}
