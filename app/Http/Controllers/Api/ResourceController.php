<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductImageStorage;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    public function __construct()
    {
        $this->middleware('env_auth')->only(['destroy_log']);
    }

    public function index()
    {
        // Produk kini primer di S3; local hanya sisa yang belum di-sync.
        $s3 = ProductImageStorage::s3Stats();
        $localProducts = getFolderSize(storage_path('app/public/products'));
        $logs = getFolderSize(storage_path('logs'));
        $log_content = '';
        $logPath = storage_path('logs/laravel.log');

        if (file_exists($logPath)) {
            $log_content = file_get_contents($logPath);
        }

        return $this->sendResponse([
            'products' => [
                'files' => $s3['files'],
                'value' => $s3['bytes'],
                'parse' => formatBytes($s3['bytes']),
                'truncated' => $s3['truncated'],
                'error' => $s3['error'],
            ],
            'products_local' => [
                'value' => $localProducts,
                'parse' => formatBytes($localProducts),
            ],
            'logs' => [
                'value' => $logs,
                'parse' => formatBytes($logs),
                'content'   => $log_content
            ],
        ]);
    }

    public function destroy_log(Request $request)
    {
        $logPath = storage_path('logs/laravel.log');
        if (file_exists($logPath)) {
            file_put_contents($logPath, '');
            return $this->sendResponse(null, 'Log berhasil dikosongkan.');
        }
    }
}
