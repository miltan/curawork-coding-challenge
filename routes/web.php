<?php

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

Route::get('/', function () {
    return view('welcome');
});



Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

//Request Controller Routes
Route::get('/requests', [App\Http\Controllers\RequestController::class, 'index'])->name('requests');
Route::post('/requests', [App\Http\Controllers\RequestController::class, 'store']);
Route::delete('/requests/{id}', [App\Http\Controllers\RequestController::class, 'destroy']);
Route::patch('/requests/{id}', [App\Http\Controllers\RequestController::class, 'update']);
