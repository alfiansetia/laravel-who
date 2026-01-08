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
     * Display the specified resource.
     */
    public function show(Problem $problem)
    {
        return response()->json(['data' => $problem->load(['items.product', 'logs'])]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Problem $problem)
    {
        $problem->delete();
        return response()->json(['message' => 'Problem deleted successfully']);
    }

    public function destroy_batch(Request $request)
    {
        $this->validate($request, [
            'ids'       => 'required|array',
            'ids.*'     => 'integer|exists:problems,id',
        ]);
        $deleted = Problem::whereIn('id', $request->ids)->delete();
        return $this->sendResponse([
            'deleted_count' => $deleted
        ], 'Problem deleted successfully.');
    }


    public function import(Request $request)
    {
        $this->validate($request, [
            'data'              => 'required|array',
            'data.*'            => 'array',
            'data.*.product'    => 'required',
            'data.*.lot'        => 'required|max:200',
            'data.*.ed'         => 'nullable|max:200',
            'data.*.date'       => 'required|date_format:Y-m-d',
            'data.*.qc_by'      => 'nullable|max:200',
            'data.*.qc_note'    => 'nullable|max:200',
        ]);
        try {
            DB::beginTransaction();
            $problems = [];
            foreach ($request->data ?? [] as $key => $item) {
                $product = Product::query()->where('code', $item['product'])->first();
                if (!$product) {
                    throw new Exception('Product not found: ' . $item['product']);
                }
                $problems[] = Problem::create([
                    'product_id' => $product->id,
                    'lot_number' => $item['lot'],
                    'lot_expiry' => $item['ed'],
                    'qc_date'    => $item['date'],
                    'qc_by'      => $item['qc_by'] ?? null,
                    'qc_note'    => $item['qc_note'] ?? null,
                ]);
            }
            DB::commit();
            return $this->sendResponse($problems, 'Problem created successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->sendError($th->getMessage());
        }
    }
}
