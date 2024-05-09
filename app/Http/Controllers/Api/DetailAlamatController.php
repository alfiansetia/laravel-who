<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alamat;
use App\Models\DetailAlamat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DetailAlamatController extends Controller
{
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'alamat'    => 'required|exists:alamats,id',
            'product'   => 'required|exists:products,id',
            'qty'       => 'required',
            'lot'       => 'nullable',
        ]);
        if ($validate->fails()) {
            return response()->json(['message' => $validate->getMessageBag()], 422);
        }
        // $this->validate($request, );
        DetailAlamat::create([
            'alamat_id'     => $request->alamat,
            'product_id'    => $request->product,
            'qty'           => $request->qty,
            'lot'           => $request->lot,
        ]);
        return response()->json(['message' => 'Success!']);
    }


    public function update(Request $request, DetailAlamat $detail_alamat)
    {
        $this->validate($request, [
            'qty'       => 'required',
            'lot'       => 'nullable',
        ]);
        $detail_alamat->update([
            'qty' => $request->qty,
            'lot' => $request->lot,
        ]);
        return response()->json(['message' => 'Success!']);
    }

    public function destroy(DetailAlamat $detail_alamat)
    {
        $detail_alamat->delete();
        return response()->json(['message' => 'Success!']);
    }
}
