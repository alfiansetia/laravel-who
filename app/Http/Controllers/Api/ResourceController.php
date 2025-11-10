<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    public function __construct()
    {
        $this->middleware('env_auth')->only(['destroy_log']);
    }

    public function index()
    {
        $products = getFolderSize(storage_path('app/public/products'));
        $logs = getFolderSize(storage_path('logs'));
        $log_content = '';
        $logPath = storage_path('logs/laravel.log');

        if (file_exists($logPath)) {
            $log_content = file_get_contents($logPath);
        }

        return $this->sendResponse([
            'products' => [
                'value' => $products,
                'parse' => formatBytes($products),
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
