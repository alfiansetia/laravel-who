<?php

namespace App\Http\Controllers;

use App\Models\PackingList;
use App\Models\Product;
use Illuminate\Http\Request;

class PackingListController extends Controller
{
    public function index()
    {
        return view('pl.data');
    }

    public function create()
    {
        $products = Product::all();
        return view('pl.create', compact('products'));
    }


    public function show($id)
    {
        $product = Product::query()->with('pls')->findOrFail($id);
        return view('pl.show', compact('product'));
    }
}
