<?php
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Example of a protected route
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public Routes (Guests)
Route::get('/products', [ProductController::class, 'GetActiveProducts']);
Route::get('/products/{id}', [ProductController::class, 'ViewSingleProduct']);

Route::middleware(['auth:sanctum', 'vendor'])->group(function () {
    Route::post('/createProducts', [ProductController::class, 'createProduct']);
    Route::put('/products/{id}', [ProductController::class, 'updateProduct']);
    Route::delete('/products/{id}', [ProductController::class, 'deleteProduct']);
    Route::get('/vendor/products', [ProductController::class, 'viewProductsByVendor']);
});

// Orders should only be 'auth:sanctum' so both roles (or just customers) can hit it
Route::middleware('auth:sanctum')->post('/orders', [OrderController::class, 'store']);
