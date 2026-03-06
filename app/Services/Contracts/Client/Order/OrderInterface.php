<?php

namespace App\Services\Contracts\Client\Order;

use App\Models\Order;
interface OrderInterface
{
    public function list(array $filters = [], bool $paginate = false, int $perPage = 15);

    public function store(array $data, ?int $userId = null): Order;

    public function update(Order $order, array $data): Order;

    public function delete(Order $order): string;
}
