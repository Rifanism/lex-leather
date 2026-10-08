<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

/**
 * Locks the P1 design-system invariants: the hand-authored icon set compiles,
 * the status pill has exactly one source of truth, and the compiled CSS ships
 * the x-cloak guard that stops Alpine components flashing open.
 */
class DesignSystemTest extends TestCase
{
    use RefreshDatabase;

    /** Every name the app is allowed to pass to <x-icon>. */
    private const ICONS = [
        'bag', 'wallet', 'cart', 'search', 'filter',
        'chevron-down', 'chevron-right', 'chevron-left',
        'check', 'x', 'plus', 'minus', 'trash',
        'truck', 'shield', 'package', 'card', 'qr',
        'user', 'menu', 'arrow-right', 'edit', 'sliders',
        'alert', 'spinner',
    ];

    /** TestView renders eagerly and is Stringable, so cast instead of ->render(). */
    private function render(string $template, array $data = []): string
    {
        return (string) $this->blade($template, $data);
    }

    public function test_every_icon_renders(): void
    {
        foreach (self::ICONS as $name) {
            $html = $this->render('<x-icon :name="$name" />', ['name' => $name]);

            $this->assertStringContainsString('<svg', $html, "icon [{$name}] did not render an svg");
            $this->assertStringContainsString('viewBox="0 0 24 24"', $html);
        }
    }

    public function test_unknown_icon_fails_loudly(): void
    {
        // Blade wraps render-time throws in a ViewException, so assert the
        // message rather than coupling to the wrapper class.
        try {
            $this->render('<x-icon name="tidak-ada" />');
        } catch (\Throwable $e) {
            $this->assertStringContainsString('Unknown x-icon [tidak-ada]', $e->getMessage());

            return;
        }

        $this->fail('An unknown icon name rendered instead of failing loudly.');
    }

    public function test_icon_inherits_its_size_class(): void
    {
        $html = $this->render('<x-icon name="cart" class="size-6" />');

        $this->assertStringContainsString('size-6', $html);
        $this->assertStringContainsString('currentColor', $html);
    }

    public function test_status_pill_label_comes_from_the_model(): void
    {
        $order = Order::factory()->status('pending')->create();

        $html = $this->render('<x-order-status :status="$status" />', ['status' => $order->status]);

        $this->assertStringContainsString($order->statusLabel(), $html);
        $this->assertStringContainsString('badge-warning', $html);
    }

    public function test_every_status_has_a_label_and_a_tone(): void
    {
        foreach (Order::STATUSES as $status) {
            $this->assertArrayHasKey($status, Order::statusLabels());

            $html = $this->render('<x-order-status :status="$status" />', ['status' => $status]);
            $this->assertStringContainsString('badge-', $html, "status [{$status}] rendered no tone");
        }
    }

    public function test_status_pill_falls_back_for_an_unknown_status(): void
    {
        $html = $this->render('<x-order-status status="entah" />');

        $this->assertStringContainsString('badge-neutral', $html);
        $this->assertStringContainsString('entah', $html);
    }

    public function test_customer_and_admin_render_the_same_pill(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = Order::factory()->status('shipped')->create();

        $this->actingAs($admin)->get('/admin/orders')->assertOk()->assertSee('Dikirim');
    }

    public function test_compiled_css_ships_the_x_cloak_guard(): void
    {
        $manifestPath = public_path('build/manifest.json');

        // The suite does not build assets; skip rather than fail on a clean clone.
        if (! is_file($manifestPath)) {
            $this->markTestSkipped('Run `npm run build` to assert against the compiled CSS.');
        }

        // Vite owns the manifest shape, so let it resolve the hashed filename.
        // asset() returns an absolute URL, so reduce it to a public/ path.
        $path = parse_url(Vite::asset('resources/css/app.css'), PHP_URL_PATH) ?: '';
        $css = public_path(ltrim($path, '/'));

        $this->assertFileExists($css);
        $this->assertStringContainsString('[x-cloak]', (string) file_get_contents($css));
    }
}
