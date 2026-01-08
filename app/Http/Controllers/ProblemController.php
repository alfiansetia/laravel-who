<?php

namespace App\Http\Controllers;

use App\Models\Problem;
use App\Models\Product;
use App\Services\Breadcrumb;
use Illuminate\Http\Request;

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
        $problem = Problem::create($request->only([
            'date',
            'number',
            'type',
            'stock',
            'ri_po',
            'status',
            'email_on',
            'pic',
        ]));

        // Create items if provided
        if ($request->has('items') && is_array($request->items)) {
            foreach ($request->items as $item) {
                if (!empty($item['product_id']) && !empty($item['qty'])) {
                    $problem->items()->create([
                        'product_id' => $item['product_id'],
                        'qty' => $item['qty'],
                        'lot' => $item['lot'] ?? null,
                        'desc' => $item['desc'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('problems.edit', $problem->id);
    }

    /**
     * Display the specified resource.
     */
    public function show(Problem $problem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Problem $problem)
    {
        $data = $problem->load(['items.product', 'logs']);
        $products = Product::orderBy('code')->get();
        return view('problem.edit', compact('data', 'products'))->with('title', 'Data Problem');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Problem $problem)
    {
        $problem->update($request->only([
            'date',
            'number',
            'type',
            'stock',
            'ri_po',
            'status',
            'email_on',
            'pic',
        ]));
        return redirect()->route('problems.edit', $problem->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Problem $problem)
    {
        //
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
