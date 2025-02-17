<?php

use App\Http\Controllers\AlamatController;
use App\Http\Controllers\AtkController;
use App\Http\Controllers\BastController;
use App\Http\Controllers\FileDownloaderController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KontakController;
use App\Http\Controllers\POController;
use App\Http\Controllers\ProblemController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RIController;
use App\Http\Controllers\StockController;
use App\Services\DoMonitorService;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return redirect()->route('products.index');
})->name('index');

Route::get('products', [ProductController::class, 'index'])->name('products.index');
Route::get('kontak', [KontakController::class, 'index'])->name('kontak.index');

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

Route::get('tes', function () {
    return view('tes');
});
