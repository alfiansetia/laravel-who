<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Breadcrumb;
use Illuminate\Http\Request;

class QcController extends Controller
{
    public function index()
    {
        $bcms = collect([
            new Breadcrumb('Form QC', route('qc.index'), false),
        ]);
        $products = Product::all();
        return view('qc.index', compact('products', 'bcms'))->with('title', 'Form QC');
    }
}
