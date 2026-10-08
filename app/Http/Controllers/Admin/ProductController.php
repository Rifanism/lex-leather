<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    /** Uploads live on the `public` disk, served through the storage symlink. */
    private const DISK = 'public';

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $products = Product::query()
            ->with('category')
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where('name', 'like', "%{$term}%"))
            ->when($filters['category'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->when(($filters['status'] ?? null) === 'active', fn ($q) => $q->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'inactive', fn ($q) => $q->where('is_active', false))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::query()->orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['image'] = $request->file('image')->store('products', self::DISK);

        Product::create($data);

        return redirect()->route('admin.products.index')->with('status', 'Produk ditambahkan.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product,
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request, $product);

        if ($request->hasFile('image')) {
            Storage::disk(self::DISK)->delete($product->image);
            $data['image'] = $request->file('image')->store('products', self::DISK);
        }

        $product->update($data);

        return redirect()->route('admin.products.index')->with('status', 'Produk diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        // order_items.product_id is a restrictive FK. Products that were ever
        // ordered are retired with is_active = false instead of being deleted.
        if ($product->orderItems()->exists()) {
            $product->update(['is_active' => false]);

            return redirect()->route('admin.products.index')
                ->with('status', "Produk \"{$product->name}\" pernah dipesan, jadi dinonaktifkan alih-alih dihapus.");
        }

        Storage::disk(self::DISK)->delete($product->image);
        $name = $product->name;
        $product->delete();

        return redirect()->route('admin.products.index')->with('status', "Produk \"{$name}\" dihapus.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash'],
            'description' => ['required', 'string'],
            'material' => ['required', Rule::in(Product::MATERIALS)],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'image' => [$product === null ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $slug = ($data['slug'] ?? null) ?: Str::slug($data['name']);

        if ($slug === '') {
            throw ValidationException::withMessages([
                'slug' => 'Slug tidak boleh kosong. Isi manual bila nama produk hanya berisi simbol.',
            ]);
        }

        return [
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'],
            'material' => $data['material'],
            'price' => $data['price'],
            'stock' => $data['stock'],
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
