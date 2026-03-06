<?php

namespace App\Services\Client\Payment\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentStatus;
use App\Models\Order;

class CreditCardGateway implements PaymentGatewayInterface
{
    public function process(Order $order, float $amount, array $options = []): array
    {
        $simulateSuccess = $options['simulate_success'] ?? true;
        $paymentId = $options['payment_id'] ?? 'cc_' . uniqid();

        if ($simulateSuccess) {
            return [
                'success' => true,
                'payment_id' => $paymentId,
                'status' => PaymentStatus::Successful,
                'message' => 'Payment processed successfully',
                'gateway_response' => [
                    'gateway' => 'credit_card',
                    'transaction_id' => $paymentId,
                    'auth_code' => strtoupper(bin2hex(random_bytes(4))),
                ],
            ];
        }

        return [
            'success' => false,
            'payment_id' => $paymentId,
            'status' => PaymentStatus::Failed,
            'message' => 'Payment declined',
            'gateway_response' => [
                'gateway' => 'credit_card',
                'error_code' => 'DECLINED',
            ],
        ];
    }

    public function getMethod(): string
    {
        return 'credit_card';
    }
}
