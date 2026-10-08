<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Locks the admin shell (P4). Two behaviours are load-bearing and were silent
 * regressions once:
 *
 *  - deletes used `onsubmit="return confirm(...)"`, an unstyleable native dialog;
 *  - a populated category used to 500 on delete (restrictive products FK), so the
 *    button must be disabled *before* the request is made, not errored after.
 */
class AdminUiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_dashboard_shows_the_four_stat_cards(): void
    {
        $admin = $this->admin();
        Product::factory()->count(3)->create();
        Product::factory()->inactive()->create();
        Category::factory()->count(2)->create();
        Order::factory()->status('pending')->count(2)->create();

        $html = $this->actingAs($admin)->get('/admin')->assertOk()->getContent();

        $this->assertStringContainsString('Produk aktif', $html);
        $this->assertStringContainsString('1 nonaktif', $html);
        $this->assertStringContainsString('Pesanan menunggu', $html);
        $this->assertStringContainsString('Total pendapatan', $html);
    }

    public function test_no_native_confirm_dialogs_survive_in_admin_views(): void
    {
        $admin = $this->admin();
        $product = Product::factory()->create();
        $category = Category::factory()->create();

        foreach ([
            '/admin/products',
            '/admin/categories',
        ] as $url) {
            $html = $this->actingAs($admin)->get($url)->assertOk()->getContent();

            $this->assertStringNotContainsString('onsubmit="return confirm', $html, "[{$url}] still uses a native confirm()");
        }

        unset($product, $category);
    }

    public function test_populated_category_cannot_be_deleted_from_the_list(): void
    {
        $admin = $this->admin();
        $category = Category::factory()->create(['name' => 'Dompet']);
        Product::factory()->create(['category_id' => $category->id]);

        $html = $this->actingAs($admin)
            ->get('/admin/categories')
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/<button[^>]*disabled[^>]*title="Kategori ini masih ada produknya"/',
            $html,
            'the delete button is enabled on a category that still has products',
        );
    }

    public function test_empty_category_offers_the_delete_confirmation(): void
    {
        $admin = $this->admin();
        $category = Category::factory()->create(['name' => 'Dompet Kosong']);

        $html = $this->actingAs($admin)
            ->get('/admin/categories')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('x-on:click="confirm = true"', $html);
        $this->assertStringNotContainsString('title="Kategori ini masih ada produknya"', $html);
    }

    public function test_product_delete_warns_that_ordered_products_are_retired_not_deleted(): void
    {
        $admin = $this->admin();
        $product = Product::factory()->create(['name' => 'Dompet Terlaris']);
        OrderItem::factory()->create(['product_id' => $product->id]);

        $html = $this->actingAs($admin)
            ->get('/admin/products')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Nonaktifkan', $html);
        $this->assertStringContainsString('dipesan, jadi datanya tidak bisa dihapus permanen', $html);
    }

    public function test_product_list_spells_out_which_products_will_be_retired(): void
    {
        $admin = $this->admin();
        Product::factory()->create(['name' => 'Belum Pernah Dipesan']);

        $html = $this->actingAs($admin)
            ->get('/admin/products')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('akan dihapus permanen', $html);
    }

    public function test_order_status_uses_the_model_label_map_not_a_raw_enum_value(): void
    {
        $admin = $this->admin();
        Order::factory()->status('shipped')->create();

        $html = $this->actingAs($admin)->get('/admin/orders')->assertOk()->getContent();

        $this->assertStringContainsString('Dikirim', $html);
        $this->assertStringNotContainsString('>shipped<', $html);
    }

    public function test_order_status_form_offers_all_five_statuses(): void
    {
        $admin = $this->admin();
        $order = Order::factory()->create();

        $html = $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->getContent();

        foreach (Order::statusLabels() as $label) {
            $this->assertStringContainsString($label, $html);
        }
    }

    public function test_cancelling_from_the_admin_view_spells_out_the_restock(): void
    {
        $admin = $this->admin();
        $order = Order::factory()->status('pending')->create();

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('akan mengembalikan stok produk ke katalog');
    }

    public function test_payment_settings_render_all_three_method_groups(): void
    {
        $html = $this->actingAs($this->admin())
            ->get('/admin/settings/payment')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Transfer Bank', $html);
        $this->assertStringContainsString('E-Wallet', $html);
        $this->assertStringContainsString('Kode QRIS', $html);
    }

    public function test_flash_messages_are_shown_exactly_once_per_page(): void
    {
        $admin = $this->admin();

        $html = $this->actingAs($admin)
            ->withSession(['status' => 'Produk dihapus.'])
            ->get('/admin/products')
            ->assertOk()
            ->getContent();

        $this->assertSame(
            1,
            substr_count($html, 'Produk dihapus.'),
            'the flash message renders more than once (layout + view)',
        );
    }

    public function test_validation_errors_are_shown_on_admin_forms(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/admin/products', ['name' => '', 'category_id' => '', 'material' => 'genuine', 'price' => 'abc', 'stock' => -1, 'description' => '', 'image' => null])
            ->assertSessionHasErrors(['name', 'category_id', 'price', 'stock', 'description']);

        $this->actingAs($admin)
            ->from('/admin/categories/create')
            ->followingRedirects()
            ->post('/admin/categories', ['name' => '', 'slug' => ''])
            ->assertOk()
            ->assertSee('The name field is required', false);
    }

    public function test_a_checkbox_still_carries_value_and_checked_through_to_the_input(): void
    {
        // ComponentAttributeBag::only() takes ONE array argument — it does not
        // spread varargs. The component used to call only('name', 'value',
        // 'checked', ...), which kept only 'name', so the input lost both:
        //   - value  → the browser fell back to `on`, which `boolean` rejects,
        //              failing every edit with "is active field must be true or false";
        //   - checked → an active product rendered its box empty on load.
        // Endpoint tests can never see this: they post 'is_active' => 1 directly.
        $admin = $this->admin();
        $product = Product::factory()->create(['is_active' => true]);

        $html = $this->actingAs($admin)
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/<input type="checkbox"[^>]*value="1"/',
            $html,
            'the is_active checkbox lost its value attribute, so a checked box submits the browser default "on"',
        );

        $this->assertMatchesRegularExpression(
            '/<input type="checkbox"[^>]*checked=/',
            $html,
            'an active product rendered its is_active box unchecked',
        );
    }
}
