<?php

namespace App\Http\Controllers;

use App\Models\Pack;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;

class PackController extends Controller
{
    public function index()
    {
        $vendors = Vendor::all();
        return view('pack.index', compact(['vendors']));
    }

    public function create()
    {
        $products = Product::all();
        $vendors = Vendor::all();
        return view('pack.create', compact(['products', 'vendors']));
    }

    public function edit(Pack $pack)
    {
        $data = $pack;
        $products = Product::all();
        $vendors = Vendor::all();
        return view('pack.edit', compact(['data', 'products', 'vendors']));
    }

    public function show($id)
    {
        $product = Product::query()->with('pls')->findOrFail($id);
        return view('pl.show', compact('product'));
    }
}
