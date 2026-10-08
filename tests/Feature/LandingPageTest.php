<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * `/` is the landing page; `/products` keeps the catalog. Both are served by
 * ProductController but they must not be the same view.
 */
class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_shows_hero_trust_bar_and_categories(): void
    {
        $category = Category::factory()->create(['name' => 'Dompet Kulit', 'slug' => 'dompet-kulit']);
        Product::factory()->count(4)->create();

        $this->get('/')
            ->assertOk()
            ->assertSee('Dompet Kulit')
            ->assertSee(route('products.catalog', ['category' => $category->slug]))
            ->assertSee(route('products.catalog'));
    }

    public function test_landing_page_shows_a_featured_row(): void
    {
        Product::factory()->count(6)->create();

        $this->get('/')->assertOk()->assertSee('Produk pilihan');
    }

    public function test_catalog_is_still_reachable_on_its_own_url(): void
    {
        Product::factory()->create(['name' => 'Tas Ransel Kulit']);

        $this->get(route('products.catalog'))->assertOk()->assertSee('Tas Ransel Kulit');
    }

    public function test_landing_page_works_with_an_empty_catalog(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_landing_page_never_shows_inactive_products(): void
    {
        Product::factory()->inactive()->create(['name' => 'Produk Nonaktif']);

        $this->get('/')->assertOk()->assertDontSee('Produk Nonaktif');
    }
}
