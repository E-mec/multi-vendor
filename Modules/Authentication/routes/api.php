<?php

use Illuminate\Support\Facades\Route;
use Modules\Authentication\Http\Controllers\AuthenticationController;
use Modules\Authentication\Http\Controllers\VendorController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthenticationController::class, 'register']);

    Route::post('/login', [AuthenticationController::class, 'login']);

    Route::post('/logout', [AuthenticationController::class, 'logout']);

});

Route::middleware(['auth:api'])->group(function () {
    Route::post('/vendor', [VendorController::class, 'store']);
});
