<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\ProductController;
use Modules\Product\Http\Controllers\VendorProductController;
use Modules\Product\Http\Middleware\IsVendor;

Route::prefix('vendor')
    ->middleware(['auth:api', IsVendor::class])
    ->group(function () {
        Route::apiResource('products', VendorProductController::class)
            ->only(['index', 'store','show', 'update', 'destroy']);
    });

Route::get('/products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'show']);
