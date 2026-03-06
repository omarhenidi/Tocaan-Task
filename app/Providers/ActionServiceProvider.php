<?php

namespace App\Providers;

use App\Services\Admin\Order\OrderManagementService;
use App\Services\Admin\Product\ProductManagementService;
use App\Services\Client\Order\OrderService as ClientOrderService;
use App\Services\Client\Product\ProductService;
use App\Services\Client\Checkout\CheckoutService;
use App\Services\Contracts\Client\Checkout\CheckoutInterface;
use App\Services\Contracts\Client\Order\OrderInterface;
use App\Services\Contracts\Client\Product\ProductInterface;
use App\Services\Contracts\Admin\Order\OrderManagementInterface;
use App\Services\Contracts\Admin\Product\ProductManagementInterface;
use App\Services\Contracts\Admin\User\UserManagementInterface;
use App\Services\Admin\User\UserManagementService;
use Illuminate\Support\ServiceProvider;

class ActionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $bindings = [
            UserManagementInterface::class => UserManagementService::class,
            ProductInterface::class => ProductService::class,
            OrderInterface::class => ClientOrderService::class,
            CheckoutInterface::class => CheckoutService::class,
            OrderManagementInterface::class => OrderManagementService::class,
            ProductManagementInterface::class => ProductManagementService::class,
        ];

        foreach ($bindings as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
    }
}
