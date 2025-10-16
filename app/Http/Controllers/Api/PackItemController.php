<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PackItem;
use Illuminate\Http\Request;

class PackItemController extends Controller
{
    public function index(Request $request)
    {
        $data = PackItem::with(['pack'])->filter($request->only(['pack_id']))->get();
        return $this->sendResponse($data);
    }
}
