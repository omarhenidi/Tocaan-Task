<?php

use App\Http\Controllers\Api\V1\Client\Order\OrderController as ClientOrderController;
use Illuminate\Support\Facades\Route;

Route::post('payment/callback', [ClientOrderController::class, 'handleCallback'])
    ->name('payment.callback');
