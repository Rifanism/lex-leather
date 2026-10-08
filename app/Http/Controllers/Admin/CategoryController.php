<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::query()->withCount('products')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Category::create($data);

        return redirect()->route('admin.categories.index')
            ->with('status', "Kategori \"{$data['name']}\" ditambahkan.");
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($this->validated($request));

        return redirect()->route('admin.categories.index')
            ->with('status', "Kategori \"{$category->name}\" diperbarui.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        // products.category_id has a restrictive FK, so deleting a populated
        // category would raise a QueryException (500).
        if ($category->products()->exists()) {
            return back()->with('error', 'Kategori masih memiliki produk. Pindahkan atau hapus produknya dulu.');
        }

        $name = $category->name;
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('status', "Kategori \"{$name}\" dihapus.");
    }

    /**
     * @return array<string, string>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash'],
        ]);

        $slug = ($data['slug'] ?? null) ?: Str::slug($data['name']);

        if ($slug === '') {
            throw ValidationException::withMessages([
                'slug' => 'Slug tidak boleh kosong. Isi manual bila nama kategori hanya berisi simbol.',
            ]);
        }

        return [
            'name' => $data['name'],
            'slug' => $slug,
        ];
    }
}
