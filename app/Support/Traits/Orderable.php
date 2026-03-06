<?php

namespace App\Support\Traits;

use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;

trait Orderable
{
    protected function logTransaction(Order $order, array $data): Transaction
    {
        $status = $data['transaction_status'] ?? 'pending';
        $successful = in_array(strtolower((string) $status), ['a', 'approved', 'success', 'successful', 'completed', 'paid'], true);

        return $order->transactions()->create([
            'payment_id' => $data['reference'] ?? null,
            'status' => $successful ? 'successful' : (strtolower((string) $status) === 'failed' ? 'failed' : 'pending'),
            'payment_method' => $data['payment_method'] ?? 'credit_card',
            'amount' => (float) ($data['amount'] ?? $order->total_price),
            'gateway_response' => $data['meta'] ?? null,
        ]);
    }

    protected function resolveItemsFromProducts(array $items): array
    {
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
}
