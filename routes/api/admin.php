<?php

use App\Enums\UserRole;
use App\Http\Controllers\Api\V1\Admin\Auth\LoginController;
use App\Http\Controllers\Api\V1\Admin\Order\OrderController;
use App\Http\Controllers\Api\V1\Admin\Product\ProductController;
use App\Http\Controllers\Api\V1\Admin\User\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/admin')->group(function () {
    Route::post('login', LoginController::class);

    Route::middleware(['auth:api', 'role:' . UserRole::Admin])->group(function () {
        Route::apiResource('orders', OrderController::class);
        Route::apiResource('products', ProductController::class);
        Route::apiResource('users', UserManagementController::class);
    });
});
