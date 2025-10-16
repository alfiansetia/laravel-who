<?php

use App\Http\Controllers\Api\AlamatController;
use App\Http\Controllers\Api\AtkController;
use App\Http\Controllers\Api\AtkTransactionController;
use App\Http\Controllers\Api\BastController;
use App\Http\Controllers\Api\DetailAlamatController;
use App\Http\Controllers\Api\DetailBastController;
use App\Http\Controllers\Api\DOController;
use App\Http\Controllers\Api\FcmTokenController;
use App\Http\Controllers\Api\KarganController;
use App\Http\Controllers\Api\KontakController;
use App\Http\Controllers\Api\PackController;
use App\Http\Controllers\Api\PackingListController;
use App\Http\Controllers\Api\PackItemController;
use App\Http\Controllers\Api\POController;
use App\Http\Controllers\Api\ProblemController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RIController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\SopController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\TargetController;
use App\Http\Controllers\Api\VendorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('products', ProductController::class)->names('api.product');
Route::post('product_sync', [ProductController::class, 'sync'])->name('api.product.sync');
Route::get('kontak', [KontakController::class, 'index'])->name('api.kontak.index');
Route::post('kontak_sync', [KontakController::class, 'sync'])->name('api.kontak.sync');

Route::apiResource('packing-list', PackingListController::class)->names('api.packing_list');
Route::apiResource('target', TargetController::class)->names('api.target');
Route::apiResource('kargan', KarganController::class)->names('api.kargan');


Route::get('do', [DOController::class, 'index'])->name('api.do.index');
Route::get('do/{id}', [DOController::class, 'detail'])->name('api.do.detail');

Route::post('alamat/{alamat}/duplicate', [AlamatController::class, 'duplicate'])->name('api.alamat.duplicate');
Route::get('alamat/{alamat}/sync', [AlamatController::class, 'sync'])->name('api.alamat.sync');
Route::apiResource('alamat', AlamatController::class)->names('api.alamat');

Route::post('detail_alamat/{detail_alamat}/order', [DetailAlamatController::class, 'order'])->name('api.detail_alamat.order');
Route::apiResource('detail_alamat', DetailAlamatController::class)->names('api.detail_alamat');

Route::get('setting/env', [SettingController::class, 'index'])->name('api.setting.index');
Route::post('setting/env', [SettingController::class, 'set_env'])->name('api.setting.env');
Route::post('setting/reload', [SettingController::class, 'reload'])->name('api.setting.reload');

Route::get('stock', [StockController::class, 'index'])->name('api.stock.index');
Route::get('stock/{id}', [StockController::class, 'lot'])->name('api.stock.lot');

Route::get('bast/{bast}/sync', [BastController::class, 'sync'])->name('api.bast.sync');
Route::apiResource('bast', BastController::class)->names('api.bast');
Route::apiResource('detail_bast', DetailBastController::class)->names('api.detail_bast');

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

Route::apiResource('token', FcmTokenController::class)->only(['index', 'show', 'store', 'delete']);

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
Route::post('packs-change', [PackController::class, 'change'])->name('api.packs.change');
Route::apiResource('packs', PackController::class)
    ->names('api.packs');
Route::apiResource('pack-items', PackItemController::class)
    ->names('api.pack_items');

Route::apiResource('vendors', VendorController::class)
    ->names('api.vendors');

Route::apiResource('sops', SopController::class)
    ->names('api.sops');
