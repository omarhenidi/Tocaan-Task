<?php

namespace App\Services\Client\Payment\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentStatus;
use App\Models\Order;

class PayPalGateway implements PaymentGatewayInterface
{
    public function process(Order $order, float $amount, array $options = []): array
    {
        $simulateSuccess = $options['simulate_success'] ?? true;
        $paymentId = $options['payment_id'] ?? 'paypal_' . uniqid();

        if ($simulateSuccess) {
            return [
                'success' => true,
                'payment_id' => $paymentId,
                'status' => PaymentStatus::Successful,
                'message' => 'PayPal payment completed',
                'gateway_response' => [
                    'gateway' => 'paypal',
                    'capture_id' => $paymentId,
                    'state' => 'approved',
                ],
            ];
        }

        return [
            'success' => false,
            'payment_id' => $paymentId,
            'status' => PaymentStatus::Failed,
            'message' => 'PayPal payment failed',
            'gateway_response' => [
                'gateway' => 'paypal',
                'reason' => 'INSUFFICIENT_FUNDS',
            ],
        ];
    }

    public function getMethod(): string
    {
        return 'paypal';
    }
}
