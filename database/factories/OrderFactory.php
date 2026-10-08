<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_number' => Order::generateOrderNumber(),
            'user_id' => User::factory(),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->numerify('08##########'),
            'shipping_address' => fake()->address(),
            'total_amount' => fake()->numberBetween(100_000, 3_000_000),
            'status' => 'pending',
            'payment_method' => 'cod',
            'note' => null,
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }

    public function cod(): static
    {
        return $this->state(fn () => ['payment_method' => 'cod']);
    }

    public function bankTransfer(): static
    {
        return $this->state(fn () => ['payment_method' => 'bank_transfer']);
    }
}
