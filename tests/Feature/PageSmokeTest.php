<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Renders every GET route so a broken Blade template fails here instead of
 * in front of a user.
 */
class PageSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_pages_render(): void
    {
        $product = Product::factory()->create();

        $this->get('/')->assertOk();
        $this->get('/products')->assertOk();
        $this->get(route('products.show', $product))->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
        $this->get('/forgot-password')->assertOk();
    }

    public function test_customer_pages_render(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);
        $order = Order::factory()->bankTransfer()->status('pending')->create(['user_id' => $user->id]);
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($user)->get('/profile')->assertOk();
        $this->actingAs($user)->get('/orders')->assertOk();
        $this->actingAs($user)->get(route('orders.show', $order))->assertOk();

        $this->actingAs($user)->withSession(['cart' => [$product->id => 2]])->get('/cart')->assertOk()->assertSee($product->name);
        $this->actingAs($user)->withSession(['cart' => [$product->id => 2]])->get('/checkout')->assertOk();
        $this->actingAs($user)->get('/dashboard')->assertOk();
    }

    public function test_admin_pages_render(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $order = Order::factory()->create();
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $product->id]);

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/products')->assertOk();
        $this->actingAs($admin)->get('/admin/products/create')->assertOk();
        $this->actingAs($admin)->get(route('admin.products.edit', $product))->assertOk();
        $this->actingAs($admin)->get('/admin/categories')->assertOk();
        $this->actingAs($admin)->get('/admin/categories/create')->assertOk();
        $this->actingAs($admin)->get(route('admin.categories.edit', $category))->assertOk();
        $this->actingAs($admin)->get('/admin/orders')->assertOk();
        $this->actingAs($admin)->get(route('admin.orders.show', $order))->assertOk();
        $this->actingAs($admin)->get('/admin/settings/payment')->assertOk();
        $this->actingAs($admin)->get('/admin/settings/account')->assertOk();
    }

    public function test_checkout_shows_filled_payment_instructions(): void
    {
        PaymentSetting::create([
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'PT Lex Leather',
            'ewallet_provider' => 'DANA',
            'ewallet_number' => '081298765432',
            'ewallet_holder' => 'PT Lex Leather',
        ]);

        $product = Product::factory()->create(['stock' => 10]);

        $this->actingAs(User::factory()->create())
            ->withSession(['cart' => [$product->id => 1]])
            ->get('/checkout')
            ->assertOk()
            ->assertSee('1234567890')
            ->assertSee('081298765432');
    }

    public function test_cod_order_detail_renders_without_payment_settings(): void
    {
        $order = Order::factory()->cod()->status('pending')->create();

        $this->actingAs($order->user)->get(route('orders.show', $order))->assertOk();
    }
}
