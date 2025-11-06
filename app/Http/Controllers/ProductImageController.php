<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\Breadcrumb;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    public function index(Request $request)
    {
        $bcms = collect([
            new Breadcrumb('List Product Image', route('product_images.index'), false),
        ]);
        $products = Product::all();
        return view('product_image.index', compact(['bcms', 'products']));
    }
}
