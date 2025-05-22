<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            'desc'      => 'nullable',
        ]);
        if ($validate->fails()) {
            return response()->json(['message' => $validate->getMessageBag()], 422);
        }
        // $this->validate($request, );
        $last_order =  (DetailAlamat::where('alamat_id', $request->alamat)->max('order') ?? 0) + 1;
        DetailAlamat::create([
            'alamat_id'     => $request->alamat,
            'product_id'    => $request->product,
            'qty'           => $request->qty,
            'lot'           => $request->lot,
            'desc'          => $request->desc,
            'order'         => $last_order,
        ]);
        return response()->json(['message' => 'Success!']);
    }


    public function update(Request $request, DetailAlamat $detail_alamat)
    {
        $this->validate($request, [
            'qty'       => 'required',
            'lot'       => 'nullable',
            'desc'      => 'nullable',
        ]);
        $detail_alamat->update([
            'qty'   => $request->qty,
            'lot'   => $request->lot,
            'desc'  => $request->desc,
        ]);
        return response()->json(['message' => 'Success!']);
    }

    public function destroy(DetailAlamat $detail_alamat)
    {
        $detail_alamat->delete();
        return response()->json(['message' => 'Success!']);
    }

    public function order(Request $request, DetailAlamat $detail_alamat)
    {
        $this->validate($request, [
            'type' => 'required|in:up,down'
        ]);
        $totaldata = DetailAlamat::where('alamat_id', $detail_alamat->alamat_id)->count();
        if ($totaldata < 2) {
            return response()->json([
                'message'   => 'No change!',
                'data'      => ''
            ]);
        }

        $currentOrder = $detail_alamat->order;

        // Tentukan arah naik atau turun
        if ($request->type == 'up') {
            // Ambil data dengan order lebih kecil (di atas)
            $swapTarget = DetailAlamat::where('alamat_id', $detail_alamat->alamat_id)
                ->where('order', '<', $currentOrder)
                ->orderByDesc('order')
                ->first();
        } else {
            // Ambil data dengan order lebih besar (di bawah)
            $swapTarget = DetailAlamat::where('alamat_id', $detail_alamat->alamat_id)
                ->where('order', '>', $currentOrder)
                ->orderBy('order', 'asc')
                ->first();
        }

        // Jika ada data yang bisa ditukar
        if ($swapTarget) {
            // Tukar nilai order-nya
            $temp = $detail_alamat->order;
            $detail_alamat->update(['order' => $swapTarget->order]);
            $swapTarget->update(['order' => $temp]);

            return response()->json([
                'message' => 'Berhasil ditukar urutannya.',
                'data' => $detail_alamat->fresh()
            ]);
        }

        return response()->json([
            'message'   => 'No change!',
            'data'      => ''
        ]);
    }
}
