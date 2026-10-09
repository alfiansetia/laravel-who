<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sop;
use App\Models\SopItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SopController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Sop/Index', [
            'title' => 'SOP QC',
            'filters' => $request->only(['search', 'page']),
        ]);
    }

    public function create()
    {
        $products = Product::query()->select('id', 'code', 'name')->orderBy('code')->get();
        $sop_items = SopItem::query()
            ->select('item')
            ->distinct()
            ->get();

        return Inertia::render('Sop/Create', [
            'title' => 'Manage SOP QC',
            'products' => $products,
            'sopItems' => $sop_items->pluck('item'),
        ]);
    }

    public function show(Sop $sop)
    {
        return redirect()->route('sops.index');
    }

    public function edit(Sop $sop)
    {
        return redirect()->route('sops.index');
    }

    public function print(Sop $sop)
    {
        $sop->load(['product', 'items']);

        return view('sop.print', compact('sop'));
    }
}
