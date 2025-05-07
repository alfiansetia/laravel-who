<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kargan;
use App\Models\Product;
use Illuminate\Http\Request;

class KarganController extends Controller
{
    public function index()
    {
        $data = Kargan::with('product')->latest()->get();
        return response()->json([
            'data' => $data,
            'message' => '',
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'product_id'    => 'required|exists:products,id',
            'date'          => 'date_format:Y-m-d',
            'number'        => 'required|string|max:200',
            'sn'            => 'nullable|string|max:200',
            'pic'           => 'required|string|max:200',
        ]);
        $masa = '1 Tahun ( Unit Utama )';
        $kargan = Kargan::create([
            'product_id'    => $request->product_id,
            'date'          => $request->date,
            'number'        => $request->number,
            'sn'            => $request->sn,
            'pic'           => $request->pic,
            'masa'          => $masa,
        ]);
        return response()->json([
            'message'   => 'Success!',
            'data'      => $kargan
        ]);
    }

    public function update(Request $request, Kargan $kargan)
    {
        $this->validate($request, [
            'product_id'    => 'required|exists:products,id',
            'date'          => 'date_format:Y-m-d',
            'number'        => 'required|string|max:200',
            'sn'            => 'nullable|string|max:200',
            'pic'           => 'required|string|max:200',
        ]);
        $masa = '1 Tahun ( Unit Utama )';
        $kargan->update([
            'product_id'    => $request->product_id,
            'date'          => $request->date,
            'number'        => $request->number,
            'sn'            => $request->sn,
            'pic'           => $request->pic,
            'masa'          => $masa,
        ]);
        return response()->json([
            'message'   => 'Success!',
            'data'      => $kargan
        ]);
    }

    public function show(Request $request, Kargan $kargan)
    {
        return response()->json([
            'message'   => 'Success!',
            'data'      => $kargan->load('product')
        ]);
    }

    public function destroy(Kargan $kargan)
    {
        $kargan->delete();
        return response()->json([
            'message'   => 'Success!',
            'data'      => $kargan
        ]);
    }
}
