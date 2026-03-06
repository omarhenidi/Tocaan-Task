<?php

namespace App\Services\Contracts\Client\Checkout;

interface CheckoutInterface
{
    public function store(array $data): array;
}
