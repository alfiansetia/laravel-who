<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProblemItem;
use Illuminate\Http\Request;

class ProblemItemController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'problem_id' => 'required|exists:problems,id',
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
            'lot' => 'nullable|string',
            'desc' => 'nullable|string',
        ]);

        $item = ProblemItem::create([
            'problem_id' => $request->problem_id,
            'product_id' => $request->product_id,
            'qty' => $request->qty,
            'lot' => $request->lot,
            'desc' => $request->desc,
        ]);

        return response()->json([
            'message' => 'Problem item created successfully',
            'data' => $item->load('product'),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProblemItem $problemItem)
    {
        return response()->json([
            'data' => $problemItem->load('product'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProblemItem $problemItem)
    {
        $request->validate([
            'qty' => 'sometimes|integer|min:1',
            'lot' => 'nullable|string',
            'desc' => 'nullable|string',
        ]);

        $problemItem->update($request->only(['qty', 'lot', 'desc']));

        return response()->json([
            'message' => 'Problem item updated successfully',
            'data' => $problemItem->load('product'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProblemItem $problemItem)
    {
        $problemItem->delete();

        return response()->json([
            'message' => 'Problem item deleted successfully',
        ]);
    }
}
