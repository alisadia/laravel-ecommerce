<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\registerController;
use App\Http\Controllers\resourceController;

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

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/register',[registerController::class,'showRegistration'])->name('register');
// Route::post('/register',[registerController::class,'register']);

// Route::get('/login',[registerController::class,'showlogin'])->name('login');
// Route::post('/login',[registerController::class,'login']);
Route::get('/demo', function () {
    return "hello world get";
   
});

Route::post('/post',function(){
   return "post";
});

Route::put('/post',function(){
    return "put";
 });
Route::patch('/post',function(){
    return "patch";
 });

 Route::any('/post',function(){
    return "any";
 });
 Route::match(['get','post'],'/',function(){
    return "any";
 });
Route::get('/demo/{name}/{id}', function ($name,$id) {
    echo $name ." " . $id;
})->whereAlpha('name')->whereNumber('id');

// Route::get('/demo/{name}', function ($name) {
//     return $name;
// })->whereAlphaNumeric('name');

// Route::get('/page/about',function(){
//     return "about";
// });
// Route::get('/page/service',function(){
//     return "service";
// });
// Route::get('/page/log',function(){
//     return "log";
// });
Route::prefix('page')->group(function(){
    Route::get('/about',function(){
        return "about";
    });
    Route::get('/service',function(){
        return "service";
    });
    Route::get('/log',function(){
        return "log";
    });
});
// Route::get('/', resourceController::class);
Route::resource('resource', resourceController::class);