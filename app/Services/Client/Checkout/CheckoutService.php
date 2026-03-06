<?php

namespace App\Services\Client\Checkout;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Services\Contracts\Client\Checkout\CheckoutInterface;
use App\Services\Client\Payment\PaymentGatewayManager;
use Illuminate\Support\Facades\DB;

class CheckoutService implements CheckoutInterface
{
    public function __construct(
        protected PaymentGatewayManager $paymentGatewayManager
    ) {
    }

    public function store(array $data): array
    {
        $order = DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];
            $resolved = $this->resolveItemsFromProducts($items);
            $total = $this->calculateTotal($resolved);

            $user = isset($data['user_id']) ? \App\Models\User::find($data['user_id']) : null;

            $order = Order::create([
                'user_id' => $data['user_id'] ?? null,
                'order_number' => $data['order_number'] ?? $this->generateOrderNumber(),
                'customer_name' => $data['customer_name'] ?? $user?->name ?? '',
                'customer_email' => $data['customer_email'] ?? $user?->email ?? '',
                'status' => OrderStatus::Pending,
                'total_price' => $total,
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

            return $order;
        });

        $paymentMethod = $data['payment_method'] ?? config('payment.default', 'credit_card');
        $payUrl = $this->buildPaymentUrl($order, $paymentMethod);

        return [$order, $payUrl];
    }

    protected function resolveItemsFromProducts(array $items): array
    {
        if (empty($items)) {
            return [];
        }
        $productIds = array_unique(array_column($items, 'product_id'));
        $products = Product::query()->whereIn('id', $productIds)->get()->keyBy('id');

        $resolved = [];
        foreach ($items as $item) {
            $product = $products->get((int) $item['product_id']);
            if (! $product) {
                throw new \InvalidArgumentException("Product not found: {$item['product_id']}");
            }
            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $resolved[] = [
                'product_id' => $product->id,
                'product_name' => $product->name ?? 'Product #' . $product->id,
                'quantity' => $qty,
                'price' => (float) $product->price,
            ];
        }

        return $resolved;
    }

    protected function calculateTotal(array $items): float
    {
        $total = 0.0;
        foreach ($items as $item) {
            $total += (float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 1);
        }

        return round($total, 2);
    }

    protected function generateOrderNumber(): string
    {
        return 'ORD-' . strtoupper(uniqid()) . '-' . now()->format('Ymd');
    }

    protected function buildPaymentUrl(Order $order, string $paymentMethod): ?string
    {
        if (! $this->paymentGatewayManager->has($paymentMethod)) {
            return null;
        }

        return url("/api/payment/checkout?order_id={$order->id}&method={$paymentMethod}");
    }
}
