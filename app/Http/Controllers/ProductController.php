<?php

namespace App\Http\Controllers;

use App\Services\Breadcrumb;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $bcms = collect([
            new Breadcrumb('List Product', route('products.index'), false),
        ]);
        return view('product.data', compact('bcms'));
    }
}
