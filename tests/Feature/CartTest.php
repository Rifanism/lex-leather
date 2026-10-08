<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_cart(): void
    {
        $this->get('/cart')->assertRedirect('/login');
    }

    public function test_product_can_be_added_to_cart(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->actingAs(User::factory()->create())
            ->post('/cart', ['product_id' => $product->id, 'quantity' => 2])
            ->assertSessionHasNoErrors();

        $this->actingAs(User::factory()->create())
            ->get('/cart')
            ->assertOk()
            ->assertSee($product->name);
    }

    public function test_adding_more_than_available_stock_is_rejected(): void
    {
        $product = Product::factory()->create(['stock' => 1]);

        $this->actingAs(User::factory()->create())
            ->from('/products/'.$product->slug)
            ->post('/cart', ['product_id' => $product->id, 'quantity' => 5])
            ->assertSessionHasErrors('quantity');
    }

    public function test_inactive_product_cannot_be_added(): void
    {
        $product = Product::factory()->inactive()->create();

        $this->actingAs(User::factory()->create())
            ->post('/cart', ['product_id' => $product->id, 'quantity' => 1])
            ->assertSessionHasErrors('product_id');
    }

    public function test_unknown_product_id_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/cart', ['product_id' => 999999, 'quantity' => 1])
            ->assertSessionHasErrors('product_id');
    }

    public function test_zero_quantity_is_rejected(): void
    {
        $product = Product::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post('/cart', ['product_id' => $product->id, 'quantity' => 0])
            ->assertSessionHasErrors('quantity');
    }

    public function test_empty_cart_shows_empty_state(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/cart')
            ->assertOk()
            ->assertSee('Keranjangmu masih kosong.');
    }
}
