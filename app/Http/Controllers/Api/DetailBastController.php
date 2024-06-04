<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetailBast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DetailBastController extends Controller
{

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'bast'      => 'required|exists:basts,id',
            'product'   => 'required|exists:products,id',
            'qty'       => 'required',
            'lot'       => 'nullable',
        ]);
        if ($validate->fails()) {
            return response()->json(['message' => $validate->getMessageBag()], 422);
        }
        DetailBast::create([
            'bast_id'       => $request->bast,
            'product_id'    => $request->product,
            'qty'           => $request->qty,
            'lot'           => $request->lot,
        ]);
        return response()->json(['message' => 'Success!']);
    }


    public function update(Request $request, DetailBast $detail_bast)
    {
        $this->validate($request, [
            'qty'       => 'required',
            'lot'       => 'nullable',
        ]);
        $detail_bast->update([
            'qty' => $request->qty,
            'lot' => $request->lot,
        ]);
        return response()->json(['message' => 'Success!']);
    }
    public function destroy(DetailBast $detail_bast)
    {
        $detail_bast->delete();
        return response()->json([
            'message'   => 'Success!',
            'data'      => $detail_bast->load('bast')
        ]);
    }
}
