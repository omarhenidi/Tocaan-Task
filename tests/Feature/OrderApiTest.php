<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
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

    public function test_can_create_order_with_product_ids_and_get_pay_url(): void
    {
        $user = User::factory()->create();
        $p1 = Product::factory()->create(['price' => 10]);
        $p2 = Product::factory()->create(['price' => 20]);

        $response = $this->postJson('/api/v1/client/orders', [
            'items' => [
                ['product_id' => $p1->id, 'quantity' => 2],
                ['product_id' => $p2->id, 'quantity' => 1],
            ],
            'payment_method' => 'credit_card',
        ], ['Authorization' => 'Bearer ' . $this->getToken($user)]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'order' => ['id', 'order_number', 'total_price', 'items'],
                    'pay_url',
                ],
            ])
            ->assertJsonPath('data.order.total_price', 40)
            ->assertJsonPath('data.pay_url', fn ($v) => str_contains((string) $v, 'order_id='));
    }

    public function test_create_order_validates_product_exists(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/client/orders', [
            'items' => [['product_id' => 99999, 'quantity' => 1]],
        ], ['Authorization' => 'Bearer ' . $this->getToken($user)]);

        $response->assertStatus(422)->assertJsonValidationErrors(['items.0.product_id']);
    }

    public function test_list_orders_returns_user_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(3)->create(['user_id' => $user->id, 'status' => OrderStatus::Pending]);

        $response = $this->getJson('/api/v1/client/orders?per_page=10', [
            'Authorization' => 'Bearer ' . $this->getToken($user),
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data' => ['data', 'meta' => ['current_page', 'per_page', 'total']]])
            ->assertJsonPath('success', true);
        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_list_orders_can_filter_by_status(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(2)->create(['user_id' => $user->id, 'status' => OrderStatus::Pending]);
        Order::factory()->count(1)->create(['user_id' => $user->id, 'status' => OrderStatus::Confirmed]);

        $response = $this->getJson('/api/v1/client/orders?status=confirmed&per_page=10', [
            'Authorization' => 'Bearer ' . $this->getToken($user),
        ]);

        $response->assertStatus(200)->assertJsonPath('success', true);
        $this->assertCount(1, $response->json('data.data'));
        $this->assertSame('confirmed', $response->json('data.data.0.status'));
    }

    public function test_delete_order_with_payments_cancels_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $order->transactions()->create([
            'payment_id' => 'pay_1',
            'status' => 'successful',
            'payment_method' => 'credit_card',
            'amount' => $order->total_price,
        ]);

        $response = $this->deleteJson('/api/v1/client/orders/' . $order->id, [], [
            'Authorization' => 'Bearer ' . $this->getToken($user),
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Order cancelled successfully.']);
        $order->refresh();
        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_can_delete_order_without_payments(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson('/api/v1/client/orders/' . $order->id, [], [
            'Authorization' => 'Bearer ' . $this->getToken($user),
        ]);

        $response->assertStatus(200);
        $this->assertSoftDeleted('orders', ['id' => $order->id]);
    }
}
