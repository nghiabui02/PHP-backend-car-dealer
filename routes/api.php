<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::get('products', [ProductController::class, 'index']);

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('home', [ProductController::class, 'index']);
    //Product api
    Route::get('products', [ProductController::class, 'index'])->name('get_products_for_admin');
    Route::post('products', [ProductController::class, 'store']);
    Route::put('update_products/{id}', [ProductController::class, 'update']);
    Route::delete('products/{id}', [ProductController::class, 'destroy']);

});
