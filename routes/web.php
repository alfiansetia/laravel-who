<?php

use App\Http\Controllers\AlamatController;
use App\Http\Controllers\BastController;
use App\Http\Controllers\FileDownloaderController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KontakController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return redirect()->route('products.index');
});

Route::get('products', [ProductController::class, 'index'])->name('products.index');

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('products/download-sample', function () {
    return response()->download(public_path('source/product.xls'));
})->name('product.download.sample');


Route::resource('alamat', AlamatController::class);

Route::delete('/kontak', [KontakController::class, 'destroy'])->name('kontak.destroy');
Route::resource('kontak', KontakController::class)->except('create', 'destroy');
Route::get('kontak/download-sample', function () {
    return response()->download(public_path('source/kontak.xls'));
})->name('kontak.download.sample');


Route::get('stock', [StockController::class, 'index'])->name('stock.index');
Route::resource('bast', BastController::class);

Route::get('printso/{so}', [FileDownloaderController::class, 'download']);
