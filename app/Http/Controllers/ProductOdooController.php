<?php

namespace App\Http\Controllers;

use App\Services\Breadcrumb;
use Illuminate\Http\Request;

class ProductOdooController extends Controller
{
    public function index()
    {
        $bcms = collect([
            new Breadcrumb('Product Odoo', route('product_odoo.index'), false),
        ]);
        return view('product_odoo.index', compact('bcms'))->with('title', 'Product Odoo');
    }
}
