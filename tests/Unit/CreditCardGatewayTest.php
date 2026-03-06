<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Services\Client\Payment\Gateways\CreditCardGateway;
use PHPUnit\Framework\TestCase;

class CreditCardGatewayTest extends TestCase
{
    public function test_process_returns_success_structure_when_simulate_success_true(): void
    {
        $gateway = new CreditCardGateway;
        $order = new Order;
        $order->id = 1;
        $order->total_price = 99.99;

        $result = $gateway->process($order, 99.99, ['simulate_success' => true]);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('payment_id', $result);
        $this->assertSame('successful', $result['status']);
    }

    public function test_process_returns_failed_structure_when_simulate_success_false(): void
    {
        $gateway = new CreditCardGateway;
        $order = new Order;
        $order->id = 1;
        $order->total_price = 50;

        $result = $gateway->process($order, 50, ['simulate_success' => false]);

        $this->assertFalse($result['success']);
        $this->assertSame('failed', $result['status']);
    }

    public function test_get_method_returns_credit_card(): void
    {
        $gateway = new CreditCardGateway;
        $this->assertSame('credit_card', $gateway->getMethod());
    }
}
