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
            'satuan'    => 'required|in:Pcs,Pck,Unit,EA,Box,Btl,Vial',
        ]);
        if ($validate->fails()) {
            return $this->sendError($validate->errors()->first(), 422);
        }
        $data = DetailBast::create([
            'bast_id'       => $request->bast,
            'product_id'    => $request->product,
            'qty'           => $request->qty,
            'lot'           => $request->lot,
            'satuan'        => $request->satuan,
        ]);
        return $this->sendResponse($data, 'Created!');
    }


    public function update(Request $request, DetailBast $detail_bast)
    {
        $this->validate($request, [
            'qty'       => 'required',
            'lot'       => 'nullable',
            'satuan'    => 'required|in:Pcs,Pck,Unit,EA,Box,Btl,Vial',
        ]);
        $detail_bast->update([
            'qty'       => $request->qty,
            'lot'       => $request->lot,
            'satuan'    => $request->satuan,
        ]);
        return $this->sendResponse($detail_bast, 'Updated!');
    }
    public function destroy(DetailBast $detail_bast)
    {
        $detail_bast->delete();
        return $this->sendResponse($detail_bast, 'Deleted!');
    }
}
