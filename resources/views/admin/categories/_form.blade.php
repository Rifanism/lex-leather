@php $editing = isset($category) && $category->exists; @endphp

<div class="space-y-4">
    <div>
        <x-input-label for="name" value="Nama Kategori" />
        <x-text-input id="name" name="name" type="text" required maxlength="255" class="mt-1.5"
                      :value="old('name', $editing ? $category->name : '')" />
        <p class="help mt-1.5">Tampil di navigasi katalog dan di halaman produk.</p>
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="slug" value="Slug (opsional)" />
        <x-text-input id="slug" name="slug" type="text" maxlength="255" class="mt-1.5"
                      :value="old('slug', $editing ? $category->slug : '')" placeholder="dibuat otomatis dari nama" />
        <p class="help mt-1.5">Dipakai di URL katalog. Kalau dikosongkan, diambil dari nama kategori.</p>
        <x-input-error :messages="$errors->get('slug')" />
    </div>
</div>

<div class="mt-8 flex flex-wrap items-center justify-end gap-3 border-t border-parchment-200 pt-6">
    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline">Batal</a>
    <x-primary-button><x-icon name="check" /> {{ $editing ? 'Simpan Perubahan' : 'Simpan Kategori' }}</x-primary-button>
</div>