<?php

use App\Http\Controllers\Api\Auth\GoogleAuthController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CouponCodeController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WishlistedProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {

    Route::post('google/login', [GoogleAuthController::class, 'store']);

    Route::post('login', [LoginController::class, 'store']);

    Route::post('register', [RegisterController::class, 'store']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('logout', [LogoutController::class, 'destroy']);

        // User routes
        Route::apiResource('users', UserController::class);

        Route::delete('users', [UserController::class, 'destroyMany']);

        Route::get('auth-user', [UserController::class, 'authenticatedUser']);

        Route::put('auth-user', [UserController::class, 'updateAuthenticatedUser']);

        // Category routes
        Route::apiResource('categories', CategoryController::class);

        Route::delete('categories', [CategoryController::class, 'destroyMultiple']);

        // Coupon code routes
        Route::apiResource('coupon-codes', CouponCodeController::class);

        Route::delete('coupon-codes', [CouponCodeController::class, 'destroyMultiple']);

        // Product routes
        Route::apiResource('products', ProductController::class);

        Route::delete('products/delete-multiple', [ProductController::class, 'destroyMultiple']);

        // Wishlist routes
        Route::get('wishlist', [WishlistedProductController::class, 'index']);

        Route::post('wishlist/{productId}', [WishlistedProductController::class, 'store']);

        Route::delete('wishlist/{productId}', [WishlistedProductController::class, 'destroy']);

        // Order routes
        Route::apiResource('orders', OrderController::class);

        Route::post('orders/place-order', [OrderController::class, 'placeOrder']);

        Route::delete('orders/delete-multiple', [OrderController::class, 'destroyMultiple']);

        // Cart routes
        Route::get('cart', [CartController::class, 'show']);

        Route::post('cart/products', [CartController::class, 'addProductOrIncrementQuantity']);

        Route::patch('cart/products/{productId}', [CartController::class, 'update']);

        Route::delete('cart/product/{productId}', [CartController::class, 'removeProduct']);

        Route::patch('cart/product/{productId}/decrement', [CartController::class, 'decrementQuantityOrDeleteProduct']);

        Route::delete('cart/clear', [CartController::class, 'clearCart']);
    });

});