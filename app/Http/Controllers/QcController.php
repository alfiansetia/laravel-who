<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class QcController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return view('qc.index', compact('products'))->with('title', 'Form QC');
    }
}
