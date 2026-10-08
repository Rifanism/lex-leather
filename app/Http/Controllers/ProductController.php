<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Landing page. Kept separate from the catalog so the first impression is
     * editorial rather than a grid; `/products` stays the browsable list.
     */
    public function home(): View
    {
        return view('products.home', [
            'categories' => Category::query()->orderBy('name')->get(),
            'featured' => Product::query()
                ->with('category')
                ->active()
                ->latest()
                ->take(4)
                ->get(),
        ]);
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'material' => ['nullable', Rule::in(Product::MATERIALS)],
            'min_price' => ['nullable', 'integer', 'min:0'],
            'max_price' => ['nullable', 'integer', 'min:0'],
            'sort' => ['nullable', Rule::in(['latest', 'price_asc', 'price_desc', 'name'])],
        ]);

        $products = Product::query()
            ->with('category')
            ->active()
            ->filter($filters)
            ->sort($filters['sort'] ?? null)
            ->paginate(12)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::query()->orderBy('name')->get(),
            'filters' => $filters,
            'materials' => Product::MATERIALS,
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        return view('products.show', [
            'product' => $product->load('category'),
        ]);
    }
}
