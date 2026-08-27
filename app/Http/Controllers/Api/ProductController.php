<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Odoo;
use App\Services\ProductMoveService;
use App\Services\ProductServices;
use App\Services\ExcelService;
use Illuminate\Http\Request;
use ZipArchive;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    protected $excelService;

    public function __construct(ExcelService $excelService)
    {
        $this->excelService = $excelService;
    }

    public function index(Request $request)
    {
        $data = Product::query()
            ->withCount(['images', 'packs'])
            ->with(['pltbb:id,product_id,p,l,t,b', 'sop:id,product_id'])
            ->orderBy('code', 'ASC')
            ->get(['id', 'code', 'name', 'akl', 'akl_exp', 'desc'])
            ->map(function ($p) {
                $p->pltbb_complete = $p->pltbb && $p->pltbb->is_complete;
                $p->has_pltbb = !is_null($p->pltbb);
                $p->has_sop = !is_null($p->sop);
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

    public function show($id)
    {
        $data = Product::query()->with(['packs.items', 'sop.items', 'images', 'pltbb'])->find($id);
        if (!$data) {
            return $this->sendNotFound();
        }
        return $this->sendResponse($data, 'Success!');
    }

    public function move($id)
    {
        $product = Product::query()->with(['packs.items', 'sop.items'])->find($id);
        if (!$product) {
            return $this->sendNotFound();
        }
        if (!$product->odoo_id) {
            return $this->sendNotFound();
        }
        $data = ProductMoveService::getAll($product->odoo_id);

        return $this->sendResponse($data['records'] ?? [], 'Success!');
    }

    public function sync()
    {
        $records = ProductServices::getAll();
        $chunks = array_chunk($records, 100);
        foreach ($chunks as $chunk) {
            foreach ($chunk as $item) {
                Product::query()->updateOrCreate([
                    'code' => $item['default_code'],
                ], [
                    'odoo_id'   => $item['id'],
                    'code'      => $item['default_code'],
                    'name'      => $item['name'] ?? null,
                    'akl'       => $item['akl_id'] != false ? $item['akl_id'][1] : null,
                    'akl_exp'   => $item['x_studio_valid_to_akl'] != false ? date('Y-m-d', strtotime($item['x_studio_valid_to_akl'])) : null,
                    'desc'      => $item['description'] != false ? $item['description'] : null,
                ]);
            }
        }
        return $this->sendResponse(['message' => 'Success!', 'data' => $records],);
    }

    public function downloadZip($id)
    {
        $product = Product::with(['packs.vendor', 'sop.items'])->find($id);
        if (!$product) {
            return $this->sendNotFound();
        }

        // Clean temp directory
        $tempDir = storage_path('app/temp/' . $product->id . '_' . time());
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0777, true);
        }

        $files = [];

        // 1. Generate Combined Pack (PL) file (Multiple sheets inside service)
        $plName = preg_replace('/[^A-Za-z0-9_.\-+()]/', '-', $product->code) . "-PL.xlsx";
        $plPath = $this->excelService->generateCombinedPack($product, $product->id . '_' . time() . '/' . $plName);
        if ($plPath) {
            $files[$plName] = $plPath;
        }

        // 2. Generate SOP file (Handle multiple sheets inside service)
        $sopName = preg_replace('/[^A-Za-z0-9_.\-+()]/', '-', $product->code) . "-SOP.xlsx";
        $sopPath = $this->excelService->generateSop($product, $product->id . '_' . time() . '/' . $sopName);
        if ($sopPath) {
            $files[$sopName] = $sopPath;
        }

        if (empty($files)) {
            return $this->sendError('No files to download');
        }

        // 3. Create ZIP
        $zipName = preg_replace('/[^A-Za-z0-9_.\-+() ]/', '-', "{$product->code} {$product->name}") . ".zip";
        $zipPath = storage_path("app/temp/{$zipName}");

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($files as $nameInZip => $fullPath) {
                $zip->addFile($fullPath, $nameInZip);
            }
            $zip->close();
        }

        // Cleanup: remove generated excel files immediately after zipping
        File::deleteDirectory($tempDir);

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function compare($product)
    {
        $product = Product::with(['packs.vendor', 'sop.items', 'pltbb'])
            ->where('code', $product)
            ->first();
        if (!$product) {
            return $this->sendNotFound();
        }
        return $this->sendResponse($product, 'Success!');
    }
}
