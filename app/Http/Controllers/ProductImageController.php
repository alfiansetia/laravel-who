<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\Breadcrumb;
use App\Services\ProductImageStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use ZipArchive;

class ProductImageController extends Controller
{
    public function __construct()
    {
        $this->middleware('env_auth')->only(['store', 'destroy', 'destroy_batch']);
    }

    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            $data = ProductImage::query()->with(['product'])->latest()->get();

            return $this->sendResponse($data, 'Success!');
        }

        return Inertia::render('ProductImage/Index', [
            'title' => 'Product Images',
            'products' => Product::orderBy('name')->get(['id', 'code', 'name']),
        ]);
    }

    public function show(Request $request, $product_image): JsonResponse|RedirectResponse
    {
        $data = ProductImage::query()->with(['product'])->find($product_image);
        if (! $data) {
            if ($request->wantsJson()) {
                return $this->sendNotFound();
            }

            return redirect()->route('product_images.index')
                ->with('error', 'Gambar tidak ditemukan.');
        }

        if ($request->wantsJson()) {
            return $this->sendResponse($data, 'Success!');
        }

        return redirect()->route('product_images.index');
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'images' => 'required|array',
                'images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
            ]);

            $saved = [];
            $failed = [];
            foreach ($request->file('images') as $file) {
                $filename = time().'_'.uniqid().'_'.preg_replace('/\s+/', '_', $file->getClientOriginalName());
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
                    'name' => $filename,
                ]);
                $saved[] = $img;
            }
            if (empty($saved)) {
                return response()->json(['success' => false, 'message' => 'Upload gagal, file tidak tersimpan di S3/R2.', 'failed' => $failed], 500);
            }

            return $this->sendResponse($saved, 'Images uploaded');
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $count = 0;
        $failed = [];
        foreach ($request->file('images') as $file) {
            $filename = time().'_'.uniqid().'_'.preg_replace('/\s+/', '_', $file->getClientOriginalName());
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
            ProductImage::create([
                'product_id' => $request->product_id,
                'name' => $filename,
            ]);
            $count++;
        }

        if ($count === 0) {
            return redirect()->route('product_images.index')
                ->with('error', 'Upload gagal, file tidak tersimpan di S3/R2. Cek permission API token R2 dan log laravel.');
        }

        return redirect()->route('product_images.index')
            ->with('success', "{$count} gambar berhasil diupload.");
    }

    public function destroy(Request $request, ProductImage $product_image): JsonResponse|RedirectResponse
    {
        $product_image->delete();

        if ($request->wantsJson()) {
            return $this->sendResponse($product_image, 'Deleted!');
        }

        return redirect()->route('product_images.index')
            ->with('success', 'Gambar berhasil dihapus.');
    }

    public function destroy_batch(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->has('ids')) {
            $this->validate($request, [
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:product_images,id',
            ]);
            $images = ProductImage::whereIn('id', $request->ids)->get();
            foreach ($images as $img) {
                if ($img->name) {
                    ProductImageStorage::delete($img->name);
                }
            }
            $deleted = ProductImage::whereIn('id', $request->ids)->delete();

            if ($request->wantsJson()) {
                return $this->sendResponse([
                    'deleted_count' => $deleted,
                ], 'Product Images deleted successfully.');
            }

            return redirect()->route('product_images.index')
                ->with('success', "{$deleted} gambar berhasil dihapus.");
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $images = ProductImage::where('product_id', $request->product_id)->get();
        foreach ($images as $img) {
            $img->delete(); // model observer handles file deletion
        }

        if ($request->wantsJson()) {
            return $this->sendResponse([
                'deleted_count' => $images->count(),
            ], 'Product Images deleted successfully.');
        }

        return redirect()->route('product_images.index')
            ->with('success', "{$images->count()} gambar berhasil dihapus.");
    }

    public function download(Product $product)
    {
        $images = ProductImage::where('product_id', $product->id)->get();

        if ($images->isEmpty()) {
            return redirect()->route('product_images.index')
                ->with('error', 'Tidak ada gambar untuk produk ini.');
        }

        $zip = new ZipArchive;
        $tempFile = tempnam(sys_get_temp_dir(), 'product_images_');

        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return redirect()->route('product_images.index')
                ->with('error', 'Gagal membuat file ZIP.');
        }

        foreach ($images as $index => $image) {
            $contents = $image->name ? ProductImageStorage::get($image->name) : null;
            if ($contents !== null) {
                $ext = pathinfo($image->name, PATHINFO_EXTENSION) ?: 'jpg';
                $zip->addFromString('images/'.($index + 1).'.'.$ext, $contents);
            }
        }

        $zip->close();

        // Sanitize filename: keep alphanumeric, spaces, dots, hyphens, underscores
        $safeCode = preg_replace('/[^A-Za-z0-9.\-_ ]/', '', $product->code ?? '');
        $safeName = preg_replace('/[^A-Za-z0-9.\-_ ]/', '', $product->name ?? '');
        $zipName = trim($safeCode.' ('.$safeName.')').'.zip';

        return response()->download($tempFile, $zipName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
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
