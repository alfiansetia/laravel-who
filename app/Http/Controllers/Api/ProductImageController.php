<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function __construct()
    {
        $this->middleware('env_auth')->only(['store', 'destroy', 'destroy_batch']);
    }

    public function index()
    {
        $data = ProductImage::query()->with(['product'])->latest()->get();
        return $this->sendResponse($data, 'Success!');
    }

    public function show($id)
    {
        $data = ProductImage::query()->with(['product'])->find($id);
        if (!$data) {
            return $this->sendNotFound();
        }
        return $this->sendResponse($data, 'Success!');
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'images'     => 'required|array',
            'images.*'   => 'image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $saved = [];
        if (!Storage::disk('public')->exists('products')) {
            Storage::disk('public')->makeDirectory('products');
        }
        foreach ($request->file('images') as $file) {
            $filename = time() . '_' . uniqid() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $path = 'products/' . $filename;
            scaleDown($file)->save(storage_path('app/public/' . $path), 90, 'jpg');
            $img = ProductImage::create([
                'product_id' => $request->product_id,
                'name'       => $filename,
            ]);
            $saved[] = $img;
        }
        return $this->sendResponse($saved, 'Images uploaded');
    }

    public function destroy($id)
    {
        $product_image = ProductImage::find($id);
        if (!$product_image) {
            return $this->sendNotFound();
        }
        $product_image->delete();
        return $this->sendResponse($product_image, 'Deleted!');
    }

    public function destroy_batch(Request $request)
    {
        $this->validate($request, [
            'ids'       => 'required|array',
            'ids.*'     => 'integer|exists:product_images,id',
        ]);
        $images = ProductImage::whereIn('id', $request->ids)->get();
        foreach ($images as $img) {
            if ($img->name && Storage::disk('public')->exists('products/' . $img->name)) {
                Storage::disk('public')->delete('products/' . $img->name);
            }
        }
        $deleted = ProductImage::whereIn('id', $request->ids)->delete();
        return $this->sendResponse([
            'deleted_count' => $deleted
        ], 'Product Images deleted successfully.');
    }
}
