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
        $response = DoServices::getAll($search);
        return $this->sendResponse($response);
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
