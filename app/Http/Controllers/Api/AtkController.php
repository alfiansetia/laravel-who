<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AtkResource;
use App\Models\Atk;
use Illuminate\Http\Request;

class AtkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Atk::all();
        return response()->json(['data' => AtkResource::collection($data)], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'code'      => 'required|max:200|unique:atks,code',
            'name'      => 'required|max:200',
            'satuan'    => 'required|max:200',
            'desc'      => 'nullable|max:200',
        ]);
        $atk = Atk::create([
            'code'      => $request->code,
            'name'      => $request->name,
            'satuan'    => $request->satuan,
            'desc'      => $request->desc,
        ]);
        return response()->json([
            'data' => $atk,
            'message' => 'Success Insert Data'
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Atk $atk)
    {
        return response()->json(['data' => new AtkResource($atk->load('transactions'))], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Atk $atk)
    {
        $this->validate($request, [
            'code'      => 'required|max:200|unique:atks,code,' . $atk->id,
            'name'      => 'required|max:200',
            'satuan'    => 'required|max:200',
            'desc'      => 'nullable|max:200',
        ]);
        $atk->update([
            'code'      => $request->code,
            'name'      => $request->name,
            'satuan'    => $request->satuan,
            'desc'      => $request->desc,
        ]);
        return response()->json([
            'data' => $atk,
            'message' => 'Success Update Data'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Atk $atk)
    {
        $atk->delete();
        return response()->json([
            'data' => $atk,
            'message' => 'Success Delete Data'
        ], 200);
    }


    public function import(Request $request)
    {
        foreach ($request->data ?? [] as $key => $item) {
            Atk::query()->updateOrCreate([
                'code' => $item['code'],
            ], [
                'code'      => $item['code'],
                'name'      => $item['name'],
                'satuan'    => strtolower($item['satuan']),
            ]);
        }
        return response()->json([
            'message' => 'Success'
        ]);
    }
}
