<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sop;
use Illuminate\Http\Request;

class SopController extends Controller
{
    public function index()
    {
        return view('sop.index');
    }

    public function create()
    {
        $products = Product::all();
        return view('sop.create', compact('products'));
    }
}
