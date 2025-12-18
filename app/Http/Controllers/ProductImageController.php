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

    public function collage(Product $product)
    {
        $bcms = collect([
            new Breadcrumb('List Product Image', route('product_images.index'), true),
            new Breadcrumb('Cetak Kolase', route('product_images.collage', $product->id), false),
        ]);

        $images = ProductImage::with('product')
            ->where('product_id', $product->id)
            ->get();

        return view('product_image.collage', compact(['bcms', 'images']));
    }
}
