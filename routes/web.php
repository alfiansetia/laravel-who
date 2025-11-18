<?php

use App\Http\Controllers\AlamatController;
use App\Http\Controllers\AtkController;
use App\Http\Controllers\BastController;
use App\Http\Controllers\FileDownloaderController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KarganController;
use App\Http\Controllers\KontakController;
use App\Http\Controllers\LaporanPengirimanController;
use App\Http\Controllers\PackController;
use App\Http\Controllers\POController;
use App\Http\Controllers\ProblemController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\QcController;
use App\Http\Controllers\RIController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SnController;
use App\Http\Controllers\SopController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;



Route::get('/home', [HomeController::class, 'index'])->name('home');


Route::get('stock', [StockController::class, 'index'])->name('stock.index');

Route::get('printso/{so}', [FileDownloaderController::class, 'download']);

Route::get('monitor-do', function () {
    $title = 'Monitor DO';
    return view('monitor.do', compact('title'));
})->name('monitor.do');


Route::resource('problem', ProblemController::class);

Route::get('po', [POController::class, 'index'])->name('po.index');
Route::get('ri', [RIController::class, 'index'])->name('ri.index');
Route::get('atk', [AtkController::class, 'index'])->name('atk.index');
Route::get('atk-import', [AtkController::class, 'import'])->name('atk.import');
Route::get('atk-eksport/{atk}', [AtkController::class, 'eksport'])->name('atk.eksport');


// NEW ROUTE
Route::get('/', [HomeController::class, 'index'])->name('index');


Route::resource('packs', PackController::class)
    ->names('packs')
    ->only(['index', 'show', 'create', 'edit']);

Route::resource('vendors', VendorController::class)
    ->names('vendors')
    ->only(['index', 'show', 'create', 'edit']);

Route::resource('sops', SopController::class)
    ->names('sops')
    ->only(['index', 'show', 'create']);

Route::resource('alamats', AlamatController::class)
    ->names('alamats')
    ->only(['index', 'create', 'edit', 'show']);

Route::resource('form-qc', QcController::class)
    ->names('qc')
    ->only(['index', 'show']);

Route::get('sn/', [SnController::class, 'index'])
    ->name('sn.index');

Route::get('laporan-pengiriman/', [LaporanPengirimanController::class, 'index'])
    ->name('laporan_pengiriman.index');

Route::get('settings/', [SettingController::class, 'index'])
    ->name('settings.index');

Route::resource('kargans', KarganController::class)
    ->only(['index', 'edit', 'create'])
    ->names('kargans');

Route::get('products', [ProductController::class, 'index'])
    ->name('products.index');

Route::get('kontaks', [KontakController::class, 'index'])
    ->name('kontaks.index');

Route::resource('basts', BastController::class)
    ->names('basts')
    ->only(['index', 'create', 'edit']);

Route::get('/product_images', [ProductImageController::class, 'index'])
    ->name('product_images.index');
