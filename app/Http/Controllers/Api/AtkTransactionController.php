<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AtkTransaction;
use Illuminate\Http\Request;

class AtkTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = AtkTransaction::with('atk');
        if ($request->filled('atk_id')) {
            $query->where('atk_id', $request->atk_id);
        }
        $data = $query->orderBy('date', 'asc')->get();
        return response()->json(['data' => $data], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'atk_id'    => 'required|exists:atks,id',
            'date'      => 'required|date_format:Y-m-d',
            'pic'       => 'required|max:200',
            'type'      => 'required|in:in,out',
            'qty'       => 'required|gt:0',
            'desc'      => 'nullable|max:200',
        ]);
        $trx = AtkTransaction::create($request->only([
            'atk_id',
            'date',
            'pic',
            'type',
            'qty',
            'desc'
        ]));
        return response()->json([
            'data' => $trx,
            'message' => 'Success Insert Data'
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $trx = AtkTransaction::with('atk')->find($id);
        if (!$trx) {
            return response()->json([
                'data' => null,
                'message' => 'Data Not Found!',
            ], 404);
        }
        return response()->json([
            'data' => $trx,
            'message' => '',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $trx = AtkTransaction::with('atk')->find($id);
        if (!$trx) {
            return response()->json([
                'data' => null,
                'message' => 'Data Not Found!',
            ], 404);
        }
        $this->validate($request, [
            'atk_id'    => 'required|exists:atks,id',
            'date'      => 'required|date_format:Y-m-d',
            'pic'       => 'required|max:200',
            'type'      => 'required|in:in,out',
            'qty'       => 'required|gt:0',
            'desc'      => 'nullable|max:200',
        ]);
        $trx->update($request->only([
            'atk_id',
            'date',
            'pic',
            'type',
            'qty',
            'desc'
        ]));
        return response()->json([
            'data' => $trx,
            'message' => 'Success Update Data'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $trx = AtkTransaction::with('atk')->find($id);
        if (!$trx) {
            return response()->json([
                'data' => null,
                'message' => 'Data Not Found!',
            ], 404);
        }
        $trx->delete();
        return response()->json([
            'data' => $trx,
            'message' => 'Success Delete Data'
        ], 200);
    }
}
