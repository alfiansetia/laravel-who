<?php

use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\LogoutController;
use App\Http\Controllers\Api\ProductController as ApiProductController;
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
