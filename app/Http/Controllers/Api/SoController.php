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
        $search = $request->search ?? '';
        $response = SoServices::getAll($search);
        return $this->sendResponse(Arr::get($response, 'records'));
    }

    public function detail(int $id)
    {
        $id = intval($id);
        $response = SoServices::detail($id);
        return $this->sendResponse($response);
    }
}
