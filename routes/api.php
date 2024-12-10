<?php

use App\Http\Controllers\Api\AlamatController;
use App\Http\Controllers\Api\BastController;
use App\Http\Controllers\Api\DetailAlamatController;
use App\Http\Controllers\Api\DetailBastController;
use App\Http\Controllers\Api\DOController;
use App\Http\Controllers\Api\KontakController;
use App\Http\Controllers\Api\ProblemController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\StockController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('products', [ProductController::class, 'index'])->name('api.product.index');
Route::post('product_sync', [ProductController::class, 'sync'])->name('api.product.sync');
Route::get('kontak', [KontakController::class, 'index'])->name('api.kontak.index');
Route::post('kontak_sync', [KontakController::class, 'sync'])->name('api.kontak.sync');


Route::get('do', [DOController::class, 'index'])->name('api.do.index');
Route::get('do/{id}', [DOController::class, 'detail'])->name('api.do.detail');

Route::post('alamat/{alamat}/duplicate', [AlamatController::class, 'duplicate'])->name('api.alamat.duplicate');
Route::get('alamat/{alamat}/sync', [AlamatController::class, 'sync'])->name('api.alamat.sync');
Route::apiResource('alamat', AlamatController::class)->names('api.alamat');
Route::apiResource('detail_alamat', DetailAlamatController::class)->names('api.detail_alamat');

Route::post('setting/env', [SettingController::class, 'set_env'])->name('api.setting.env');

Route::get('stock', [StockController::class, 'index'])->name('api.stock.index');
Route::get('stock/{id}', [StockController::class, 'lot'])->name('api.stock.lot');

Route::get('bast/{bast}/sync', [BastController::class, 'sync'])->name('api.bast.sync');
Route::apiResource('bast', BastController::class)->names('api.bast');
Route::apiResource('detail_bast', DetailBastController::class)->names('api.detail_bast');

Route::get('monitor-do', [DOController::class, 'monitor'])->name('api.monitor.do');

Route::apiResource('problem', ProblemController::class)->names('api.problem');
