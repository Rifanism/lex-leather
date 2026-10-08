<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Locks the single-shell layout (P2). The previous storefront nav was
 * `hidden sm:flex` with no hamburger, so a phone user had no route to Katalog,
 * Pesanan Saya or Admin. These assertions exist so that cannot regress.
 */
class LayoutShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_menu_trigger_is_present_and_wired_to_the_drawer(): void
    {
        $html = $this->get('/products')->assertOk()->getContent();

        $this->assertStringContainsString('aria-controls="mobile-nav"', $html, 'no hamburger trigger found');
        $this->assertStringContainsString('id="mobile-nav"', $html, 'the mobile drawer is missing');
        $this->assertStringContainsString('lg:hidden', $html, 'the hamburger must be hidden on desktop');
    }

    public function test_drawer_exposes_every_section_reachable_from_the_desktop_bar(): void
    {
        // A customer reaches all four; the desktop bar and the drawer must agree.
        $customer = User::factory()->create();

        $html = $this->actingAs($customer)->get('/products')->assertOk()->getContent();

        foreach ([
            route('products.catalog'),
            route('orders.index'),
            route('cart.index'),
            route('profile.edit'),
        ] as $url) {
            $this->assertStringContainsString($url, $html, "[{$url}] is unreachable in the shell");
        }

        $this->assertStringNotContainsString(route('admin.dashboard'), $html, 'customers must not see the admin link');
    }

    public function test_admin_shell_offers_no_shopping_links(): void
    {
        // An admin is a manager, not a shopper: no catalog, no cart, no order
        // history — those routes 403 for them, so the links must not render.
        $admin = User::factory()->create(['role' => 'admin']);

        $html = $this->actingAs($admin)->get('/admin')->assertOk()->getContent();

        foreach ([
            route('products.catalog'),
            route('cart.index'),
            route('orders.index'),
        ] as $url) {
            $this->assertStringNotContainsString($url, $html, "[{$url}] must not be offered to an admin");
        }

        // The brand mark must land on /admin — / is a 403 for this user.
        $this->assertMatchesRegularExpression(
            '/<a href="http:\/\/localhost\/admin"[^>]*>\s*<span class="flex size-9/',
            $html,
            'the brand mark still points at the storefront for an admin',
        );
        $this->assertStringContainsString(route('admin.dashboard'), $html);
    }

    public function test_guest_shell_offers_login_and_register(): void
    {
        $html = $this->get('/products')->assertOk()->getContent();

        $this->assertStringContainsString(route('login'), $html);
        $this->assertStringContainsString(route('register'), $html);
        $this->assertStringNotContainsString(route('orders.index'), $html, 'guests must not see customer links');
        $this->assertStringNotContainsString(route('admin.dashboard'), $html, 'guests must not see admin links');
    }

    public function test_admin_shell_shows_the_admin_link_to_admins_only(): void
    {
        $customer = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($customer)->get('/products')
            ->assertOk()
            ->assertDontSee(route('admin.dashboard'));

        // /products is a 403 for an admin now, so read the shell off /admin.
        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertSee(route('admin.dashboard'));
    }

    public function test_cart_badge_counts_every_unit_not_every_line(): void
    {
        $user = User::factory()->create();
        [$a, $b] = Product::factory()->count(2)->create();

        $html = $this->actingAs($user)
            ->withSession(['cart' => [$a->id => 2, $b->id => 3]])
            ->get('/products')
            ->assertOk()
            ->getContent();

        // The badge renders 2 + 3 units, not 2 lines.
        $this->assertMatchesRegularExpression(
            '/aria-label="Keranjang".*?bg-cognac-500[^>]*>\s*5\s*</s',
            $html,
        );
    }

    public function test_auth_pages_use_the_centered_variant_without_a_cart_link(): void
    {
        $html = $this->get('/login')->assertOk()->getContent();

        $this->assertStringContainsString('bg-parchment-50', $html);
        $this->assertStringContainsString(route('home'), $html, 'the brand mark must link somewhere');
        $this->assertStringNotContainsString(route('cart.index'), $html, 'the login screen must not show a cart');
        $this->assertStringNotContainsString('id="mobile-nav"', $html, 'the centered variant must not ship the nav');
    }

    public function test_every_page_shares_one_html_shell(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        // `actingAs` leaks across requests within a test, so the guest call
        // goes before the loop instead of after it.
        $this->get('/products')->assertSee('fonts.bunny.net', false)->assertSee('/build/assets/app-', false);

        foreach (['/products', '/cart', '/orders', '/profile', '/admin'] as $url) {
            $acting = str_starts_with($url, '/admin') ? $admin : $user;

            $this->actingAs($acting)->get($url)->assertOk();
        }

        // One shell, one font request, one compiled stylesheet — for every page.
        $this->actingAs($user)->get('/orders')->assertSee('/build/assets/app-', false);
        $this->actingAs($admin)->get('/admin')->assertSee('/build/assets/app-', false);
        $this->actingAs($admin)->get('/admin/settings/account')->assertSee('/build/assets/app-', false);
    }

    public function test_every_page_carries_the_mobile_viewport_meta(): void
    {
        // Without this the whole responsive layout is inert on a phone.
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        foreach (['/products', '/cart', '/orders', '/profile', '/admin'] as $url) {
            $acting = str_starts_with($url, '/admin') ? $admin : $user;

            $this->actingAs($acting)->get($url)
                ->assertOk()
                ->assertSee('<meta name="viewport" content="width=device-width, initial-scale=1">', false);
        }
    }

    public function test_the_centered_variant_loads_the_same_assets(): void
    {
        // No actingAs() here: the centered variant is for logged-out screens.
        $this->get('/login')->assertOk()->assertSee('/build/assets/app-', false);
    }

    public function test_guests_are_redirected_away_from_authenticated_pages(): void
    {
        foreach (['/cart', '/orders', '/profile', '/admin'] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_page_title_carries_the_app_name(): void
    {
        $this->get('/products')->assertOk()->assertSee('<title>Katalog · '.config('app.name').'</title>', false);
    }

    public function test_custom_title_is_trimmed_of_duplicate_separators(): void
    {
        $order = Order::factory()->create();

        $this->actingAs($order->user)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee($order->order_number, false);
    }

    public function test_the_back_control_is_a_traversal_so_the_browser_cannot_undo_it(): void
    {
        // An `<a href>` pushes a new history entry, so after using the control
        // the browser's own back button would land on the page you just left —
        // undoing it. A conventional `<a href>` link pushes a history entry,
        // so the browser's own back button will then land on the page you just left —
        // the expected behaviour for a conventional back control.
        $user = User::factory()->create();

        $html = $this->actingAs($user)
            ->withHeaders(['Referer' => 'http://localhost/products'])
            ->get('/cart')
            ->assertOk()
            ->getContent();

        // the back control is an <a> with href pointing to the referer, not a <button> with history.back()
        $this->assertStringContainsString('<a href="http://localhost/products"', $html, 'the back control is a conventional <a href>, not a <button>');
    }

    public function test_the_back_control_stays_hidden_when_there_is_nothing_to_go_back_to(): void
    {
        $user = User::factory()->create();

        // `withHeaders` persists for the rest of the test, so the request with
        // no Referer has to run first. Without a same-origin referer there is no
        // previous page to link to, so the back control stays hidden.
        $this->actingAs($user)
            ->get('/cart')
            ->assertOk()
            ->assertDontSee('Kembali', false);

        $this->actingAs($user)
            ->withHeaders(['Referer' => 'http://localhost/products'])
            ->get('/cart')
            ->assertOk()
            ->assertSee('Kembali', false);
    }
}
