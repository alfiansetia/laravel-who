<?php

namespace App\Http\Controllers;

use App\Models\Pack;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PackController extends Controller
{
    public function index(Request $request)
    {
        $vendors = Vendor::query()->select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Pack/Index', [
            'title' => 'Packing List',
            'vendors' => $vendors,
            'filters' => $request->only(['search', 'page']),
        ]);
    }

    public function create()
    {
        $products = Product::query()->select('id', 'code', 'name')->orderBy('code')->get();
        $vendors = Vendor::query()->select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Pack/Create', [
            'title' => 'Create Packing List',
            'products' => $products,
            'vendors' => $vendors,
        ]);
    }

    public function edit(Pack $pack)
    {
        $data = $pack->load(['product', 'vendor']);
        $products = Product::query()->select('id', 'code', 'name')->orderBy('code')->get();
        $vendors = Vendor::query()->select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Pack/Edit', [
            'title' => 'Edit Packing List',
            'pack' => $data,
            'products' => $products,
            'vendors' => $vendors,
        ]);
    }

    public function show($id)
    {
        return redirect()->route('packs.index');
    }

    public function print(Pack $pack)
    {
        $pack->load(['product.sop.items', 'vendor', 'items.children']);

        return view('pack.print', compact('pack'));
    }

    public function printCombined(Pack $pack)
    {
        $pack->load(['product.sop.items', 'vendor', 'items.children', 'product.pltbb']);
        $sop = $pack->product->sop;

        return view('pack.print_combined', compact('pack', 'sop'));
    }
}
