<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The catalog sidebar is a GET form, so filter chips must carry every *other*
 * filter forward. A chip that drops `sort` silently resets the ordering.
 */
class CatalogFiltersTest extends TestCase
{
    use RefreshDatabase;

    private Category $wallets;

    private Category $bags;

    protected function setUp(): void
    {
        parent::setUp();

        $this->wallets = Category::factory()->create(['name' => 'Dompet', 'slug' => 'dompet']);
        $this->bags = Category::factory()->create(['name' => 'Tas', 'slug' => 'tas']);
    }

    public function test_each_filter_narrows_the_grid(): void
    {
        // Descriptions are pinned: the factory fills them with random words, and a
        // random hit on "tas" (pasta, pastas) makes the `q` filter flaky.
        Product::factory()->create(['name' => 'Dompet Asli', 'category_id' => $this->wallets->id, 'material' => 'genuine', 'price' => 400_000, 'description' => 'Kulit.dompet']);
        Product::factory()->create(['name' => 'Tas Murah', 'category_id' => $this->bags->id, 'material' => 'synthetic', 'price' => 50_000, 'description' => 'Bahan sintetis.']);

        $this->get('/products?category=dompet')->assertOk()->assertSee('Dompet Asli')->assertDontSee('Tas Murah');
        $this->get('/products?material=synthetic')->assertOk()->assertSee('Tas Murah')->assertDontSee('Dompet Asli');
        $this->get('/products?min_price=100000')->assertOk()->assertSee('Dompet Asli')->assertDontSee('Tas Murah');
        $this->get('/products?max_price=100000')->assertOk()->assertSee('Tas Murah')->assertDontSee('Dompet Asli');
        $this->get('/products?q=Tas')->assertOk()->assertSee('Tas Murah')->assertDontSee('Dompet Asli');
    }

    public function test_chip_url_keeps_the_other_active_filters(): void
    {
        Product::factory()->create(['name' => 'Dompet Asli', 'category_id' => $this->wallets->id]);

        $html = $this->get('/products?category=dompet&material=genuine&sort=price_asc')
            ->assertOk()
            ->getContent();

        // Removing the material chip must not drop the category or the sort.
        $this->assertStringContainsString(
            'category=dompet',
            html_entity_decode($html),
            'the chip lost the category filter',
        );
        $this->assertStringContainsString('sort=price_asc', $html, 'the chip lost the sort');
    }

    public function test_empty_state_offers_a_reset(): void
    {
        $this->get('/products?q=tidak-ada-hasil-ini')
            ->assertOk()
            ->assertSee('Tidak ada yang cocok')
            ->assertSee(route('products.catalog', [], false));
    }

    public function test_reset_link_only_appears_when_something_is_filtered(): void
    {
        $unfiltered = $this->get('/products')->assertOk()->getContent();
        $this->assertStringNotContainsString('Hapus semua', $unfiltered);

        $filtered = $this->get('/products?material=genuine')->assertOk()->getContent();
        $this->assertStringContainsString('Hapus semua', $filtered);
    }

    public function test_malformed_filter_values_are_still_rejected(): void
    {
        $this->get('/products?material=bukan-material')->assertSessionHasErrors('material');
        $this->get('/products?sort=sql-injection')->assertSessionHasErrors('sort');
    }

    public function test_filter_inputs_are_preselected_after_a_search(): void
    {
        Product::factory()->create(['name' => 'Dompet Asli', 'category_id' => $this->wallets->id, 'material' => 'genuine']);

        $html = $this->get('/products?category=dompet&material=genuine&max_price=500000')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('<option value="dompet" selected', $html);
        $this->assertStringContainsString('<option value="genuine" selected', $html);
        $this->assertStringContainsString('value="500000"', $html);
    }

    public function test_pagination_is_styled_with_the_project_primitives(): void
    {
        Product::factory()->count(20)->create();

        $this->get('/products')->assertOk()->assertSee('Navigasi halaman');
    }
}
