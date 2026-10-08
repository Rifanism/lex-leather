<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Locks the admin/customer boundary (P6).
 *
 * The decision is B: an admin is a store manager, not a shopper. Every
 * storefront route is a hard 403 for them — not merely an unrendered link —
 * and /dashboard bounces them to /admin so login lands them where they work.
 */
class AdminBoundaryTest extends TestCase
{
    use RefreshDatabase;

    private const STOREFRONT = [
        'home',
        'products.catalog',
        'cart.index',
        'checkout.create',
        'orders.index',
    ];

    public function test_admin_gets_a_403_on_every_storefront_route(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create();

        $this->actingAs($admin)->get(route('home'))->assertForbidden();
        $this->actingAs($admin)->get(route('products.catalog'))->assertForbidden();
        $this->actingAs($admin)->get(route('products.show', $product))->assertForbidden();
        $this->actingAs($admin)->get(route('cart.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('checkout.create'))->assertForbidden();
        $this->actingAs($admin)->get(route('orders.index'))->assertForbidden();
    }

    public function test_the_storefront_stays_open_for_guests_and_customers(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create();

        // Every guest call goes first: `actingAs` leaks across requests, so a
        // customer session would otherwise turn these redirect checks green-red.
        $this->get(route('home'))->assertOk();
        $this->get(route('products.catalog'))->assertOk();
        $this->get(route('products.show', $product))->assertOk();

        foreach (['cart.index', 'checkout.create', 'orders.index'] as $name) {
            $this->get(route($name))->assertRedirect(route('login'));
        }

        foreach (['home', 'products.catalog'] as $name) {
            $this->actingAs($customer)->get(route($name))->assertOk();
        }

        $this->actingAs($customer)->get(route('products.show', $product))->assertOk();

        // Account-gated, but still a customer surface: login, never 403.
        // Checkout redirects an *empty* cart away, so hand this one a cart.
        foreach (['cart.index', 'orders.index'] as $name) {
            $this->actingAs($customer)->get(route($name))->assertOk();
        }

        $this->actingAs($customer)
            ->withSession(['cart' => [$product->id => 1]])
            ->get(route('checkout.create'))
            ->assertOk();
    }

    public function test_admin_login_lands_on_the_admin_panel_not_the_customer_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();

        // The route still exists — it just hands an admin off to /admin.
        $this->actingAs($admin)->get(route('dashboard'))->assertRedirect(route('admin.dashboard'));
        $this->actingAs($customer)->get(route('dashboard'))->assertOk();
    }

    public function test_admin_gets_a_403_on_the_customer_profile(): void
    {
        // /profile is a customer surface: shipping contact, checkout prefill,
        // and a self-delete that could remove the store's only admin. An admin
        // manages their own account under /admin instead.
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('profile.edit'))->assertForbidden();
        $this->actingAs($admin)->patch(route('profile.update'), [
            'name' => 'X', 'email' => 'x@example.com', 'phone' => '0812', 'address' => 'Jl. X',
        ])->assertForbidden();
        $this->actingAs($admin)->delete(route('profile.destroy'), [
            'password' => 'password',
        ])->assertForbidden();
    }

    public function test_the_admin_account_screen_is_their_only_account_screen(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $html = $this->actingAs($admin)
            ->get(route('admin.settings.account.edit'))
            ->assertOk()
            ->getContent();

        // It ships the default variant (nav + footer), so the nav brand link is
        // the one that must avoid `home` — or an admin taps the logo and hits a 403.
        $this->assertMatchesRegularExpression(
            '/<a href="http:\/\/localhost\/admin"[^>]*>\s*<span class="flex size-9/',
            $html,
            'the brand mark still points at the storefront for an admin',
        );

        foreach (self::STOREFRONT as $name) {
            if ($name === 'home') {
                continue;
            }

            $this->assertStringNotContainsString(
                route($name),
                $html,
                "[admin account] offers [{$name}], which is a 403 for an admin",
            );
        }

        // No phone, no address: both are checkout prefill, which an admin never
        // reaches. And no self-deletion — role is not mass assignable and nothing
        // in the UI creates admins, so deleting this one bricks the store.
        $this->assertStringNotContainsString('name="address"', $html);
        $this->assertStringNotContainsString('name="phone"', $html);
        $this->assertStringNotContainsString('profile.destroy', $html);
    }

    public function test_admin_can_update_their_own_name_and_email(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->patch(route('admin.settings.account.update'), [
                'name' => 'Admin Baru',
                'email' => 'admin-baru@gmail.com',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.settings.account.edit'));

        $admin->refresh();

        $this->assertSame('Admin Baru', $admin->name);
        $this->assertSame('admin-baru@gmail.com', $admin->email);
        $this->assertSame('admin', $admin->role, 'an account update leaked into role');
    }

    public function test_admin_cannot_write_to_the_storefront_either(): void
    {
        // GET is only the readable half. The POST/PATCH verbs are where stock
        // and money move, so they must be behind the same middleware — a route
        // group applies it to every verb, and this test proves it.
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create(['stock' => 10]);

        $this->actingAs($admin)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('cart.store'), ['product_id' => $product->id, 'quantity' => 1])
            ->assertForbidden();

        $this->actingAs($admin)
            ->withSession(['cart' => [$product->id => 1]])
            ->post(route('checkout.store'), [
                'customer_name' => 'Admin',
                'customer_email' => 'admin@gmail.com',
                'customer_phone' => '081234567890',
                'shipping_address' => 'Jalan Admin 1',
                'payment_method' => 'cod',
            ])
            ->assertForbidden();

        $this->assertSame(10, $product->fresh()->stock, 'an admin order deducted stock');
    }

    public function test_admin_never_sees_a_link_that_would_403_them(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // /profile now 403s for an admin too, so it belongs on the blocked side.
        $blocked = [...self::STOREFRONT, 'profile.edit'];

        foreach (['/admin', '/admin/products', '/admin/orders', '/admin/settings/account'] as $url) {
            $html = $this->actingAs($admin)->get($url)->assertOk()->getContent();

            foreach ($blocked as $name) {
                // `route('home')` is just the app root, which every absolute URL
                // contains — skip it, it is checked by the brand-mark test.
                if ($name === 'home') {
                    continue;
                }

                $this->assertStringNotContainsString(
                    route($name),
                    $html,
                    "[{$url}] offers [{$name}], which is a 403 for an admin",
                );
            }
        }
    }
}
