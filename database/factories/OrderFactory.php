<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_number' => 'ORD-' . strtoupper(fake()->unique()->lexify('??????')) . '-' . now()->format('Ymd'),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'status' => OrderStatus::Pending,
            'total_price' => fake()->randomFloat(2, 10, 500),
            'currency' => 'USD',
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => ['status' => OrderStatus::Confirmed]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => ['status' => OrderStatus::Cancelled]);
    }
}
