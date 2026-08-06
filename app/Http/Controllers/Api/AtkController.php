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
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 25), 200);
        $page    = max((int) $request->input('page', 1), 1);

        $query = Atk::query()->select('atks.*');

        // computed stok via subquery
        $query->selectSub(function ($sub) {
            $sub->selectRaw("COALESCE(SUM(CASE WHEN type='in' THEN qty ELSE 0 END),0) - COALESCE(SUM(CASE WHEN type='out' THEN qty ELSE 0 END),0)")
                ->from('atk_transactions')
                ->whereColumn('atk_transactions.atk_id', 'atks.id');
        }, 'stok');

        // search
        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('code', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('satuan', 'like', "%{$keyword}%")
                    ->orWhere('desc', 'like', "%{$keyword}%");
            });
        }

        // satuan filter
        if ($request->filled('satuan')) {
            $query->where('satuan', $request->satuan);
        }

        $total = (clone $query)->count();
        $data  = $query->orderBy('code', 'asc')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return response()->json([
            'data'       => $data,
            'total'      => $total,
            'page'       => $page,
            'per_page'   => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ]);
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

    public function destroy_batch(Request $request)
    {
        $this->validate($request, [
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:atks,id',
        ]);
        $deleted = Atk::whereIn('id', $request->ids)->delete();
        return $this->sendResponse(['deleted_count' => $deleted], 'Atk deleted successfully.');
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
