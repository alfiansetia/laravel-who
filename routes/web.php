<?php

use App\Http\Controllers\AlamatController;
use App\Http\Controllers\AtkController;
use App\Http\Controllers\BastController;
use App\Http\Controllers\FileDownloaderController;
use App\Http\Controllers\FormqcController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KarganController;
use App\Http\Controllers\KontakController;
use App\Http\Controllers\PackController;
use App\Http\Controllers\PackingListController;
use App\Http\Controllers\POController;
use App\Http\Controllers\ProblemController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RIController;
use App\Http\Controllers\SopController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\TargetController;
use App\Http\Controllers\ToolsController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return redirect()->route('products.index');
})->name('index');

Route::get('tools-sn', [ToolsController::class, 'sn'])->name('tools.sn');

Route::get('products', [ProductController::class, 'index'])->name('products.index');
Route::get('kontak', [KontakController::class, 'index'])->name('kontak.index');

Route::resource('packing-list', PackingListController::class)->names('pl');
Route::resource('target', TargetController::class)->names('target');
Route::resource('kargan', KarganController::class);

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::resource('alamat', AlamatController::class);

Route::get('stock', [StockController::class, 'index'])->name('stock.index');
Route::resource('bast', BastController::class);

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

Route::get('form-qc/', [FormqcController::class, 'create'])->name('qc.create');
Route::post('form-qc/', [FormqcController::class, 'store'])->name('qc.store');

Route::get('tes', function () {
    return view('tes');
});

// NEW ROUTE
Route::resource('packs', PackController::class)
    ->names('packs')
    ->only(['index', 'show', 'create', 'edit']);

Route::resource('vendors', VendorController::class)
    ->names('vendors')
    ->only(['index', 'show', 'create', 'edit']);

Route::resource('sops', SopController::class)
    ->names('sops')
    ->only(['index', 'show', 'create']);
