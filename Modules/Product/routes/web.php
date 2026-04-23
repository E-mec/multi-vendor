<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\VendorProductController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('products', VendorProductController::class)->names('product');
});
