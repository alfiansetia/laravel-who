<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductImage;
use App\Services\ProductImageStorage;
use Illuminate\Http\Request;

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
        $failed = [];
        foreach ($request->file('images') as $file) {
            $filename = time() . '_' . uniqid() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $encoded = scaleDown($file)->encodeByExtension('jpg', quality: 90);
            try {
                $ok = ProductImageStorage::put($filename, (string) $encoded);
            } catch (\Throwable $e) {
                report($e);
                $ok = false;
            }
            if (! $ok) {
                $failed[] = $file->getClientOriginalName();
                continue;
            }
            $img = ProductImage::create([
                'product_id' => $request->product_id,
                'name'       => $filename,
            ]);
            $saved[] = $img;
        }
        if (empty($saved)) {
            return response()->json(['success' => false, 'message' => 'Upload gagal, file tidak tersimpan di S3/R2.', 'failed' => $failed], 500);
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
            if ($img->name) {
                ProductImageStorage::delete($img->name);
            }
        }
        $deleted = ProductImage::whereIn('id', $request->ids)->delete();
        return $this->sendResponse([
            'deleted_count' => $deleted
        ], 'Product Images deleted successfully.');
    }
}
