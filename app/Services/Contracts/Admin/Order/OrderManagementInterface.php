<?php

namespace App\Services\Contracts\Admin\Order;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderManagementInterface
{
    public function listOrders(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function show(Order $order): Order;

    public function store(array $data): Order;

    public function update(Order $order, array $data): Order;

    public function delete(Order $order): string;
}
