<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Support\Facades\Route;

// Orders
Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show']);

// Payments
Route::post('payments', [PaymentController::class, 'store']);
