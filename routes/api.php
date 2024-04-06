<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\dummyapi;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SellerController;
use App\Models\Seller;
use App\Models\User;

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
// Route::get('data',[dummyapi::class,'getdata']);
// Route::post('data',[dummyapi::class,'post']);
// Route::put('data',[dummyapi::class,'put']);
// Route::get('data/{name}',[dummyapi::class,'search']);
// Route::delete('data/{id}',[dummyapi::class,'delete']);
// Route::post('data',[dummyapi::class,'validat']);


Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::middleware('auth:sanctum')->post('logout', [UserController::class, 'logout']);
Route::middleware('auth:sanctum')->get('user',[UserController::class,'display_user']);
// Route::middleware('auth:sanctum')->post('product',[UserController::class,'add_product']);
Route::middleware('auth:sanctum')->get('display_product',[UserController::class,'display_product']);
Route::middleware('auth:sanctum')->delete('product/delete/{id}',[UserController::class,'destroy']);
Route::middleware('auth:sanctum')->post('cart/{id}',[UserController::class,'addToCart']);
Route::middleware('auth:sanctum')->get('view/cart',[UserController::class,'viewCart']);
Route::middleware('auth:sanctum')->delete('cart/remove/{id}',[UserController::class,'removeFromCart']);

Route::post('seller/register', [SellerController::class, 'register']);
Route::post('seller/login', [SellerController::class, 'login']);
Route::middleware('auth:sanctum')->get('display_seller',[SellerController::class,'display_seller']);
Route::middleware('auth:sanctum')->post('seller/{id}/activate',[SellerController::class,'activate']);
Route::middleware('auth:sanctum')->post('seller/{id}/deactivate',[SellerController::class,'deactivate']);
Route::middleware('auth:sanctum')->post('product',[SellerController::class,'add_product']);
Route::middleware('auth:sanctum')->get('seller/products/{id}',[SellerController::class,'show_seller_products']);
