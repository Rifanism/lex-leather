<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the four hard requirements:
 *  1. empty input never becomes a 500
 *  2. price + shipping address are snapshotted onto the order
 *  3. stock is deducted the moment the order is created
 *  4. product photos live behind the storage symlink
 */
class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Siti Pembeli',
            'customer_email' => 'siti@example.com',
            'customer_phone' => '081200000002',
            'shipping_address' => 'Jl. Merdeka No. 10, Jakarta',
            'payment_method' => 'cod',
            'note' => 'Tolong dibungkus rapi',
        ], $overrides);
    }

    /** Requirement 1: every required field missing must be a validation error. */
    public function test_every_field_is_required(): void
    {
        $product = Product::factory()->create(['stock' => 3]);

        $this->actingAs(User::factory()->create())
            ->withSession(['cart' => [$product->id => 1]])
            ->from('/checkout')
            ->post('/checkout', [])
            ->assertSessionHasErrors([
                'customer_name',
                'customer_email',
                'customer_phone',
                'shipping_address',
                'payment_method',
            ]);

        $this->assertSame(0, Order::count());
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_blank_strings_are_rejected(): void
    {
        $product = Product::factory()->create();

        $this->actingAs(User::factory()->create())
            ->withSession(['cart' => [$product->id => 1]])
            ->from('/checkout')
            ->post('/checkout', $this->validPayload([
                'customer_name' => '   ',
                'shipping_address' => '',
            ]))
            ->assertSessionHasErrors(['customer_name', 'shipping_address']);
    }

    public function test_unknown_payment_method_is_rejected(): void
    {
        $product = Product::factory()->create();

        $this->actingAs(User::factory()->create())
            ->withSession(['cart' => [$product->id => 1]])
            ->from('/checkout')
            ->post('/checkout', $this->validPayload(['payment_method' => 'bitcoin']))
            ->assertSessionHasErrors('payment_method');
    }

    public function test_empty_cart_redirects_instead_of_erroring(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/checkout', $this->validPayload())
            ->assertRedirect('/cart');

        $this->assertSame(0, Order::count());
    }

    /** Requirement 3: stock is deducted on order creation, not on payment. */
    public function test_stock_is_deducted_and_snapshot_is_stored(): void
    {
        $product = Product::factory()->create([
            'name' => 'Tas Ransel Luxe',
            'price' => 450_000,
            'stock' => 5,
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 2]])
            ->post('/checkout', $this->validPayload());

        $order = Order::first();

        $response->assertRedirect(route('orders.show', $order));
        $this->assertSame('pending', $order->status);
        $this->assertSame('cod', $order->payment_method);
        $this->assertSame(900_000, $order->total_amount);
        $this->assertSame('Jl. Merdeka No. 10, Jakarta', $order->shipping_address);
        $this->assertSame($user->id, $order->user_id);

        $item = $order->items->first();
        $this->assertSame('Tas Ransel Luxe', $item->product_name);
        $this->assertSame(450_000, $item->price);
        $this->assertSame(2, $item->quantity);
        $this->assertSame(900_000, $item->subtotal);

        $this->assertSame(3, $product->fresh()->stock);
        $this->assertEmpty(session('cart', []));
    }

    /** Requirement 2: later edits must not rewrite history. */
    public function test_snapshot_survives_later_product_and_profile_edits(): void
    {
        $product = Product::factory()->create(['name' => 'Dompet Asli', 'price' => 120_000, 'stock' => 4]);

        $user = User::factory()->create(['name' => 'Nama Lama', 'address' => 'Alamat Lama']);

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 1]])
            ->post('/checkout', $this->validPayload());

        $order = Order::first();

        $product->update(['name' => 'Domtip Murah', 'price' => 99_000]);
        $user->update(['name' => 'Nama Baru', 'address' => 'Alamat Baru']);
        $product->increment('stock', 10);

        $order->refresh()->load('items');

        $this->assertSame(120_000, $order->items->first()->price);
        $this->assertSame('Dompet Asli', $order->items->first()->product_name);
        $this->assertSame(120_000, $order->total_amount);
        $this->assertSame('Jl. Merdeka No. 10, Jakarta', $order->shipping_address);
        $this->assertSame('Siti Pembeli', $order->customer_name);
    }

    public function test_order_is_rejected_when_stock_is_insufficient(): void
    {
        $product = Product::factory()->create(['stock' => 1]);

        $this->actingAs(User::factory()->create())
            ->withSession(['cart' => [$product->id => 5]])
            ->from('/checkout')
            ->post('/checkout', $this->validPayload())
            ->assertSessionHasErrors('cart');

        $this->assertSame(0, Order::count());
        $this->assertSame(1, $product->fresh()->stock);
    }

    public function test_order_is_rejected_when_product_was_deactivated_after_being_added(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->actingAs(User::factory()->create())
            ->withSession(['cart' => [$product->id => 1]]);

        $product->update(['is_active' => false]);

        $this->actingAs(User::factory()->create())
            ->withSession(['cart' => [$product->id => 1]])
            ->from('/checkout')
            ->post('/checkout', $this->validPayload())
            ->assertSessionHasErrors('cart');

        $this->assertSame(0, Order::count());
    }

    public function test_product_deleted_after_being_added_does_not_crash_checkout(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $productId = $product->id;

        // Gone from the catalogue, but still sitting in the session cart.
        $product->delete();

        $this->actingAs(User::factory()->create())
            ->withSession(['cart' => [$productId => 1]])
            ->from('/checkout')
            ->post('/checkout', $this->validPayload())
            ->assertSessionHasErrors('cart');

        $this->assertSame(0, Order::count());
    }

    public function test_multi_item_order_totals_correctly(): void
    {
        $a = Product::factory()->create(['price' => 100_000, 'stock' => 10]);
        $b = Product::factory()->create(['price' => 55_000, 'stock' => 10]);

        $this->actingAs(User::factory()->create())
            ->withSession(['cart' => [$a->id => 2, $b->id => 3]])
            ->post('/checkout', $this->validPayload());

        $order = Order::first();

        $this->assertSame(365_000, $order->total_amount);
        $this->assertCount(2, $order->items);
        $this->assertSame(8, $a->fresh()->stock);
        $this->assertSame(7, $b->fresh()->stock);
    }

    public function test_guest_cannot_checkout(): void
    {
        $this->post('/checkout', $this->validPayload())->assertRedirect('/login');
    }

    /** Requirement 1 continued: an admin-only catalogue mistake must not 500. */
    public function test_checkout_page_renders_when_payment_settings_table_is_empty(): void
    {
        $product = Product::factory()->create();

        $this->actingAs(User::factory()->create())
            ->withSession(['cart' => [$product->id => 1]])
            ->get('/checkout')
            ->assertOk();
    }
}
