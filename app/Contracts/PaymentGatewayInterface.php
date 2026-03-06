<?php

namespace App\Contracts;

use App\Models\Order;

interface PaymentGatewayInterface
{
    public function process(Order $order, float $amount, array $options = []): array;

    public function getMethod(): string;
}
