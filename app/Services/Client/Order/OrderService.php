<?php

namespace App\Services\Client\Order;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\Contracts\Client\Order\OrderInterface;
use App\Support\Filter\Handler\Order\StatusFilter;
use App\Support\Filter\Handler\Order\UserIdFilter;
use App\Support\Traits\Orderable;
use Illuminate\Support\Facades\DB;

class OrderService implements OrderInterface
{
    use Orderable;

    public function list(array $filters = [], bool $paginate = false, int $perPage = 15)
    {
        $query = Order::query()->filter($filters, [
            'status' => StatusFilter::class,
            'user_id' => UserIdFilter::class,
        ])->with(['orderItems', 'transactions']);

        return $paginate ? $query->paginate($perPage) : $query->get();
    }

    public function store(array $data, ?int $userId = null): Order
    {
        return DB::transaction(function () use ($data, $userId) {
            $user = $userId ? \App\Models\User::find($userId) : null;
            $items = $data['items'] ?? [];
            $resolved = $this->resolveItemsFromProducts($items);
            $total = $this->calculateTotal($resolved);

            $order = Order::create([
                'user_id' => $userId ?? $data['user_id'] ?? null,
                'order_number' => $data['order_number'] ?? $this->generateOrderNumber(),
                'customer_name' => $data['customer_name'] ?? $user?->name ?? '',
                'customer_email' => $data['customer_email'] ?? $user?->email ?? '',
                'status' => $data['status'] ?? OrderStatus::Pending,
                'payment_status' => $data['payment_status'] ?? PaymentStatus::UnPaid,
                'total_price' => $data['total_price'] ?? $total,
                'currency' => $data['currency'] ?? 'USD',
            ]);

            foreach ($resolved as $item) {
                $order->orderItems()->create([
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'quantity' => (int) $item['quantity'],
                    'price' => (float) $item['price'],
                ]);
            }

            return $order->load('orderItems');
        });
    }

    public function update(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data) {
            $items = $data['items'] ?? null;
            unset($data['items']);

            if (is_array($items) && ! empty($items)) {
                $resolved = $this->resolveItemsFromProducts($items);
                $order->orderItems()->delete();
                $data['total_price'] = $this->calculateTotal($resolved);
                foreach ($resolved as $item) {
                    $order->orderItems()->create([
                        'product_id' => $item['product_id'],
                        'product_name' => $item['product_name'],
                        'quantity' => (int) $item['quantity'],
                        'price' => (float) $item['price'],
                    ]);
                }
            }

            $order->update(array_filter($data, fn ($v) => $v !== null));

            return $order->fresh()->load('orderItems');
        });
    }

    public function delete(Order $order): string
    {
        if ($order->hasPayments()) {
            $order->update(['status' => OrderStatus::Cancelled]);

            return 'cancelled';
        }
        $order->delete();

        return 'deleted';
    }
}
