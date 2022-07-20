<?php

use Illuminate\Support\Facades\Route;


//Request Controller Routes
Route::get('/requests', [App\Http\Controllers\RequestController::class, 'index'])->name('requests');
Route::post('/requests', [App\Http\Controllers\RequestController::class, 'store']);
Route::delete('/requests/{id}', [App\Http\Controllers\RequestController::class, 'destroy']);
Route::patch('/requests/{id}', [App\Http\Controllers\RequestController::class, 'update']);
