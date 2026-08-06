<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\Breadcrumb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function index(Request $request)
    {
        $bcms = collect([
            new Breadcrumb('Product Images', route('product_images.index'), false),
        ]);

        // Products with images only, for the main gallery
        $query = Product::with('images')->whereHas('images');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->paginate(10)->withQueryString();

        // All products for upload modal
        $allProducts = Product::orderBy('name')->get();

        return view('product_image.index', compact(['bcms', 'products', 'allProducts']));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'images'     => 'required|array',
            'images.*'   => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if (!Storage::disk('public')->exists('products')) {
            Storage::disk('public')->makeDirectory('products');
        }

        $count = 0;
        foreach ($request->file('images') as $file) {
            $filename = time() . '_' . uniqid() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $path = 'products/' . $filename;
            scaleDown($file)->save(storage_path('app/public/' . $path), 90, 'jpg');
            ProductImage::create([
                'product_id' => $request->product_id,
                'name'       => $filename,
            ]);
            $count++;
        }

        return redirect()->route('product_images.index')
            ->with('success', "{$count} gambar berhasil diupload.");
    }

    public function destroy(ProductImage $product_image)
    {
        $product_image->delete();
        return redirect()->route('product_images.index')
            ->with('success', 'Gambar berhasil dihapus.');
    }

    public function destroy_batch(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $images = ProductImage::where('product_id', $request->product_id)->get();
        foreach ($images as $img) {
            $img->delete(); // model observer handles file deletion
        }

        return redirect()->route('product_images.index')
            ->with('success', "{$images->count()} gambar berhasil dihapus.");
    }

    public function collage(Product $product)
    {
        $bcms = collect([
            new Breadcrumb('Product Images', route('product_images.index'), true),
            new Breadcrumb('Cetak Kolase', route('product_images.collage', $product->id), false),
        ]);

        $images = ProductImage::with('product')
            ->where('product_id', $product->id)
            ->get();

        return view('product_image.collage', compact(['bcms', 'images']));
    }
}
