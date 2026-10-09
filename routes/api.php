<?php

// API routes tersisa: proxy Odoo/read-only, sync, auth, dan modul Blade
// (alamats, shipping-estimate). Modul CRUD Inertia sudah pindah ke
// routes/web.php dengan single controller dual-mode.

use App\Http\Controllers\AlamatController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoController;
use App\Http\Controllers\ItController;
use App\Http\Controllers\IzinEdarController;
use App\Http\Controllers\LotController;
use App\Http\Controllers\POController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductOdooController;
use App\Http\Controllers\RIController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShippingEstimateController;
use App\Http\Controllers\SoController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\VendorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('do', [DoController::class, 'index'])->name('api.do.index');
Route::get('do/{id}', [DoController::class, 'detail'])->name('api.do.detail');

Route::post('so/{id}/mark-as-print', [SoController::class, 'mark_as_print'])->name('api.so.mark_as_print');
Route::post('so/{id}/mark-as-unprint', [SoController::class, 'mark_as_unprint'])->name('api.so.mark_as_unprint');
Route::get('so', [SoController::class, 'index'])->name('api.so.index');
Route::get('so/{id}', [SoController::class, 'detail'])->name('api.so.detail');

Route::get('it', [ItController::class, 'index'])->name('api.it.index');
Route::get('it/{id}', [ItController::class, 'detail'])->name('api.it.detail');

Route::post('detail_alamat/{detail_alamat}/order', [AlamatController::class, 'detailOrder'])->name('api.detail_alamat.order');
Route::post('detail_alamat', [AlamatController::class, 'detailStore'])->name('api.detail_alamat.store');
Route::match(['put', 'patch'], 'detail_alamat/{detail_alamat}', [AlamatController::class, 'detailUpdate'])->name('api.detail_alamat.update');
Route::delete('detail_alamat/{detail_alamat}', [AlamatController::class, 'detailDestroy'])->name('api.detail_alamat.destroy');

Route::get('product-odoo/{id}/{variant}/move', [ProductOdooController::class, 'move'])->name('api.product_odoo.move');
Route::get('product-odoo/{id}/{variant}/on-hand', [ProductOdooController::class, 'on_hand'])->name('api.product_odoo.on_hand');
Route::get('product-odoo/{id}', [ProductOdooController::class, 'detail'])->name('api.product_odoo.detail');
Route::get('product-odoo', [ProductOdooController::class, 'index'])->name('api.product_odoo.index');

Route::get('stock-opname', [StockController::class, 'opname'])->name('api.stock.opname');
Route::get('stock/{id}', [StockController::class, 'lot'])->name('api.stock.lot');
Route::get('stock', [StockController::class, 'index'])->name('api.stock.index');

Route::get('lot/{id}/trace', [LotController::class, 'trace'])->name('api.lots.trace');
Route::get('lot/{id}', [LotController::class, 'detail'])->name('api.lots.lot');
Route::get('lot', [LotController::class, 'index'])->name('api.lots.index');

Route::get('vendor-odoo/{id}', [VendorController::class, 'vendorOdooDetail'])->name('api.vendor_odoo.detail');
Route::get('vendor-odoo', [VendorController::class, 'vendorOdooIndex'])->name('api.vendor_odoo.index');

Route::get('monitor-do', [DoController::class, 'monitor'])->name('api.monitor.do');

Route::get('po', [POController::class, 'index'])->name('api.po.index');
Route::get('po/order-line', [POController::class, 'order_line'])->name('api.po.order_line');
Route::get('po/{id}', [POController::class, 'detail'])->name('api.po.detail');

Route::get('ri', [RIController::class, 'index'])->name('api.ri.index');
Route::get('ri/order-line', [RIController::class, 'order_line'])->name('api.ri.order_line');
Route::get('ri/{id}', [RIController::class, 'detail'])->name('api.ri.detail');

Route::get('firebase-config', function () {
    return response()->json([
        'apiKey' => config('services.firebase.api_key'),
        'authDomain' => config('services.firebase.auth_domain'),
        'projectId' => config('services.firebase.project_id'),
        'storageBucket' => config('services.firebase.storage_bucket'),
        'messagingSenderId' => config('services.firebase.messaging_sender_id'),
        'appId' => config('services.firebase.app_id'),
        'measurementId' => config('services.firebase.measurement_id'),
    ]);
});

Route::post('alamats/{alamat}/duplicate', [AlamatController::class, 'duplicate'])
    ->name('api.alamats.duplicate');
Route::get('alamats/{alamat}/sync', [AlamatController::class, 'sync'])
    ->name('api.alamats.sync');
Route::delete('alamats', [AlamatController::class, 'destroy_batch'])
    ->name('api.alamats.delete_batch');
Route::apiResource('alamats', AlamatController::class)
    ->names('api.alamats');

Route::get('settings/cek-odoo', [SettingController::class, 'cek_odoo'])
    ->name('api.settings.cek_odoo');
Route::get('settings/', [SettingController::class, 'index'])
    ->name('api.settings.index');
Route::post('settings/', [SettingController::class, 'store'])
    ->name('api.settings.store');
Route::put('settings/', [SettingController::class, 'reload'])
    ->name('api.settings.reload');
Route::delete('settings/', [SettingController::class, 'test_notif'])
    ->name('api.settings.test_notif');

Route::post('spreadsheet/sync-all', [ToolController::class, 'spreadsheetSyncAll'])
    ->name('api.spreadsheet.sync_all');
Route::get('spreadsheet', [ToolController::class, 'spreadsheetIndex'])
    ->name('api.spreadsheet.index');
Route::post('spreadsheet', [ToolController::class, 'spreadsheetSyncProduct'])
    ->name('api.spreadsheet.sync_product');

Route::post('tokens/test', [SettingController::class, 'tokenTest'])
    ->name('api.tokens.test');
Route::get('tokens', [SettingController::class, 'tokenIndex'])
    ->name('api.tokens.index');
Route::get('tokens/{token}', [SettingController::class, 'tokenShow'])
    ->name('api.tokens.show');
Route::post('tokens', [SettingController::class, 'tokenStore'])
    ->name('api.tokens.store');
Route::delete('tokens/{token}', [SettingController::class, 'tokenDestroy'])
    ->name('api.tokens.destroy');

// TIKI Tracking (digabung ke ToolController)
Route::get('tiki/track', [ToolController::class, 'tikiTrack'])->name('api.tiki.track');

Route::get('products/search', [ProductController::class, 'search'])
    ->name('api.products.search');
Route::get('products/{product}/download-zip', [ProductController::class, 'downloadZip'])
    ->name('api.products.download_zip');
Route::get('products/{product}/move', [ProductController::class, 'move'])
    ->name('api.products.move');
Route::apiResource('products', ProductController::class)
    ->names('api.products')
    ->only(['index', 'show']);

Route::get('products/{product:code}/compare', [ProductController::class, 'compare'])
    ->name('api.products.compare');

Route::post('product-sync', [ProductController::class, 'sync'])
    ->name('api.products.sync');

Route::post('/auth/verify', [AuthController::class, 'verify'])->name('auth.verify');
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
Route::get('/auth/status', [AuthController::class, 'status'])->name('auth.status');

Route::get('/resources/logs/{file}', [SettingController::class, 'logShow'])
    ->where('file', '[A-Za-z0-9._-]+')
    ->name('api.resources.log_show');
Route::post('/resources/logs/{file}/clear', [SettingController::class, 'logClear'])
    ->where('file', '[A-Za-z0-9._-]+')
    ->name('api.resources.log_clear');
Route::delete('/resources/logs/{file}', [SettingController::class, 'logDestroy'])
    ->where('file', '[A-Za-z0-9._-]+')
    ->name('api.resources.log_destroy');
Route::delete('/resources', [SettingController::class, 'resourceDestroyLog'])
    ->name('api.resources.destroy_log');
Route::get('/resources', [SettingController::class, 'resourceIndex'])
    ->name('api.resources.index');

// Shipping Estimate API Routes
Route::delete('shipping-estimate', [ShippingEstimateController::class, 'destroyBatch'])
    ->name('api.shipping_estimate.destroy_batch');
Route::apiResource('shipping-estimate', ShippingEstimateController::class)
    ->names('api.shipping_estimate')
    ->only(['index', 'show', 'destroy']);

// Izin Edar API Routes
Route::get('izin-edars/{izinEdar}', [IzinEdarController::class, 'show'])
    ->name('api.izin_edars.show');
Route::get('izin-edars', [IzinEdarController::class, 'index'])
    ->name('api.izin_edars.index');
Route::post('izin-edars/sync', [IzinEdarController::class, 'sync'])
    ->name('api.izin_edars.sync');
Route::post('izin-edars/sync/stop', [IzinEdarController::class, 'syncStop'])
    ->name('api.izin_edars.sync_stop');
Route::get('izin-edars/sync/progress', [IzinEdarController::class, 'syncProgress'])
    ->name('api.izin_edars.sync_progress');
Route::delete('izin-edars/sync/reset', [IzinEdarController::class, 'syncReset'])
    ->name('api.izin_edars.sync_reset');
Route::get('izin-edars/sync/files', [IzinEdarController::class, 'checkFiles'])
    ->name('api.izin_edars.check_files');
Route::get('izin-edars/files/{kategori}/download', [IzinEdarController::class, 'downloadFile'])
    ->name('api.izin_edars.download_file');
Route::delete('izin-edars/files/{kategori}', [IzinEdarController::class, 'deleteFile'])
    ->name('api.izin_edars.delete_file');
Route::post('izin-edars/import-batch', [IzinEdarController::class, 'importBatch'])
    ->name('api.izin_edars.import_batch');
Route::delete('izin-edars/kategori/{kategori}', [IzinEdarController::class, 'deleteByKategori'])
    ->name('api.izin_edars.delete_by_kategori');
