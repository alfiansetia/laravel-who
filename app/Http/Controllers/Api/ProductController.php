<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Odoo;
use App\Services\ProductMoveService;
use App\Services\ProductServices;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function index()
    {
        $data = Product::orderBy('code', 'ASC')->get();
        return $this->sendResponse($data, 'Success!');
    }

    public function show($id)
    {
        $data = Product::query()->with(['packs.items', 'sop.items', 'images'])->find($id);
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
}
