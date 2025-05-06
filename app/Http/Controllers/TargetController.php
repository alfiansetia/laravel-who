<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Target;
use Illuminate\Http\Request;

class TargetController extends Controller
{
    public function index()
    {
        return view('target.data');
    }

    public function create()
    {
        $products = Product::all();
        return view('target.create', compact('products'));
    }
}
