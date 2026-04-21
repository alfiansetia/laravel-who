<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Problem;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProblemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Problem::query();

        // Filter by type (dus/unit)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by year
        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        }

        // Filter by product (problems that have items with this product)
        if ($request->filled('product_id')) {
            $query->whereHas('items', function ($q) use ($request) {
                $q->where('product_id', $request->product_id);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $data = $query->orderBy('date', 'desc')->get();
        return response()->json(['data' => $data]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date'   => 'required|date',
            'number' => 'required|string|unique:problems,number',
            'type'   => 'required|in:dus,unit',
            'stock'  => 'required|in:stock,import',
            'pic'    => 'required|string|max:100',
            'items'  => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty'        => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            $problem = Problem::create($request->only([
                'date',
                'number',
                'type',
                'stock',
                'ri_po',
                'status',
                'email_on',
                'pic'
            ]));

            foreach ($request->items as $item) {
                $problem->items()->create([
                    'product_id' => $item['product_id'],
                    'qty'        => $item['qty'],
                    'lot'        => $item['lot'] ?? null,
                    'desc'       => $item['desc'] ?? null,
                ]);
            }

            return response()->json(['message' => 'Data Problem berhasil disimpan.', 'data' => $problem]);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Problem $problem)
    {
        return response()->json(['data' => $problem->load(['items.product', 'logs'])]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Problem $problem)
    {
        $request->validate([
            'date'     => 'required|date',
            'number'   => 'required|string|unique:problems,number,' . $problem->id,
            'type'     => 'required|in:dus,unit',
            'stock'    => 'required|in:stock,import',
            'pic'      => 'required|string|max:100',
            'email_on' => 'nullable|date',
            'ri_po'    => 'nullable|string|max:100',
            'status'   => 'nullable|in:done,pending',
            'items'    => 'sometimes|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty'        => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request, $problem) {
            $problem->update($request->only([
                'date',
                'number',
                'type',
                'stock',
                'email_on',
                'ri_po',
                'status',
                'pic'
            ]));

            if ($request->has('items')) {
                $problem->items()->delete();
                foreach ($request->items as $item) {
                    $problem->items()->create([
                        'product_id' => $item['product_id'],
                        'qty'        => $item['qty'],
                        'lot'        => $item['lot'] ?? null,
                        'desc'       => $item['desc'] ?? null,
                    ]);
                }
            }

            return response()->json(['message' => 'Data Problem berhasil diperbarui.']);
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Problem $problem)
    {
        $problem->delete();
        return response()->json(['message' => 'Data Problem berhasil dihapus.']);
    }

    /**
     * Duplicate a problem.
     */
    public function duplicate(Problem $problem)
    {
        return DB::transaction(function () use ($problem) {
            $newProblem = $problem->replicate();

            $prefix = strtoupper(date('Y-M-'));
            $latest = Problem::where('number', 'like', $prefix . '%')->orderBy('number', 'desc')->first();

            if ($latest) {
                $parts = explode('-', $latest->number);
                $serial = intval(end($parts)) + 1;
                $newNumber = $prefix . str_pad($serial, 3, '0', STR_PAD_LEFT);
            } else {
                $newNumber = $prefix . '001';
            }

            $newProblem->number = $newNumber;
            $newProblem->date = date('Y-m-d');
            $newProblem->status = 'pending';
            $newProblem->save();

            foreach ($problem->items as $item) {
                $newItem = $item->replicate();
                $newItem->problem_id = $newProblem->id;
                $newItem->save();
            }

            return response()->json(['message' => 'Data Problem berhasil diduplikasi.', 'data' => $newProblem]);
        });
    }

    /**
     * Get next sequence number.
     */
    public function nextNumber()
    {
        $prefix = strtoupper(date('Y-M-'));
        $latest = Problem::where('number', 'like', $prefix . '%')->orderBy('number', 'desc')->first();

        if ($latest) {
            $parts = explode('-', $latest->number);
            $serial = intval(end($parts)) + 1;
            $newNumber = $prefix . str_pad($serial, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = $prefix . '001';
        }

        return response()->json(['number' => $newNumber]);
    }

    public function destroy_batch(Request $request)
    {
        $request->validate([
            'ids'       => 'required|array',
            'ids.*'     => 'integer|exists:problems,id',
        ]);
        $deleted = Problem::whereIn('id', $request->ids)->delete();
        return response()->json(['message' => $deleted . ' data berhasil dihapus.']);
    }
}
