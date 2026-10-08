<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $product = Product::factory();
        $quantity = fake()->numberBetween(1, 3);
        $price = fake()->numberBetween(50_000, 1_000_000);

        return [
            'order_id' => Order::factory(),
            'product_id' => $product,
            'product_name' => fake()->words(3, true),
            'quantity' => $quantity,
            'price' => $price,
            'subtotal' => $price * $quantity,
        ];
    }
}
