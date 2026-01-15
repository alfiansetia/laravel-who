<?php

use App\Http\Controllers\Api\AlamatController;
use App\Http\Controllers\Api\AlamatBaruController;
use App\Http\Controllers\Api\AtkController;
use App\Http\Controllers\Api\AtkTransactionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BastController;
use App\Http\Controllers\Api\DetailAlamatController;
use App\Http\Controllers\Api\DetailBastController;
use App\Http\Controllers\Api\DOController;
use App\Http\Controllers\Api\FcmTokenController;
use App\Http\Controllers\Api\ItController;
use App\Http\Controllers\Api\KarganController;
use App\Http\Controllers\Api\KoliController;
use App\Http\Controllers\Api\KoliItemController;
use App\Http\Controllers\Api\KontakController;
use App\Http\Controllers\Api\PackController;
use App\Http\Controllers\Api\PackItemController;
use App\Http\Controllers\Api\POController;
use App\Http\Controllers\Api\ProblemController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductImageController;
use App\Http\Controllers\Api\QcController;
use App\Http\Controllers\Api\QcLotController;
use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\Api\RIController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\SoController;
use App\Http\Controllers\Api\SopController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\VendorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('do', [DOController::class, 'index'])->name('api.do.index');
Route::get('do/{id}', [DOController::class, 'detail'])->name('api.do.detail');

Route::post('so/{id}/mark-as-print', [SoController::class, 'mark_as_print'])->name('api.so.mark_as_print');
Route::post('so/{id}/mark-as-unprint', [SoController::class, 'mark_as_unprint'])->name('api.so.mark_as_unprint');
Route::get('so', [SoController::class, 'index'])->name('api.so.index');
Route::get('so/{id}', [SOController::class, 'detail'])->name('api.so.detail');

Route::get('it', [ItController::class, 'index'])->name('api.it.index');
Route::get('it/{id}', [ItController::class, 'detail'])->name('api.it.detail');

Route::post('detail_alamat/{detail_alamat}/order', [DetailAlamatController::class, 'order'])->name('api.detail_alamat.order');
Route::apiResource('detail_alamat', DetailAlamatController::class)->names('api.detail_alamat');

Route::get('stock/{id}', [StockController::class, 'lot'])->name('api.stock.lot');
Route::get('stock', [StockController::class, 'index'])->name('api.stock.index');

Route::get('monitor-do', [DOController::class, 'monitor'])->name('api.monitor.do');

Route::apiResource('problem', ProblemController::class)->names('api.problem');

// Problem Item Routes
Route::apiResource('problem-item', \App\Http\Controllers\Api\ProblemItemController::class)
    ->names('api.problem_item')
    ->only(['store', 'show', 'update', 'destroy']);

// Problem Log Routes
Route::apiResource('problem-log', \App\Http\Controllers\Api\ProblemLogController::class)
    ->names('api.problem_log')
    ->only(['store', 'show', 'update', 'destroy']);

Route::get('po', [POController::class, 'index'])->name('api.po.index');
Route::get('po/order-line', [POController::class, 'order_line'])->name('api.po.order_line');
Route::get('po/{id}', [POController::class, 'detail'])->name('api.po.detail');

Route::get('ri', [RIController::class, 'index'])->name('api.ri.index');
Route::get('ri/order-line', [RIController::class, 'order_line'])->name('api.ri.order_line');
Route::get('ri/{id}', [RIController::class, 'detail'])->name('api.ri.detail');

Route::apiResource('atk', AtkController::class)->names('api.atk');
Route::post('atk-import', [AtkController::class, 'import'])->name('api.atk.import');
Route::apiResource('atk-trx', AtkTransactionController::class)->names('api.atktrx');


Route::get('firebase-config', function () {
    return response()->json([
        'apiKey'            => config('services.firebase.api_key'),
        'authDomain'        => config('services.firebase.auth_domain'),
        'projectId'         => config('services.firebase.project_id'),
        'storageBucket'     => config('services.firebase.storage_bucket'),
        'messagingSenderId' => config('services.firebase.messaging_sender_id'),
        'appId'             => config('services.firebase.app_id'),
        'measurementId'     => config('services.firebase.measurement_id'),
    ]);
});

// NEW ROUTE
Route::get('packs/{pack}/download', [PackController::class, 'download'])
    ->name('api.packs.download');
Route::post('packs-change', [PackController::class, 'change'])
    ->name('api.packs.change');
Route::apiResource('packs', PackController::class)
    ->names('api.packs');

Route::apiResource('pack-items', PackItemController::class)
    ->names('api.pack_items');

Route::delete('vendors', [VendorController::class, 'destroy_batch'])
    ->name('api.vendors.destroy_batch');
Route::apiResource('vendors', VendorController::class)
    ->names('api.vendors');

Route::get('sops/{sop}/download', [SopController::class, 'download'])
    ->name('api.sops.download');
Route::apiResource('sops', SopController::class)
    ->names('api.sops');

Route::post('alamats/{alamat}/duplicate', [AlamatController::class, 'duplicate'])
    ->name('api.alamats.duplicate');
Route::get('alamats/{alamat}/sync', [AlamatController::class, 'sync'])
    ->name('api.alamats.sync');
Route::delete('alamats', [AlamatController::class, 'destroy_batch'])
    ->name('api.alamats.delete_batch');
Route::apiResource('alamats', AlamatController::class)
    ->names('api.alamats');

// Alamat Baru Routes
Route::post('alamat-baru/{alamatBaru}/duplicate', [AlamatBaruController::class, 'duplicate'])
    ->name('api.alamat_baru.duplicate');
Route::get('alamat-baru/{alamatBaru}/sync', [AlamatBaruController::class, 'sync'])
    ->name('api.alamat_baru.sync');
Route::delete('alamat-baru', [AlamatBaruController::class, 'destroy_batch'])
    ->name('api.alamat_baru.delete_batch');
Route::apiResource('alamat-baru', AlamatBaruController::class)
    ->names('api.alamat_baru');

// Koli Routes
Route::get('koli/{koli}/sync', [KoliController::class, 'sync'])
    ->name('api.koli.sync');
Route::post('koli/{koli}/duplicate', [KoliController::class, 'duplicate'])
    ->name('api.koli.duplicate');
Route::post('koli/{koli}/order', [KoliController::class, 'order'])
    ->name('api.koli.order');
Route::apiResource('koli', KoliController::class)
    ->names('api.koli');

// Koli Item Routes
Route::post('koli-item/{koliItem}/order', [KoliItemController::class, 'order'])
    ->name('api.koli_item.order');
Route::post('koli-item/{koliItem}/clear-lot', [KoliItemController::class, 'clearLot'])
    ->name('api.koli_item.clear_lot');
Route::apiResource('koli-item', KoliItemController::class)
    ->names('api.koli_item');

Route::post('form-qc/', [QcController::class, 'store'])
    ->name('api.qc.store');

Route::get('settings/', [SettingController::class, 'index'])
    ->name('api.settings.index');
Route::post('settings/', [SettingController::class, 'store'])
    ->name('api.settings.store');
Route::put('settings/', [SettingController::class, 'reload'])
    ->name('api.settings.reload');
Route::delete('settings/', [SettingController::class, 'test_notif'])
    ->name('api.settings.test_notif');

Route::apiResource('tokens', FcmTokenController::class)
    ->names('api.tokens')
    ->only(['index', 'show', 'store', 'delete']);


Route::get('products/{product}/move', [ProductController::class, 'move'])
    ->name('api.products.move');
Route::apiResource('products', ProductController::class)
    ->names('api.products')
    ->only(['index', 'show']);

Route::post('product-sync', [ProductController::class, 'sync'])
    ->name('api.products.sync');

Route::post('kargans/{kargan}/duplicate', [KarganController::class, 'duplicate'])
    ->name('api.kargans.duplicate');
Route::get('kargans/{kargan}/download', [KarganController::class, 'download'])
    ->name('api.kargans.download');
Route::delete('kargans', [KarganController::class, 'destroy_batch'])
    ->name('api.kargans.destroy_batch');
Route::apiResource('kargans', KarganController::class)
    ->names('api.kargans');

Route::apiResource('kontaks', KontakController::class)
    ->names('api.kontaks')
    ->only(['index', 'store']);

Route::delete('basts', [BastController::class, 'destroy_batch'])
    ->name('api.basts.destroy_batch');
Route::get('basts/{bast}/sync', [BastController::class, 'sync'])
    ->name('api.basts.sync');
Route::get('basts/{bast}/download', [BastController::class, 'download'])
    ->name('api.basts.download');
Route::apiResource('basts', BastController::class)
    ->names('api.basts');

Route::post('detail-basts/{detail_bast}/order', [DetailBastController::class, 'order'])
    ->name('api.detail_basts.order');
Route::apiResource('detail-basts', DetailBastController::class)
    ->names('api.detail_basts');

Route::post('/auth/verify', [AuthController::class, 'verify'])->name('auth.verify');
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
Route::get('/auth/status', [AuthController::class, 'status'])->name('auth.status');

Route::delete('product_images', [ProductImageController::class, 'destroy_batch'])
    ->name('api.product_images.destroy_batch');
Route::apiResource('product_images', ProductImageController::class)
    ->names('api.product_images')
    ->only(['index', 'show', 'store', 'destroy']);


Route::delete('/resources', [ResourceController::class, 'destroy_log'])
    ->name('api.resources.destroy_log');
Route::get('/resources', [ResourceController::class, 'index'])
    ->name('api.resources.index');

Route::delete('qc-lots', [QcLotController::class, 'destroy_batch'])
    ->name('api.qc_lots.destroy_batch');
Route::apiResource('qc-lots', QcLotController::class)
    ->names('api.qc_lots');
Route::post('qc-lots/import', [QcLotController::class, 'import'])
    ->name('api.qc_lots.import');

// Shipping Estimate API Routes
Route::delete('shipping-estimate', [\App\Http\Controllers\Api\ShippingEstimateController::class, 'destroyBatch'])
    ->name('api.shipping_estimate.destroy_batch');
Route::apiResource('shipping-estimate', \App\Http\Controllers\Api\ShippingEstimateController::class)
    ->names('api.shipping_estimate')
    ->only(['index', 'show', 'destroy']);
