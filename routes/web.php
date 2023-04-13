<?php

use App\Http\Controllers\ProblemController;
use App\Http\Controllers\ProductController;
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
    return redirect()->route('login');
});
// Route::get('/', function () {
//     return view('welcome');
// })->name('home');

// Route::post('/cart/change', [CartController::class, 'change'])->name('cart.change');
Route::delete('/product', [ProductController::class, 'destroy'])->name('product.destroy');
Route::resource('product', ProductController::class)->except('create', 'show', 'destroy');


Route::resource('problem', ProblemController::class)->except('create', 'show', 'destroy');

// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
