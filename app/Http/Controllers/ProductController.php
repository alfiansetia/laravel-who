<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ExcelService;
use App\Services\ProductMoveService;
use App\Services\ProductServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use ZipArchive;

class ProductController extends Controller
{
    protected $excelService;

    public function __construct(ExcelService $excelService)
    {
        $this->excelService = $excelService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $data = Product::query()
                ->withCount(['images', 'packs'])
                ->with(['pltbb:id,product_id,p,l,t,b', 'sop:id,product_id'])
                ->orderBy('code', 'ASC')
                ->get(['id', 'code', 'name', 'akl', 'akl_exp', 'desc'])
                ->map(function ($p) {
                    $p->pltbb_complete = $p->pltbb && $p->pltbb->is_complete;
                    $p->has_pltbb = ! is_null($p->pltbb);
                    $p->has_sop = ! is_null($p->sop);
                    $p->has_image = $p->images_count > 0;
                    $p->has_pl = $p->packs_count > 0;
                    $p->pltbb_display = $p->pltbb
                        ? "{$p->pltbb->p}x{$p->pltbb->l}x{$p->pltbb->t}/{$p->pltbb->b}"
                        : '-';
                    unset($p->pltbb, $p->sop);

                    return $p;
                });

            return $this->sendResponse($data, 'Success!');
        }

        return Inertia::render('Product/Index', [
            'title' => 'Data Product',
            'filters' => $request->only(['search']),
        ]);
    }

    public function show(Request $request, $id)
    {
        $data = Product::query()->with(['packs.items', 'sop.items', 'images', 'pltbb'])->find($id);
        if (! $data) {
            if ($request->wantsJson()) {
                return $this->sendNotFound();
            }

            return redirect()->back()->with('error', 'Data Tidak Ditemukan');
        }

        return $this->sendResponse($data, 'Success!');
    }

    public function move(Request $request, $id)
    {
        $product = Product::query()->with(['packs.items', 'sop.items'])->find($id);
        if (! $product) {
            if ($request->wantsJson()) {
                return $this->sendNotFound();
            }

            return redirect()->back()->with('error', 'Data Tidak Ditemukan');
        }
        if (! $product->odoo_id) {
            if ($request->wantsJson()) {
                return $this->sendNotFound();
            }

            return redirect()->back()->with('error', 'Data Tidak Ditemukan');
        }
        $data = ProductMoveService::getAll($product->odoo_id);

        if ($request->wantsJson()) {
            return $this->sendResponse($data['records'] ?? [], 'Success!');
        }

        return redirect()->back()->with('success', 'Success!');
    }

    public function sync(Request $request)
    {
        $records = ProductServices::getAll();
        $chunks = array_chunk($records, 100);
        foreach ($chunks as $chunk) {
            foreach ($chunk as $item) {
                Product::query()->updateOrCreate([
                    'code' => $item['default_code'],
                ], [
                    'odoo_id' => $item['id'],
                    'code' => $item['default_code'],
                    'name' => $item['name'] ?? null,
                    'akl' => $item['akl_id'] != false ? $item['akl_id'][1] : null,
                    'akl_exp' => $item['x_studio_valid_to_akl'] != false ? date('Y-m-d', strtotime($item['x_studio_valid_to_akl'])) : null,
                    'desc' => $item['description'] != false ? $item['description'] : null,
                ]);
            }
        }

        if ($request->wantsJson()) {
            return $this->sendResponse(['message' => 'Success!', 'data' => $records]);
        }

        return redirect()->back()->with('success', 'Success!');
    }

    public function downloadZip($id)
    {
        $product = Product::with(['packs.vendor', 'sop.items'])->find($id);
        if (! $product) {
            return $this->sendNotFound();
        }

        // Clean temp directory
        $tempDir = storage_path('app/temp/'.$product->id.'_'.time());
        if (! File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0777, true);
        }

        $files = [];

        // 1. Generate Combined Pack (PL) file (Multiple sheets inside service)
        $plName = preg_replace('/[^A-Za-z0-9_.\-+()]/', '-', $product->code).'-PL.xlsx';
        $plPath = $this->excelService->generateCombinedPack($product, $product->id.'_'.time().'/'.$plName);
        if ($plPath) {
            $files[$plName] = $plPath;
        }

        // 2. Generate SOP file (Handle multiple sheets inside service)
        $sopName = preg_replace('/[^A-Za-z0-9_.\-+()]/', '-', $product->code).'-SOP.xlsx';
        $sopPath = $this->excelService->generateSop($product, $product->id.'_'.time().'/'.$sopName);
        if ($sopPath) {
            $files[$sopName] = $sopPath;
        }

        if (empty($files)) {
            return $this->sendError('No files to download');
        }

        // 3. Create ZIP
        $zipName = preg_replace('/[^A-Za-z0-9_.\-+() ]/', '-', "{$product->code}").'.zip';
        $zipPath = storage_path("app/temp/{$zipName}");

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($files as $nameInZip => $fullPath) {
                $zip->addFile($fullPath, $nameInZip);
            }
            $zip->close();
        }

        // Cleanup: remove generated excel files immediately after zipping
        File::deleteDirectory($tempDir);

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function compare(Request $request, $product)
    {
        $product = Product::with(['packs.vendor', 'sop.items', 'pltbb'])
            ->where('code', $product)
            ->first();
        if (! $product) {
            if ($request->wantsJson()) {
                return $this->sendNotFound();
            }

            return redirect()->back()->with('error', 'Data Tidak Ditemukan');
        }

        if ($request->wantsJson()) {
            return $this->sendResponse($product, 'Success!');
        }

        return redirect()->back()->with('success', 'Success!');
    }

    /**
     * Pencarian ringan untuk autocomplete (ref product CODE di form AKL Item).
     * GET /api/products/search?q=xxx  (dicari by code saja)
     */
    public function search(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        if (strlen($q) < 1) {
            if ($request->wantsJson()) {
                return $this->sendResponse([], 'Success!');
            }

            return redirect()->back()->with('success', 'Success!');
        }

        $data = Product::query()
            ->where('code', 'like', "%{$q}%")
            ->orderBy('code')
            ->limit(20)
            ->get(['id', 'code', 'name']);

        if ($request->wantsJson()) {
            return $this->sendResponse($data, 'Success!');
        }

        return redirect()->back()->with('success', 'Success!');
    }
}
