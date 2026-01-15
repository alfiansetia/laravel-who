<?php

use App\Http\Controllers\AlamatController;
use App\Http\Controllers\AlamatBaruController;
use App\Http\Controllers\ItController;
use App\Http\Controllers\AtkController;
use App\Http\Controllers\BastController;
use App\Http\Controllers\DoController;
use App\Http\Controllers\FileDownloaderController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KarganController;
use App\Http\Controllers\KontakController;
use App\Http\Controllers\PackController;
use App\Http\Controllers\POController;
use App\Http\Controllers\ProblemController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\QcController;
use App\Http\Controllers\QcLotController;
use App\Http\Controllers\RIController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShippingEstimateController;
use App\Http\Controllers\SopController;
use App\Http\Controllers\SoController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;



Route::get('/home', [HomeController::class, 'index'])->name('home');


Route::get('stock', [StockController::class, 'index'])->name('stock.index');

Route::get('printso/{so}', [FileDownloaderController::class, 'download']);

Route::get('monitor-do', function () {
    $title = 'Monitor DO';
    return view('monitor.do', compact('title'));
})->name('monitor.do');

Route::get('problems/import', [ProblemController::class, 'import'])->name('problems.import');
Route::get('problems/create', [ProblemController::class, 'create'])->name('problems.create');
Route::get('problems/{problem}/edit', [ProblemController::class, 'edit'])->name('problems.edit');
Route::get('problems', [ProblemController::class, 'index'])->name('problems.index');

Route::get('po', [POController::class, 'index'])->name('po.index');
Route::get('so', [SoController::class, 'index'])->name('so.index');
Route::get('so/{id}/print', [SoController::class, 'print'])->name('so.print');
Route::get('ri', [RIController::class, 'index'])->name('ri.index');
Route::get('atk', [AtkController::class, 'index'])->name('atk.index');
Route::get('atk-import', [AtkController::class, 'import'])->name('atk.import');
Route::get('atk-eksport/{atk}', [AtkController::class, 'eksport'])->name('atk.eksport');

Route::get('do', [DoController::class, 'index'])->name('do.index');
Route::get('do/{id}/print', [DoController::class, 'print'])->name('do.print');

Route::get('it', [ItController::class, 'index'])->name('it.index');

// NEW ROUTE
Route::get('tools/stt', [ToolController::class, 'stt'])->name('tools.stt');
Route::get('tools/kalkulator', [ToolController::class, 'kalkulator'])->name('tools.kalkulator');
Route::get('tools/laporan-pengiriman', [ToolController::class, 'laporan_pengiriman'])->name('tools.laporan_pengiriman');
Route::get('tools/sn', [ToolController::class, 'index'])->name('tools.sn');
Route::get('tools/scoreboard', [ToolController::class, 'scoreboard'])->name('tools.scoreboard');
Route::get('tools/ocr', [ToolController::class, 'ocr'])->name('tools.ocr');

Route::get('/', [HomeController::class, 'index'])->name('index');


Route::resource('packs', PackController::class)
    ->names('packs')
    ->only(['index', 'show', 'create', 'edit']);
Route::get('packs/{pack}/print', [PackController::class, 'print'])
    ->name('packs.print');
Route::get('packs/{pack}/print-combined', [PackController::class, 'printCombined'])
    ->name('packs.print_combined');

Route::resource('vendors', VendorController::class)
    ->names('vendors')
    ->only(['index', 'show', 'create', 'edit']);

Route::resource('sops', SopController::class)
    ->names('sops')
    ->only(['index', 'show', 'create', 'edit']);
Route::get('sops/{sop}/print', [SopController::class, 'print'])
    ->name('sops.print');

Route::resource('alamats', AlamatController::class)
    ->names('alamats')
    ->only(['index', 'create', 'edit', 'show']);

Route::resource('alamat-baru', AlamatBaruController::class)
    ->names('alamat_baru')
    ->only(['index', 'create', 'edit', 'show']);

Route::resource('form-qc', QcController::class)
    ->names('qc')
    ->only(['index', 'show']);

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
Route::get('/product_images/{product}/collage', [ProductImageController::class, 'collage'])
    ->name('product_images.collage');

Route::get('/qc-lots', [QcLotController::class, 'index'])
    ->name('qc_lots.index');
Route::get('/qc-lots/import', [QcLotController::class, 'import'])
    ->name('qc_lots.import');

// Shipping Estimate Routes
Route::resource('shipping-estimate', ShippingEstimateController::class)
    ->names('shipping_estimate');
