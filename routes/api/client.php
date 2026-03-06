<?php

use App\Http\Controllers\Api\V1\Client\Auth\AuthController;
use App\Http\Controllers\Api\V1\Client\Order\OrderController as ClientOrderController;
use App\Http\Controllers\Api\V1\Client\Payment\PaymentController as ClientPaymentController;
use App\Http\Controllers\Api\V1\Client\Product\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/client')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
    });

    Route::middleware('auth:api')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
        });
        Route::apiResource('products', ProductController::class);
        Route::get('orders/by-number/{orderNumber}', [ClientOrderController::class, 'showByOrderNumber'])
            ->name('orders.by-number');
        Route::get('orders/{order}/payments', [ClientPaymentController::class, 'byOrder']);
        Route::apiResource('orders', ClientOrderController::class);

        Route::post('checkout', [ClientOrderController::class, 'checkout']);

        Route::prefix('payments')->group(function () {
            Route::post('process', [ClientPaymentController::class, 'process']);
            Route::get('/', [ClientPaymentController::class, 'index']);
        });
    });
});
