<?php

namespace App\Providers;

use App\Contracts\PaymentGatewayInterface;
use App\Services\Client\Payment\PaymentGatewayManager;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentGatewayManager::class, function ($app) {
            $manager = new PaymentGatewayManager;
            $gateways = config('payment.gateways', []);

            foreach ($gateways as $method => $config) {
                if (empty($config['enabled'])) {
                    continue;
                }
                $driver = $config['driver'] ?? null;
                if (!$driver || !is_string($driver) || !class_exists($driver)) {
                    continue;
                }
                $gateway = $app->make($driver);
                if ($gateway instanceof PaymentGatewayInterface) {
                    $manager->register($method, $gateway);
                }
            }

            return $manager;
        });
    }

    public function boot(): void
    {
    }
}
