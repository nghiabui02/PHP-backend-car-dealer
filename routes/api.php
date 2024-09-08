<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::get('home', [ProductController::class, 'index']);
Route::post('customers', [CustomerController::class, 'createNewCustomer']);


Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    //Product api
    Route::get('products', [ProductController::class, 'index'])->name('get_products_for_admin');
    Route::post('products', [ProductController::class, 'store']);
    Route::put('update_products/{id}', [ProductController::class, 'update']);
    Route::delete('products/{id}', [ProductController::class, 'destroy']);
    //Customer
    Route::get('customers', [CustomerController::class, 'getCustomers']);
    Route::delete('customers/{id}', [CustomerController::class, 'destroy']);
    //Categories
    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('categories', [CategoryController::class, 'store']);
    Route::put('categories/{id}', [CategoryController::class, 'update']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']);
});
