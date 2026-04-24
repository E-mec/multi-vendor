<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\OrderController;

Route::post('/order', OrderController::class)->name('order');

