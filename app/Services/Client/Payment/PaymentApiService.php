<?php

namespace App\Services\Client\Payment;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class PaymentApiService
{
    public function __construct(
        protected PaymentGatewayManager $gatewayManager
    ) {
    }

    public function process(Order $order, string $paymentMethod, ?string $paymentId = null, bool $simulateSuccess = true): Transaction
    {
        if ($order->status !== OrderStatus::Confirmed) {
            throw new \DomainException('Payments can only be processed for orders in confirmed status.');
        }

        $gateway = $this->gatewayManager->gateway($paymentMethod);
        $amount = (float) $order->total_price;

        $result = $gateway->process($order, $amount, [
            'payment_id' => $paymentId,
            'simulate_success' => $simulateSuccess,
        ]);

        return DB::transaction(function () use ($order, $result, $paymentMethod, $amount) {
            return Transaction::create([
                'order_id' => $order->id,
                'payment_id' => $result['payment_id'],
                'status' => $result['status'],
                'payment_method' => $paymentMethod,
                'amount' => $amount,
                'gateway_response' => $result['gateway_response'] ?? null,
            ]);
        });
    }

    public function list(?int $userId = null, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Transaction::with('order')->orderByDesc('id');

        if ($userId !== null) {
            $query->whereHas('order', fn ($q) => $q->where('user_id', $userId));
        }

        return $query->paginate($perPage);
    }

    public function listByOrder(Order $order): \Illuminate\Database\Eloquent\Collection
    {
        return $order->transactions()->orderByDesc('id')->get();
    }
}
