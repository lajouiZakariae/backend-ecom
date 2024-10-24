<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\StoreController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request): mixed {
    return $request->user();
});

Route::apiResource('categories', CategoryController::class);

Route::delete('categories', [CategoryController::class, 'destroyMultiple']);

Route::apiResource('stores', StoreController::class);

Route::delete('stores', [StoreController::class, 'destroyMultiple']);