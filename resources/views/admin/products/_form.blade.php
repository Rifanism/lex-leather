@php $editing = isset($product) && $product->exists; @endphp

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-input-label for="name" value="Nama Produk" />
        <x-text-input id="name" name="name" type="text" required maxlength="255" class="mt-1.5"
                      :value="old('name', $editing ? $product->name : '')" />
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="slug" value="Slug (opsional)" />
        <x-text-input id="slug" name="slug" type="text" maxlength="255" class="mt-1.5"
                      :value="old('slug', $editing ? $product->slug : '')" placeholder="dibuat otomatis dari nama" />
        <p class="help mt-1.5">Dipakai di URL. Kalau dikosongkan, diambil dari nama produk.</p>
        <x-input-error :messages="$errors->get('slug')" />
    </div>

    <div>
        <x-input-label for="category_id" value="Kategori" />
        <x-select id="category_id" name="category_id" required class="mt-1.5">
            <option value="">Pilih kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}"
                    @selected((string) old('category_id', $editing ? $product->category_id : '') === (string) $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('category_id')" />
    </div>

    <div>
        <x-input-label for="material" value="Material" />
        <x-select id="material" name="material" required class="mt-1.5">
            @foreach (\App\Models\Product::MATERIALS as $material)
                <option value="{{ $material }}"
                    @selected(old('material', $editing ? $product->material : 'genuine') === $material)>
                    {{ \App\Models\Product::materialLabels()[$material] }}
                </option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('material')" />
    </div>

    <div>
        <x-input-label for="price" value="Harga (Rp, bulat)" />
        <x-text-input id="price" name="price" type="number" min="0" step="1" required class="mt-1.5"
                      :value="old('price', $editing ? $product->price : '')" />
        <p class="help mt-1.5">Bulat, tanpa titik atau koma.</p>
        <x-input-error :messages="$errors->get('price')" />
    </div>

    <div>
        <x-input-label for="stock" value="Stok" />
        <x-text-input id="stock" name="stock" type="number" min="0" step="1" required class="mt-1.5"
                      :value="old('stock', $editing ? $product->stock : '')" />
        <p class="help mt-1.5">Langsung turun saat pesanan dibuat.</p>
        <x-input-error :messages="$errors->get('stock')" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="description" value="Deskripsi" />
        <x-textarea id="description" name="description" rows="6" required class="mt-1.5">{{ old('description', $editing ? $product->description : '') }}</x-textarea>
        <p class="help mt-1.5">Tampil apa adanya di halaman produk, jadi tulis dalam bahasa pelanggan.</p>
        <x-input-error :messages="$errors->get('description')" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="image" :value="$editing ? 'Ganti foto (opsional)' : 'Foto produk'" />
        <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" @required(! $editing)
               class="field mt-1.5 file:me-3 file:rounded-full file:border-0 file:bg-parchment-200 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-espresso-800 hover:file:bg-parchment-300">
        <p class="help mt-1.5">JPG/PNG/WEBP, maksimal 2 MB. Disimpan di storage/app/public.</p>
        <x-input-error :messages="$errors->get('image')" />

        @if ($editing && $product->hasImage())
            <img src="{{ $product->imageUrl() }}" alt="Foto produk saat ini" loading="lazy"
                 class="mt-4 size-32 rounded-xl border border-parchment-200 bg-parchment-100 object-cover">
        @endif
    </div>

    <div class="sm:col-span-2">
        <x-checkbox name="is_active" value="1"
                    :checked="old('is_active', $editing ? $product->is_active : true)"
                    label="Tampilkan produk ini di katalog"
                    hint="Kalau dimatikan, produk hilang dari katalog tapi pesanan lama tetap utuh." />
        <x-input-error :messages="$errors->get('is_active')" />
    </div>
</div>

<div class="mt-8 flex flex-wrap items-center justify-end gap-3 border-t border-parchment-200 pt-6">
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline">Batal</a>
    <x-primary-button><x-icon name="check" /> {{ $editing ? 'Simpan Perubahan' : 'Simpan Produk' }}</x-primary-button>
</div>