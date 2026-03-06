<?php

namespace App\Services\Client\Payment;

use App\Contracts\PaymentGatewayInterface;
use InvalidArgumentException;

class PaymentGatewayManager
{
    protected array $gateways = [];

    public function gateway(string $method): PaymentGatewayInterface
    {
        $method = strtolower($method);
        if (! isset($this->gateways[$method])) {
            throw new InvalidArgumentException("Unsupported payment method: {$method}");
        }

        return $this->gateways[$method];
    }

    public function register(string $method, PaymentGatewayInterface $gateway): void
    {
        $this->gateways[strtolower($method)] = $gateway;
    }

    public function unregister(string $method): void
    {
        unset($this->gateways[strtolower($method)]);
    }

    public function supportedMethods(): array
    {
        return array_keys($this->gateways);
    }

    public function has(string $method): bool
    {
        return isset($this->gateways[strtolower($method)]);
    }
}
