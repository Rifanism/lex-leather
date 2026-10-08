<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_is_publicly_accessible(): void
    {
        Product::factory()->create(['name' => 'Tas Ransel Kulit']);

        $this->get('/products')->assertOk()->assertSee('Tas Ransel Kulit');
        $this->get('/')->assertOk();
    }

    public function test_inactive_products_are_hidden_from_catalog(): void
    {
        Product::factory()->inactive()->create(['name' => 'Produk Disembunyikan']);

        $this->get('/products')->assertOk()->assertDontSee('Produk Disembunyikan');
    }

    public function test_inactive_product_detail_returns_404(): void
    {
        $product = Product::factory()->inactive()->create();

        $this->get(route('products.show', $product))->assertNotFound();
    }

    public function test_products_can_be_filtered(): void
    {
        $wallets = Category::factory()->create(['name' => 'Dompet', 'slug' => 'dompet']);
        $bags = Category::factory()->create(['name' => 'Tas', 'slug' => 'tas']);

        Product::factory()->create(['name' => 'Dompet Kulit Asli', 'category_id' => $wallets->id, 'material' => 'genuine', 'price' => 100_000]);
        Product::factory()->create(['name' => 'Tas Kanvas Murah', 'category_id' => $bags->id, 'material' => 'synthetic', 'price' => 50_000]);

        $this->get('/products?category=dompet')
            ->assertOk()
            ->assertSee('Dompet Kulit Asli')
            ->assertDontSee('Tas Kanvas Murah');

        $this->get('/products?material=synthetic')
            ->assertOk()
            ->assertSee('Tas Kanvas Murah')
            ->assertDontSee('Dompet Kulit Asli');

        $this->get('/products?min_price=60000')
            ->assertOk()
            ->assertSee('Dompet Kulit Asli')
            ->assertDontSee('Tas Kanvas Murah');

        $this->get('/products?q=Kanvas')
            ->assertOk()
            ->assertSee('Tas Kanvas Murah')
            ->assertDontSee('Dompet Kulit Asli');
    }

    public function test_invalid_filter_value_is_rejected_not_crashed(): void
    {
        $this->get('/products?material=bukan-material')->assertSessionHasErrors('material');
        $this->get('/products?sort=sql-injection')->assertSessionHasErrors('sort');
    }
}
