<?php

use App\Http\Controllers\Api\AlamatController;
use App\Http\Controllers\Api\AtkController;
use App\Http\Controllers\Api\AtkTransactionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BastController;
use App\Http\Controllers\Api\DetailAlamatController;
use App\Http\Controllers\Api\DetailBastController;
use App\Http\Controllers\Api\DOController;
use App\Http\Controllers\Api\FcmTokenController;
use App\Http\Controllers\Api\KarganController;
use App\Http\Controllers\Api\KontakController;
use App\Http\Controllers\Api\PackController;
use App\Http\Controllers\Api\PackItemController;
use App\Http\Controllers\Api\POController;
use App\Http\Controllers\Api\ProblemController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductImageController;
use App\Http\Controllers\Api\QcController;
use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\Api\RIController;
use App\Http\Controllers\Api\SettingController;
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

Route::post('detail_alamat/{detail_alamat}/order', [DetailAlamatController::class, 'order'])->name('api.detail_alamat.order');
Route::apiResource('detail_alamat', DetailAlamatController::class)->names('api.detail_alamat');

Route::get('stock/{id}', [StockController::class, 'lot'])->name('api.stock.lot');
Route::get('stock', [StockController::class, 'index'])->name('api.stock.index');

Route::get('monitor-do', [DOController::class, 'monitor'])->name('api.monitor.do');

Route::apiResource('problem', ProblemController::class)->names('api.problem');

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

Route::get('/resources', [ResourceController::class, 'index'])
    ->name('api.resources.index');
