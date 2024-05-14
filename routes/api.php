<?php

use App\Http\Controllers\Api\AlamatController;
use App\Http\Controllers\Api\DetailAlamatController;
use App\Http\Controllers\Api\DOController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\LogoutController;
use App\Http\Controllers\Api\ProductController as ApiProductController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/tes', [ProductController::class, 'tes']);
Route::get('/product', [ProductController::class, 'getdata'])->name('product.getdata');

Route::post('import', [ProductController::class, 'import']);

// Route::group([

//     'middleware' => 'api',

// ], function ($router) {

//     Route::post('login', [LoginController::class, 'login']);
//     Route::post('logout', [LogoutController::class, 'logout']);
//     // Route::post('refresh', [AuthController::class, 'refresh']);
//     // Route::post('me', [AuthController::class, 'me']);
// });
// Route::post('/register', App\Http\Controllers\Api\RegisterController::class)->name('register');
Route::middleware(['jwt.auth'])->group(function () {
    Route::get('/products', [ApiProductController::class, 'index']);
    Route::post('me', [AuthController::class, 'me']);
});

// Route::get('/products', [ApiProductController::class, 'index'])->middleware('api');

Route::post('/login', LoginController::class)->name('api.login');
Route::post('/logout', LogoutController::class)->name('api.logout');

// Route::get('alamat/get', [AlamatController::class, 'get'])->name('api.do.list');
// Route::get('alamat/get_detail', [AlamatController::class, 'get_detail'])->name('api.do.detail');

// Route::post('alamat_detail/{id}', [AlamatController::class, 'edit_detail']);
// Route::get('alamat_detail_ist/{id}', [AlamatController::class, 'detail'])->name('api.alamat.list.detail');
// Route::post('detail_alamat_store/{id}', [DetailAlamatController::class, 'store'])->name('api.alamat.store.detail');
// Route::post('detail_alamat_delete/{id}', [DetailAlamatController::class, 'destroy'])->name('api.alamat.destroy.detail');

Route::get('do', [DOController::class, 'index'])->name('api.do.index');
Route::get('do/{id}', [DOController::class, 'detail'])->name('api.do.detail');
Route::get('do/{alamat}/sync', [DOController::class, 'sync'])->name('api.do.detail.sync');

Route::apiResource('alamat', AlamatController::class)->names('api.alamat');
Route::apiResource('detail_alamat', DetailAlamatController::class)->names('api.detail_alamat');

Route::post('setting/env', [SettingController::class, 'set_env'])->name('api.setting.env');
Route::post('product_sync', [ApiProductController::class, 'sync'])->name('api.product_sync');

Route::get('stock', [StockController::class, 'index'])->name('api.stock.index');
