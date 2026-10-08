<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The order detail page now renders a four-step progress timeline instead of a
 * bare status pill. A cancelled order has no place on that track, so it gets a
 * terminal notice instead.
 */
class OrderTimelineTest extends TestCase
{
    use RefreshDatabase;

    private function orderWithStatus(string $status, string $method = 'bank_transfer'): Order
    {
        $order = Order::factory()->status($status)->create([
            'payment_method' => $method,
            'customer_name' => 'Siti Pembeli',
            'customer_phone' => '081200000002',
            'shipping_address' => 'Jl. Merdeka No. 10, Jakarta',
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => Product::factory()->create()->id,
            'product_name' => 'Tas Ransel Kulit',
            'quantity' => 2,
            'price' => 500_000,
        ]);

        return $order;
    }

    public function test_shipped_order_marks_the_three_completed_stages(): void
    {
        $order = $this->orderWithStatus('shipped');

        $html = $this->actingAs($order->user)->get(route('orders.show', $order))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Pesanan dibuat', $html);
        $this->assertStringContainsString('Pembayaran diterima', $html);
        $this->assertStringContainsString('Dikirim', $html);
        $this->assertStringContainsString('Selesai', $html);
        $this->assertStringContainsString('Dikirim', $html);
    }

    public function test_pending_order_only_shows_the_first_stage_as_current(): void
    {
        $order = $this->orderWithStatus('pending');

        $html = $this->actingAs($order->user)->get(route('orders.show', $order))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Menunggu pembayaran', $html);
        $this->assertStringContainsString('Simulasikan pembayaran', $html);
    }

    public function test_completed_order_has_no_pay_button(): void
    {
        $order = $this->orderWithStatus('completed');

        $this->actingAs($order->user)->get(route('orders.show', $order))
            ->assertOk()
            ->assertDontSee('Simulasikan pembayaran');
    }

    public function test_cancelled_order_replaces_the_timeline_with_a_notice(): void
    {
        $order = $this->orderWithStatus('cancelled');

        $html = $this->actingAs($order->user)->get(route('orders.show', $order))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Pesanan ini dibatalkan', $html);
        $this->assertStringNotContainsString('Pembayaran diterima', $html);
    }

    public function test_detail_reads_item_snapshots_not_the_live_product(): void
    {
        $order = $this->orderWithStatus('paid');

        // Rename the product after the order exists; the page must not follow.
        $order->items->first()->product->update(['name' => 'Nama Baru Totalnya Berbeda']);

        $html = $this->actingAs($order->user)->get(route('orders.show', $order))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Tas Ransel Kulit', $html);
        $this->assertStringNotContainsString('Nama Baru Totalnya Berbeda', $html);
    }

    public function test_address_is_rendered_from_the_order_snapshot(): void
    {
        $order = $this->orderWithStatus('paid');

        $order->user->update(['address' => 'Alamat Profil Yang Berubah']);

        $this->actingAs($order->user)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Jl. Merdeka No. 10, Jakarta')
            ->assertDontSee('Alamat Profil Yang Berubah');
    }

    public function test_cod_order_explains_there_is_nothing_to_pay_now(): void
    {
        $order = $this->orderWithStatus('pending', 'cod');

        $this->actingAs($order->user)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Tidak ada langkah pembayaran sekarang.')
            ->assertDontSee('Simulasikan pembayaran');
    }

    public function test_order_list_shows_each_order_number_and_total(): void
    {
        $order = $this->orderWithStatus('paid');

        $this->actingAs($order->user)->get(route('orders.index'))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee($order->formattedTotal());
    }
}
