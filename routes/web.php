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
Route::get('kontak', [KontakController::class, 'index'])->name('kontak.index');

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::resource('alamat', AlamatController::class);

Route::get('stock', [StockController::class, 'index'])->name('stock.index');
Route::resource('bast', BastController::class);

Route::get('printso/{so}', [FileDownloaderController::class, 'download']);
