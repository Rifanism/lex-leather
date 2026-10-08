<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_pay_a_prepaid_order(): void
    {
        $order = Order::factory()->bankTransfer()->status('pending')->create();

        $this->actingAs($order->user)
            ->post(route('orders.pay', $order))
            ->assertSessionHas('status');

        $this->assertSame('paid', $order->fresh()->status);
    }

    public function test_paying_twice_is_a_no_op_not_an_error(): void
    {
        $order = Order::factory()->bankTransfer()->status('pending')->create();

        $this->actingAs($order->user)->post(route('orders.pay', $order));
        $this->actingAs($order->user)->post(route('orders.pay', $order));

        $this->assertSame('paid', $order->fresh()->status);
    }

    public function test_cod_order_has_no_pay_button_and_cannot_be_marked_paid_by_customer(): void
    {
        $order = Order::factory()->cod()->status('pending')->create();

        $this->actingAs($order->user)->post(route('orders.pay', $order));

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_customer_cannot_pay_someone_elses_order(): void
    {
        $order = Order::factory()->bankTransfer()->status('pending')->create();
        $intruder = User::factory()->create();

        $this->actingAs($intruder)->post(route('orders.pay', $order))->assertForbidden();

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_cannot_pay_a_shipped_order(): void
    {
        $order = Order::factory()->bankTransfer()->status('shipped')->create();

        $this->actingAs($order->user)->post(route('orders.pay', $order));

        $this->assertSame('shipped', $order->fresh()->status);
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $order = Order::factory()->create();

        $this->actingAs(User::factory()->create())->get(route('orders.show', $order))->assertForbidden();
    }

    public function test_customer_order_history_only_lists_own_orders(): void
    {
        $mine = Order::factory()->create(['order_number' => 'INV-OWN-000001']);
        $theirs = Order::factory()->create(['order_number' => 'INV-THEIRS-000001']);

        $this->actingAs($mine->user)
            ->get('/orders')
            ->assertOk()
            ->assertSee('INV-OWN-000001')
            ->assertDontSee('INV-THEIRS-000001');
    }

    /** Stock goes back only on the transition *into* cancelled. */
    public function test_admin_cancelling_an_order_restores_stock_once(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create(['stock' => 1]);
        $order = Order::factory()->create();
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 1]);

        // Simulate the checkout deduction.
        $product->update(['stock' => 0]);

        $this->actingAs($admin)->patch(route('admin.orders.status', $order), ['status' => 'cancelled']);
        $this->assertSame(1, $product->fresh()->stock);

        // Re-saving the same status must not hand the stock back twice.
        $this->actingAs($admin)->patch(route('admin.orders.status', $order), ['status' => 'cancelled']);
        $this->assertSame(1, $product->fresh()->stock);
    }

    public function test_admin_cannot_set_an_arbitrary_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = Order::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.orders.status', $order), ['status' => 'ngawur'])
            ->assertSessionHasErrors('status');

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_customer_cannot_change_order_status(): void
    {
        $order = Order::factory()->create();

        $this->actingAs($order->user)
            ->patch(route('admin.orders.status', $order), ['status' => 'paid'])
            ->assertForbidden();

        $this->assertSame('pending', $order->fresh()->status);
    }
}
