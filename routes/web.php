<?php

use App\Http\Controllers\AlamatController;
use App\Http\Controllers\BastController;
use App\Http\Controllers\FileDownloaderController;
use App\Http\Controllers\KontakController;
use App\Http\Controllers\ProblemController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Auth::routes(['verify' => true]);
Route::get('/', function () {
    return redirect()->route('product.index');
});
// Route::get('/', function () {
//     return view('welcome');
// })->name('home');

// Route::post('/cart/change', [CartController::class, 'change'])->name('cart.change');
Route::delete('/product', [ProductController::class, 'destroy'])->name('product.destroy');
Route::resource('product', ProductController::class)->except('create', 'show', 'destroy');


// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

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
