<?php

use App\Http\Controllers\AklController;
use App\Http\Controllers\AlamatBaruController;
use App\Http\Controllers\AlamatController;
use App\Http\Controllers\AtkController;
use App\Http\Controllers\BastController;
use App\Http\Controllers\DoController;
use App\Http\Controllers\FileDownloaderController;
use App\Http\Controllers\FileSearchController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ItController;
use App\Http\Controllers\IzinEdarController;
use App\Http\Controllers\KarganController;
use App\Http\Controllers\KontakController;
use App\Http\Controllers\LotController;
use App\Http\Controllers\PackController;
use App\Http\Controllers\POController;
use App\Http\Controllers\ProblemController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ProductOdooController;
use App\Http\Controllers\QcController;
use App\Http\Controllers\QcLotController;
use App\Http\Controllers\RIController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShippingEstimateController;
use App\Http\Controllers\SoController;
use App\Http\Controllers\SopController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('lots', [LotController::class, 'index'])->name('lots.index');
Route::get('stock', [StockController::class, 'index'])->name('stock.index');

Route::get('product-odoo', [ProductOdooController::class, 'index'])->name('product_odoo.index');

Route::get('printso/{so}', [FileDownloaderController::class, 'download']);

Route::get('monitor-do', function () {
    return Inertia::render('MonitorDo/Index', [
        'title' => 'Monitor DO',
    ]);
})->name('monitor.do');

// NOTE: `problems/*` (plural, Blade pages) vs `problem/*` (singular, JSON)
// sengaja beda path agar kontrak Blade lama + API tidak tabrakan.
// Jangan digabung tanpa migrasi Blade + PWA (lihat .ai/backend-conventions.md §5).
Route::get('problems', [ProblemController::class, 'index'])->name('problems.index');
Route::get('problems/import', [ProblemController::class, 'import'])->name('problems.import');
Route::get('problems/create', [ProblemController::class, 'create'])->name('problems.create');
Route::get('problems/{problem}/edit', [ProblemController::class, 'edit'])->name('problems.edit')->whereNumber('problem');
Route::get('problems/{problem}', [ProblemController::class, 'show'])->name('problems.show')->whereNumber('problem');

// Problem JSON (singular, single controller: ProblemController)
Route::get('problem', [ProblemController::class, 'index'])->name('problem.index');
Route::post('problem', [ProblemController::class, 'store'])->name('problem.store');
Route::get('problem/next-number', [ProblemController::class, 'nextNumber'])->name('problem.next_number');
Route::get('problem/{problem}', [ProblemController::class, 'show'])->name('problem.show');
Route::match(['put', 'patch'], 'problem/{problem}', [ProblemController::class, 'update'])->name('problem.update');
Route::delete('problem/{problem}', [ProblemController::class, 'destroy'])->name('problem.destroy');
Route::post('problem/{problem}/duplicate', [ProblemController::class, 'duplicate'])->name('problem.duplicate');
Route::post('problem/{problem}/status', [ProblemController::class, 'updateStatus'])->name('problem.status');

// Problem Item / Log (single controller: ProblemController)
Route::post('problem-item', [ProblemController::class, 'problemItemStore'])->name('problem_item.store');
Route::get('problem-item/{problemItem}', [ProblemController::class, 'problemItemShow'])->name('problem_item.show');
Route::match(['put', 'patch'], 'problem-item/{problemItem}', [ProblemController::class, 'problemItemUpdate'])->name('problem_item.update');
Route::delete('problem-item/{problemItem}', [ProblemController::class, 'problemItemDestroy'])->name('problem_item.destroy');
Route::post('problem-log', [ProblemController::class, 'problemLogStore'])->name('problem_log.store');
Route::get('problem-log/{problemLog}', [ProblemController::class, 'problemLogShow'])->name('problem_log.show');
Route::match(['put', 'patch'], 'problem-log/{problemLog}', [ProblemController::class, 'problemLogUpdate'])->name('problem_log.update');
Route::delete('problem-log/{problemLog}', [ProblemController::class, 'problemLogDestroy'])->name('problem_log.destroy');

Route::get('po', [POController::class, 'index'])->name('po.index');
Route::get('so', [SoController::class, 'index'])->name('so.index');
Route::get('so/{id}/print', [SoController::class, 'print'])->name('so.print')->whereNumber('id');
Route::get('ri', [RIController::class, 'index'])->name('ri.index');
Route::get('atk', [AtkController::class, 'index'])->name('atk.index');
Route::post('atk', [AtkController::class, 'store'])->name('atk.store');
Route::get('atk/{atk}', [AtkController::class, 'show'])->name('atk.show');
Route::match(['put', 'patch'], 'atk/{atk}', [AtkController::class, 'update'])->name('atk.update');
Route::delete('atk/{atk}', [AtkController::class, 'destroy'])->name('atk.destroy');
Route::delete('atk', [AtkController::class, 'destroy_batch'])->name('atk.destroy_batch');
Route::get('atk-import', [AtkController::class, 'import'])->name('atk.import');
Route::post('atk-import', [AtkController::class, 'import'])->name('atk.import_store');
Route::get('atk-eksport/{atk}', [AtkController::class, 'eksport'])->name('atk.eksport');

// ATK Transaction (single controller: AtkController)
Route::get('atk-trx', [AtkController::class, 'trxIndex'])->name('atk_trx.index');
Route::post('atk-trx', [AtkController::class, 'trxStore'])->name('atk_trx.store');
Route::get('atk-trx/{atk_trx}', [AtkController::class, 'trxShow'])->name('atk_trx.show');
Route::match(['put', 'patch'], 'atk-trx/{atk_trx}', [AtkController::class, 'trxUpdate'])->name('atk_trx.update');
Route::delete('atk-trx/{atk_trx}', [AtkController::class, 'trxDestroy'])->name('atk_trx.destroy');

Route::get('do', [DoController::class, 'index'])->name('do.index');
Route::get('do/{id}/print', [DoController::class, 'print'])->name('do.print')->whereNumber('id');

Route::get('it', [ItController::class, 'index'])->name('it.index');
Route::get('it/{id}/print', [ItController::class, 'print'])->name('it.print')->whereNumber('id');

// NEW ROUTE
Route::get('tools/stt', [ToolController::class, 'stt'])->name('tools.stt');
Route::get('tools/kalkulator', [ToolController::class, 'kalkulator'])->name('tools.kalkulator');
Route::get('tools/laporan-pengiriman', [ToolController::class, 'laporan_pengiriman'])->name('tools.laporan_pengiriman');
Route::get('tools/laporan-luarkota', [ToolController::class, 'laporan_luarkota'])->name('tools.laporan_luarkota');
Route::get('tools/sn', [ToolController::class, 'index'])->name('tools.sn');
Route::get('tools/scoreboard', [ToolController::class, 'scoreboard'])->name('tools.scoreboard');
Route::get('tools/ocr', [ToolController::class, 'ocr'])->name('tools.ocr');
Route::get('tools/spreadsheet', [ToolController::class, 'spreadsheet'])->name('tools.spreadsheet');

Route::get('tools/file-search', [FileSearchController::class, 'index'])->name('tools.file_search');
Route::get('tools/file-search/data', [FileSearchController::class, 'getData'])->name('tools.file_search.data');
Route::get('tools/file-search/download-script', [FileSearchController::class, 'downloadScript'])->name('tools.file_search.download_script');
Route::post('tools/file-search/upload', [FileSearchController::class, 'upload'])->name('tools.file_search.upload');

Route::get('tools/print-resi', [ToolController::class, 'print_resi'])->name('tools.print_resi');

Route::get('/', [HomeController::class, 'index'])->name('index');

Route::get('packs/export', [PackController::class, 'export'])
    ->name('packs.export');
Route::get('packs/{pack}/download', [PackController::class, 'download'])
    ->name('packs.download');
Route::post('packs-change', [PackController::class, 'change'])
    ->name('packs.change');
Route::get('pack-items', [PackController::class, 'packItemIndex'])
    ->name('packs.items.index');
Route::delete('packs', [PackController::class, 'destroy_batch'])
    ->name('packs.destroy_batch');
Route::resource('packs', PackController::class)
    ->names('packs')
    ->only(['index', 'show', 'create', 'edit', 'store', 'update', 'destroy']);
Route::get('packs/{pack}/print', [PackController::class, 'print'])
    ->name('packs.print');
Route::get('packs/{pack}/print-combined', [PackController::class, 'printCombined'])
    ->name('packs.print_combined');

Route::delete('vendors', [VendorController::class, 'destroy_batch'])
    ->name('vendors.destroy_batch');
Route::resource('vendors', VendorController::class)
    ->names('vendors')
    ->only(['index', 'store', 'show', 'update', 'destroy']);

Route::get('sops/export', [SopController::class, 'export'])
    ->name('sops.export');
Route::get('sops/{sop}/download', [SopController::class, 'download'])
    ->name('sops.download');
Route::resource('sops', SopController::class)
    ->names('sops')
    ->only(['index', 'show', 'create', 'edit', 'store']);
Route::get('sops/{sop}/print', [SopController::class, 'print'])
    ->name('sops.print');

Route::delete('alamats', [AlamatController::class, 'destroy_batch'])
    ->name('alamats.destroy_batch');
Route::resource('alamats', AlamatController::class)
    ->names('alamats')
    ->only(['index', 'create', 'edit', 'show', 'store', 'update', 'destroy']);

Route::delete('alamat-baru', [AlamatBaruController::class, 'destroy_batch'])
    ->name('alamat_baru.destroy_batch');
Route::post('alamat-baru/{alamatBaru}/duplicate', [AlamatBaruController::class, 'duplicate'])
    ->name('alamat_baru.duplicate');
Route::post('alamat-baru/{alamatBaru}/bast', [AlamatBaruController::class, 'bast'])
    ->name('alamat_baru.bast');
Route::resource('alamat-baru', AlamatBaruController::class)
    ->names('alamat_baru')
    ->only(['index', 'create', 'edit', 'show', 'store', 'update', 'destroy']);

// Koli (single controller: AlamatBaruController)
Route::get('koli', [AlamatBaruController::class, 'koliIndex'])->name('koli.index');
Route::post('koli', [AlamatBaruController::class, 'koliStore'])->name('koli.store');
Route::get('koli/{koli}', [AlamatBaruController::class, 'koliShow'])->name('koli.show')->whereNumber('koli');
Route::match(['put', 'patch'], 'koli/{koli}', [AlamatBaruController::class, 'koliUpdate'])->name('koli.update')->whereNumber('koli');
Route::delete('koli/{koli}', [AlamatBaruController::class, 'koliDestroy'])->name('koli.destroy')->whereNumber('koli');
Route::get('koli/{koli}/sync', [AlamatBaruController::class, 'koliSync'])->name('koli.sync')->whereNumber('koli');
Route::post('koli/{koli}/duplicate', [AlamatBaruController::class, 'koliDuplicate'])->name('koli.duplicate')->whereNumber('koli');
Route::post('koli/{koli}/hitung', [AlamatBaruController::class, 'koliHitung'])->name('koli.hitung')->whereNumber('koli');

// Koli Item (single controller: AlamatBaruController)
Route::get('koli-item', [AlamatBaruController::class, 'koliItemIndex'])->name('koli_item.index');
Route::post('koli-item', [AlamatBaruController::class, 'koliItemStore'])->name('koli_item.store');
Route::post('koli-item/from-do-it', [AlamatBaruController::class, 'koliItemFromDoIt'])->name('koli_item.from_do_it');
Route::get('koli-item/{koliItem}', [AlamatBaruController::class, 'koliItemShow'])->name('koli_item.show');
Route::match(['put', 'patch'], 'koli-item/{koliItem}', [AlamatBaruController::class, 'koliItemUpdate'])->name('koli_item.update');
Route::delete('koli-item/{koliItem}', [AlamatBaruController::class, 'koliItemDestroy'])->name('koli_item.destroy');
Route::post('koli-item/{koliItem}/order', [AlamatBaruController::class, 'koliItemOrder'])->name('koli_item.order');
Route::post('koli-item/{koliItem}/clear-lot', [AlamatBaruController::class, 'koliItemClearLot'])->name('koli_item.clear_lot');

Route::resource('form-qc', QcController::class)
    ->names('qc')
    ->only(['index', 'show']);

Route::get('settings/', [SettingController::class, 'index'])
    ->name('settings.index');

Route::delete('kargans', [KarganController::class, 'destroy_batch'])
    ->name('kargans.destroy_batch');
Route::post('kargans/{kargan}/duplicate', [KarganController::class, 'duplicate'])
    ->name('kargans.duplicate');
Route::get('kargans/{kargan}/download', [KarganController::class, 'download'])
    ->name('kargans.download');
Route::resource('kargans', KarganController::class)
    ->only(['index', 'edit', 'create', 'store', 'show', 'update', 'destroy'])
    ->names('kargans');

Route::get('products', [ProductController::class, 'index'])
    ->name('products.index');

Route::get('kontaks', [KontakController::class, 'index'])
    ->name('kontaks.index');
Route::post('kontaks', [KontakController::class, 'store'])
    ->name('kontaks.store');

Route::get('basts/{bast}/print', [BastController::class, 'print'])
    ->name('basts.print');
Route::delete('basts', [BastController::class, 'destroy_batch'])
    ->name('basts.destroy_batch');
Route::get('basts/{bast}/sync', [BastController::class, 'sync'])
    ->name('basts.sync');
Route::get('basts/{bast}/download', [BastController::class, 'download'])
    ->name('basts.download');
Route::get('basts/{bast}/download-zip', [BastController::class, 'downloadZip'])
    ->name('basts.download_zip');
Route::resource('basts', BastController::class)
    ->names('basts')
    ->only(['index', 'create', 'edit', 'store', 'show', 'update', 'destroy']);

// Detail BAST (single controller: BastController)
Route::get('detail-basts', [BastController::class, 'detailIndex'])->name('basts.detail.index');
Route::post('detail-basts', [BastController::class, 'detailStore'])->name('basts.detail.store');
Route::get('detail-basts/{detail_bast}', [BastController::class, 'detailShow'])->name('basts.detail.show');
Route::match(['put', 'patch'], 'detail-basts/{detail_bast}', [BastController::class, 'detailUpdate'])->name('basts.detail.update');
Route::delete('detail-basts/{detail_bast}', [BastController::class, 'detailDestroy'])->name('basts.detail.destroy');
Route::post('detail-basts/{detail_bast}/order', [BastController::class, 'detailOrder'])->name('basts.detail.order');
Route::post('detail-basts/{detail_bast}/kargan', [BastController::class, 'detailKargan'])->name('basts.detail.kargan');

Route::get('/product_images', [ProductImageController::class, 'index'])
    ->name('product_images.index');
Route::post('/product_images', [ProductImageController::class, 'store'])
    ->name('product_images.store');
Route::delete('/product_images/{product_image}', [ProductImageController::class, 'destroy'])
    ->name('product_images.destroy');
Route::delete('/product_images-batch', [ProductImageController::class, 'destroy_batch'])
    ->name('product_images.destroy_batch');
Route::get('/product_images/{product}/collage', [ProductImageController::class, 'collage'])
    ->name('product_images.collage');
Route::get('/product_images/{product}/download', [ProductImageController::class, 'download'])
    ->name('product_images.download');
Route::get('/product_images/{product_image}', [ProductImageController::class, 'show'])
    ->name('product_images.show');

Route::get('/qc-lots', [QcLotController::class, 'index'])
    ->name('qc_lots.index');
Route::post('/qc-lots', [QcLotController::class, 'store'])
    ->name('qc_lots.store');
Route::get('/qc-lots/import', [QcLotController::class, 'import'])
    ->name('qc_lots.import');
Route::post('/qc-lots/import', [QcLotController::class, 'import'])
    ->name('qc_lots.import_store');
Route::delete('/qc-lots', [QcLotController::class, 'destroy_batch'])
    ->name('qc_lots.destroy_batch');
Route::get('/qc-lots/{qc_lot}', [QcLotController::class, 'show'])
    ->name('qc_lots.show');
Route::match(['put', 'patch'], '/qc-lots/{qc_lot}', [QcLotController::class, 'update'])
    ->name('qc_lots.update');
Route::delete('/qc-lots/{qc_lot}', [QcLotController::class, 'destroy'])
    ->name('qc_lots.destroy');

// Form QC (single controller: QcController)
Route::post('form-qc', [QcController::class, 'store'])
    ->name('qc.store');

// Izin Edar Routes
Route::get('izin-edars', [IzinEdarController::class, 'index'])
    ->name('izin_edars.index');

// AKL Routes (lampiran image/PDF di S3, reg_no boleh sama untuk perpanjangan)
Route::get('akls/{akl}/items', [AklController::class, 'items'])
    ->name('akls.items');
Route::delete('akls', [AklController::class, 'destroyBatch'])
    ->name('akls.destroy_batch');
Route::post('akls/copy-from-izin', [AklController::class, 'copyFromIzin'])
    ->name('akls.copy_from_izin');
Route::get('akls/{akl}/check-izin', [AklController::class, 'checkIzin'])
    ->name('akls.check_izin');
Route::put('akls/{akl}/apply-izin', [AklController::class, 'applyIzin'])
    ->name('akls.apply_izin');
Route::resource('akls', AklController::class)
    ->names('akls')
    ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

// AKL Item (single controller: AklController)
Route::get('akl-items', [AklController::class, 'aklItemIndex'])->name('akl_items.index');
Route::post('akl-items', [AklController::class, 'aklItemStore'])->name('akl_items.store');
Route::delete('akl-items', [AklController::class, 'aklItemDestroyBatch'])->name('akl_items.destroy_batch');
Route::get('akl-items/{id}/check-product', [AklController::class, 'aklItemCheckProduct'])->name('akl_items.check_product');
Route::put('akl-items/{id}/apply-product', [AklController::class, 'aklItemApplyProduct'])->name('akl_items.apply_product');
Route::delete('akl-items/{akl_item}', [AklController::class, 'aklItemDestroy'])->name('akl_items.destroy');

// Shipping Estimate Routes
Route::resource('shipping-estimate', ShippingEstimateController::class)
    ->names('shipping_estimate');

// Service Worker gabungan (offline + FCM background). Satu-satunya SW scope root.
// WAJIB lewat route: nginx serve file statis lebih dulu, jadi public/sw.js dihapus.
Route::get('/sw.js', function () {
    $content = view('sw')->render();

    return response($content, 200)
        ->header('Content-Type', 'application/javascript')
        ->header('Cache-Control', 'no-cache');
})->name('sw');

// Transisi: rute lama dipertahankan untuk klien yang masih meregistrasi versi 1.
Route::get('/firebase-messaging-sw.js', function () {
    $content = view('firebase-messaging-sw')->render();

    return response($content, 200)
        ->header('Content-Type', 'application/javascript')
        ->header('Cache-Control', 'no-store, no-cache');
});
