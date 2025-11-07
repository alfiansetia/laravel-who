<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    public function index()
    {
        $products = getFolderSize(storage_path('app/public/products'));
        $logs = getFolderSize(storage_path('logs'));
        return $this->sendResponse([
            'products' => [
                'value' => $products,
                'parse' => formatBytes($products),
            ],
            'logs' => [
                'value' => $logs,
                'parse' => formatBytes($logs),
            ],
        ]);
    }
}
