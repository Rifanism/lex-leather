<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The cart row now ships a +/- stepper instead of a number input plus an
 * "Ubah" button. The buttons are plain submit buttons named `quantity`, so the
 * PATCH contract is unchanged — this locks that.
 */
class CartStepperTest extends TestCase
{
    use RefreshDatabase;

    public function test_stepper_posts_the_incremented_quantity(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);

        $this->actingAs($user)->withSession(['cart' => [$product->id => 2]]);

        $html = $this->get('/cart')->assertOk()->getContent();

        $this->assertStringContainsString('name="quantity" value="3"', $html);
        $this->assertStringContainsString('name="quantity" value="1"', $html);

        $this->actingAs($user)
            ->withSession(['cart' => [$product->id => 2]])
            ->patch('/cart/'.$product->slug, ['quantity' => 3])
            ->assertRedirect();

        $this->assertSame(3, session('cart')[$product->id]);
    }

    public function test_decrement_button_disappears_at_one(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);

        $html = $this->actingAs($user)->withSession(['cart' => [$product->id => 1]])
            ->get('/cart')->assertOk()->getContent();

        // The minus button must still render, but carry the disabled attribute.
        $this->assertMatchesRegularExpression(
            '/name="quantity" value="0"\s+disabled/',
            $html,
            'the decrement button is not disabled at quantity 1',
        );
    }

    public function test_increment_button_disables_at_available_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 3]);

        $html = $this->actingAs($user)->withSession(['cart' => [$product->id => 3]])
            ->get('/cart')->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/name="quantity" value="4"\s+disabled/',
            $html,
            'the increment button is not disabled at max stock',
        );
    }

    public function test_noscript_fallback_still_offers_a_number_input(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);

        $html = $this->actingAs($user)->withSession(['cart' => [$product->id => 1]])
            ->get('/cart')->assertOk()->getContent();

        $this->assertStringContainsString('<noscript>', $html);
        $this->assertStringContainsString('type="number" name="quantity"', $html);
    }

    public function test_summary_counts_units_not_lines(): void
    {
        $user = User::factory()->create();
        [$a, $b] = Product::factory()->count(2)->create(['stock' => 10]);

        $html = $this->actingAs($user)->withSession(['cart' => [$a->id => 2, $b->id => 3]])
            ->get('/cart')->assertOk()->getContent();

        $this->assertStringContainsString('Subtotal (5 barang)', $html);
    }
}
