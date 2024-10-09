<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductLogController;
use App\Http\Controllers\SaleProductController;
use App\Http\Controllers\RevenueController;

Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::get('home', [ProductController::class, 'index']);
Route::post('customers', [CustomerController::class, 'createNewCustomer']);


Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    //Product api
    Route::get('products', [ProductController::class, 'index'])->name('get_products_for_admin');
    Route::post('products', [ProductController::class, 'store']);
    Route::post('products/{id}', [ProductController::class, 'update']);
    Route::get('products/{id}', [ProductController::class, 'getProductById']);
    Route::delete('products/{id}', [ProductController::class, 'destroy']);
    //Customer
    Route::get('customers', [CustomerController::class, 'getCustomers']);
    Route::delete('customers/{id}', [CustomerController::class, 'destroy']);
    //Categories
    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('categories', [CategoryController::class, 'store']);
    Route::put('categories/{id}', [CategoryController::class, 'update']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']);
    //Brands
    Route::get('brands', [BrandController::class, 'index']);
    Route::post('brands', [BrandController::class, 'store']);
    Route::post('brands/{id}', [BrandController::class, 'update']);
    Route::delete('brands/{id}', [BrandController::class, 'destroy']);
    //ProductLogs
    Route::get('product_logs', [ProductLogController::class, 'getAllProductLogs']);
    //Product profit
    Route::get('products_sold', [SaleProductController::class, 'getAllProductSold']);
    Route::post('sale_product', [SaleProductController::class, 'SaleProduct']);
    Route::get('get_transaction', [SaleProductController::class, 'getAllTransactionSold']);
    //Revenues
    Route::get('revenues', [RevenueController::class, 'getTotalRevenue']);
    //Departments
    Route::get('departments', [DepartmentController::class, 'getDepartments']);
    Route::post('departments', [DepartmentController::class, 'store']);
    Route::put('departments/{id}', [DepartmentController::class, 'update']);
    Route::delete('departments/{id}', [DepartmentController::class, 'destroy']);
    //Employees
    Route::get('employees', [EmployeeController::class, 'getAllEmployees']);
    Route::post('employees', [EmployeeController::class, 'store']);
    Route::post('employees/{id}', [EmployeeController::class, 'update']);
    Route::delete('employees/{id}', [EmployeeController::class, 'destroy']);
    Route::get('employees/{id}', [EmployeeController::class, 'getEmployeeById']);
    Route::put('employees/{id}', [EmployeeController::class, 'changeEmployeeStatus']);

});
