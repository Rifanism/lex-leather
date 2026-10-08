<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCatalogTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_product_can_be_created_with_an_image(): void
    {
        Storage::fake('public');

        $category = Category::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'name' => 'Tas Ransel Kulit',
                'description' => 'Tas ransel dari kulit sapi.',
                'material' => 'genuine',
                'price' => 350_000,
                'stock' => 12,
                'is_active' => 1,
                'image' => UploadedFile::fake()->create('tas.jpg', 50, 'image/jpeg'),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.products.index'));

        $product = Product::first();
        $this->assertSame('tas-ransel-kulit', $product->slug);
        $this->assertTrue(Storage::disk('public')->exists($product->image));
    }

    public function test_creating_a_product_without_image_is_rejected(): void
    {
        Storage::fake('public');

        $category = Category::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'name' => 'Tanpa Foto',
                'description' => 'Tidak ada foto.',
                'material' => 'genuine',
                'price' => 1000,
                'stock' => 1,
            ])
            ->assertSessionHasErrors('image');

        $this->assertSame(0, Product::count());
    }

    public function test_missing_required_product_fields_are_rejected(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), [
                'name' => '',
                'price' => -5,
                'stock' => -1,
                'material' => 'plastik',
            ])
            ->assertSessionHasErrors(['category_id', 'name', 'description', 'material', 'price', 'stock', 'image']);
    }

    public function test_replacing_the_photo_deletes_the_old_file(): void
    {
        Storage::fake('public');

        $admin = $this->admin();
        $category = Category::factory()->create();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id, 'name' => 'Dompet', 'description' => 'Dompet kulit',
            'material' => 'genuine', 'price' => 90_000, 'stock' => 5,
            'image' => UploadedFile::fake()->create('lama.jpg', 50, 'image/jpeg'),
        ]);

        $product = Product::first();
        $oldPath = $product->image;

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'category_id' => $category->id, 'name' => 'Dompet', 'description' => 'Dompet kulit',
            'material' => 'genuine', 'price' => 90_000, 'stock' => 5, 'is_active' => 1,
            'image' => UploadedFile::fake()->create('baru.jpg', 50, 'image/jpeg'),
        ])->assertSessionHasNoErrors();

        $product->refresh();
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_updating_a_product_without_a_new_photo_keeps_the_old_one(): void
    {
        Storage::fake('public');

        $product = Product::factory()->create(['image' => 'products/lama.jpg']);
        Storage::disk('public')->put('products/lama.jpg', 'x');

        $this->actingAs($this->admin())
            ->put(route('admin.products.update', $product), [
                'category_id' => $product->category_id, 'name' => 'Nama Baru',
                'description' => 'Deskripsi', 'material' => 'synthetic',
                'price' => 5_000, 'stock' => 2, 'is_active' => 1,
            ])->assertSessionHasNoErrors();

        Storage::disk('public')->assertExists('products/lama.jpg');
        $this->assertSame('products/lama.jpg', $product->fresh()->image);
    }

    /** order_items.product_id is a restrictive FK, so ordered products get retired instead. */
    public function test_ordered_product_is_deactivated_not_deleted(): void
    {
        Storage::fake('public');

        $product = Product::factory()->create();
        OrderItem::factory()->create(['product_id' => $product->id]);

        $this->actingAs($this->admin())
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        $product->refresh();
        $this->assertNotNull($product);
        $this->assertFalse($product->is_active);
    }

    public function test_unordered_product_is_deleted_with_its_photo(): void
    {
        Storage::fake('public');

        $product = Product::factory()->create(['image' => 'products/hapus.jpg']);
        Storage::disk('public')->put('products/hapus.jpg', 'x');

        $this->actingAs($this->admin())->delete(route('admin.products.destroy', $product));

        $this->assertNull(Product::find($product->id));
        Storage::disk('public')->assertMissing('products/hapus.jpg');
    }

    public function test_populated_category_cannot_be_deleted(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $this->actingAs($this->admin())
            ->delete(route('admin.categories.destroy', $category))
            ->assertSessionHas('error');

        $this->assertNotNull(Category::find($category->id));
    }

    public function test_empty_category_can_be_deleted(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin())->delete(route('admin.categories.destroy', $category));

        $this->assertNull(Category::find($category->id));
    }

    public function test_category_slug_is_derived_from_name(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.categories.store'), ['name' => 'Tas Seldang'])
            ->assertSessionHasNoErrors();

        $this->assertSame('tas-seldang', Category::first()->slug);
    }

    public function test_symbol_only_category_name_is_rejected_instead_of_null_slug(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.categories.create'))
            ->post(route('admin.categories.store'), ['name' => '!!!'])
            ->assertSessionHasErrors('slug');

        $this->assertSame(0, Category::count());
    }

    public function test_customer_cannot_create_products(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.products.store'), [])
            ->assertForbidden();
    }
}
