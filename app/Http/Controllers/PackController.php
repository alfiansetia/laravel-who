<?php

namespace App\Http\Controllers;

use App\Models\Pack;
use App\Models\Product;
use App\Models\Vendor;
use App\Services\Breadcrumb;
use Illuminate\Http\Request;

class PackController extends Controller
{
    public function index()
    {
        $bcms = collect([
            new Breadcrumb('List Packing List', route('packs.index'), false),
        ]);
        $vendors = Vendor::all();
        return view('pack.index', compact(['vendors', 'bcms']));
    }

    public function create()
    {
        $bcms = collect([
            new Breadcrumb('List Packing List', route('packs.index'), true),
            new Breadcrumb('Create Packing List', route('packs.create'), false),
        ]);
        $products = Product::all();
        $vendors = Vendor::all();
        return view('pack.create', compact(['products', 'vendors', 'bcms']));
    }

    public function edit(Pack $pack)
    {
        $data = $pack->load(['product', 'vendor']);
        $bcms = collect([
            new Breadcrumb('List Packing List', route('packs.index'), true),
            new Breadcrumb($pack->name . '-' . ($pack->product->code ?? '-'), route('packs.edit',  $data->id), false),
        ]);
        $products = Product::all();
        $vendors = Vendor::all();
        return view('pack.edit', compact(['data', 'products', 'vendors', 'bcms']));
    }

    public function show($id)
    {
        $product = Product::query()->with('pls')->findOrFail($id);
        return view('pack.show', compact('product'));
    }

    public function print(Pack $pack)
    {
        $pack->load(['product.sop.items', 'vendor', 'items']);
        return view('pack.print', compact('pack'));
    }
}
