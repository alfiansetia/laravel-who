<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Breadcrumb;
use Illuminate\Http\Request;

class LotController extends Controller
{
    public function index()
    {
        $bcms = collect([
            new Breadcrumb('List Lot Stock', route('lots.index'), false),
        ]);
        $products = Product::all();
        return view('lot.index', compact('bcms', 'products'))->with(['title' => 'Data Lot']);
    }
}
