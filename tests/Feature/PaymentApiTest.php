<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RoleSeeder']);
    }

    protected function getToken(User $user): string
    {
        return auth('api')->login($user);
    }

    public function test_process_payment_succeeds_when_order_confirmed(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 50]);
        $order = Order::factory()->confirmed()->create([
            'user_id' => $user->id,
            'total_price' => 50,
        ]);
        $order->orderItems()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'price' => 50,
        ]);

        $response = $this->postJson('/api/v1/client/payments/process', [
            'order_id' => $order->id,
            'payment_method' => 'credit_card',
            'payment_id' => 'pay_test_123',
            'simulate_success' => true,
        ], ['Authorization' => 'Bearer ' . $this->getToken($user)]);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'data' => ['id', 'order_id', 'payment_id', 'status', 'amount']])
            ->assertJson(['success' => true, 'data' => ['status' => 'successful']]);
    }

    public function test_process_payment_fails_when_order_not_confirmed(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => OrderStatus::Pending]);

        $response = $this->postJson('/api/v1/client/payments/process', [
            'order_id' => $order->id,
            'payment_method' => 'credit_card',
            'simulate_success' => true,
        ], ['Authorization' => 'Bearer ' . $this->getToken($user)]);

        $response->assertStatus(422)->assertJson(['success' => false]);
    }

    public function test_list_payments_returns_user_payments(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $order->transactions()->create([
            'payment_id' => 'pay_1',
            'status' => 'successful',
            'payment_method' => 'credit_card',
            'amount' => 100,
        ]);

        $response = $this->getJson('/api/v1/client/payments', [
            'Authorization' => 'Bearer ' . $this->getToken($user),
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data' => ['data', 'meta' => ['current_page', 'per_page', 'total']]]);
    }

    public function test_payments_by_order_returns_only_that_orders_payments(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $order->transactions()->create([
            'payment_id' => 'pay_1',
            'status' => 'successful',
            'payment_method' => 'credit_card',
            'amount' => 50,
        ]);

        $response = $this->getJson('/api/v1/client/orders/' . $order->id . '/payments', [
            'Authorization' => 'Bearer ' . $this->getToken($user),
        ]);

        $response->assertStatus(200)->assertJsonPath('data.0.payment_id', 'pay_1');
    }
}
