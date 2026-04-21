<?php

namespace App\Http\Controllers;

use App\Models\Problem;
use App\Models\Product;
use App\Services\Breadcrumb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProblemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::orderBy('code')->get();
        return view('problem.index', compact('products'))->with('title', 'Data Problem');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::orderBy('code')->get();
        return view('problem.create', compact('products'))->with('title', 'Data Problem');
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
            'email_on' => 'nullable|date',
            'ri_po'    => 'nullable|string|max:100',
            'status'   => 'nullable|in:done,pending',
        ]);

        return DB::transaction(function () use ($request) {
            $problem = Problem::create($request->only([
                'date',
                'number',
                'type',
                'stock',
                'email_on',
                'ri_po',
                'status',
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

            return redirect()->route('problems.edit', $problem->id)
                ->with('success', 'Data Problem berhasil disimpan.');
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
     * Show the form for editing the specified resource.
     */
    public function edit(Problem $problem)
    {
        $data = $problem->load(['items.product', 'logs']);
        $products = Product::orderBy('code')->get();
        return view('problem.edit', compact('data', 'products'))->with('title', 'Edit Problem');
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
        ]);

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

        if ($request->ajax()) {
            return response()->json(['message' => 'Informasi Problem berhasil diperbarui.']);
        }

        return redirect()->route('problems.edit', $problem->id)
            ->with('success', 'Informasi Problem berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Problem $problem)
    {
        $problem->delete();
        if (request()->ajax()) {
            return response()->json(['message' => 'Data Problem berhasil dihapus.']);
        }
        return redirect()->route('problems.index')
            ->with('success', 'Data Problem berhasil dihapus.');
    }


    public function import()
    {
        $bcms = collect([
            new Breadcrumb('Problem', route('problems.index'), true),
            new Breadcrumb('Import', route('problems.import'), false),
        ]);
        return view('problem.import', compact('bcms'));
    }
}
