# Lex Leather

Toko online tas & dompet kulit. Laravel 12 + Breeze (Blade/Alpine) + Tailwind 3 + SQLite.

Dua peran: **admin** (kelola produk & kategori, ubah status pesanan) dan **customer** (jelajahi katalog, keranjang, checkout, riwayat pesanan). Admin **tidak punya jalur belanja** — semua rute storefront membalas 403 untuknya.

## Menjalankan

```bash
composer setup     # install + .env + key + migrate + npm build
composer dev       # serve + queue:listen + pail + vite
```

`composer setup` menjalankan `migrate --seed`. Jika skema berubah, gunakan `php artisan migrate:fresh --seed` — `database/database.sqlite` ikut dibangun ulang.

Akun demo (semua password `password`):

| Peran | Email |
| --- | --- |
| Admin | `admin@gmail.com` |
| Customer | `customer@gmail.com` |

Foto produk diunggah ke disk `public` dan disajikan lewat symlink `public/storage`, jadi `php artisan storage:link` wajib dijalankan.

`--seed` mengisi 2 pengguna, satu baris `payment_settings`, dan 9 produk demo di 3 kategori (`DemoCatalogSeeder`) supaya storefront bisa langsung dinilai.

```bash
php artisan test              # 168 test
vendor/bin/pint               # format
```

## Alur utama

```
Katalog → Keranjang (session) → Checkout → Pesanan dibuat (stok dipesan)
                                            → Bayar (simulasi) / ubah status oleh admin
```

- **Keranjang** disimpan di session sebagai `[product_id => qty]`. Tidak ada tabel `carts`. Nama, harga, dan stok selalu dibaca ulang dari database, jadi keranjang tidak bisa menampilkan data basi.
- **Checkout** berjalan dalam satu transaksi: baris produk dikunci `lockForUpdate()`, harga/nama/alamat di-*snapshot* ke `orders` dan `order_items`, `total_amount` dihitung dari harga saat itu, lalu stok langsung dikurangi.
- **Stok dipesan saat pesanan dibuat**, bukan saat dibayar — mencegah dua pelanggan memperebutkan unit terakhir. Stok hanya kembali saat admin mengubah status menjadi `cancelled`, dan hanya pada transisi masuk ke status tersebut.
- **Pembayaran adalah simulasi.** Tidak ada payment gateway. Pelanggan menekan tombol "Simulasikan Pembayaran" yang mengubah `pending` → `paid`; klik ganda tidak menghasilkan error. Nomor rekening/e-wallet dan kode QRIS disimpan admin di `payment_settings` (satu baris) dan ditampilkan saat checkout.

## Peta kode

| Path | Isi |
| --- | --- |
| `app/Models/` | `Product`, `Category`, `Order`, `OrderItem`, `PaymentSetting` |
| `app/Support/Cart.php` | Pembungkus keranjang session |
| `app/Http/Controllers/` | `ProductController`, `CartController`, `CheckoutController`, `OrderController` |
| `app/Http/Controllers/Admin/` | CRUD produk/kategori, ubah status pesanan, pengaturan pembayaran |
| `app/Http/Middleware/EnsureUserIsAdmin.php` | Alias `admin`, didaftarkan di `bootstrap/app.php` |
| `app/Http/Middleware/EnsureUserIsCustomer.php` | Alias `customer` — memblokir admin dari rute storefront |
| `resources/views/layouts/app.blade.php` | Satu-satunya shell HTML untuk seluruh aplikasi |

## Frontend

Satu layout untuk semua halaman. `<x-app-layout>` punya dua varian:

```blade
<x-app-layout title="Katalog">...</x-app-layout>            {{-- nav + footer + grain --}}
<x-app-layout variant="centered" title="Masuk">...</x-app-layout>  {{-- kartu polos, tanpa nav --}}
```

Varian `centered` dipakai layar auth (login, register, dsb.) — kartu polos tanpa nav. Halaman profil memakai varian default, dan hanya untuk customer (admin 403 — lihat bagian batas di bawah). `layouts/store-layout`, `layouts/guest`, dan `components/application-logo` sudah dihapus — navigasi Breeze mengasumsikan pengguna sudah login, dan itu tidak berlaku di mana pun lagi.

### Tombol kembali

`components/back.blade.php` merender tombol **Kembali** di setiap halaman (kedua varian layout), dari `layouts/app.blade.php`. Bentuknya `<button>` + `history.back()`, bukan `<a href>` — link menambah entri history, sehingga tombol back browser justru balik ke halaman yang baru saja ditinggalkan (membatalkan tombol kembali). `history.back()` hanya memindahkan indeks, jadi back browser setelahnya meneruskan ke halaman sebelumnya, tidak pernah mengundo.

Tombol hanya muncul kalau request membawa `Referer` same-origin, artinya memang ada halaman sebelumnya di tab itu. Halaman yang dibuka langsung di tab baru tidak menampilkannya — tombol yang tidak bisa melakukan apa-apa lebih buruk daripada tidak ada.

### Batas admin ↔ customer

Admin diperlakukan sebagai **pengelola toko, bukan pembeli**:

| | Customer / tamu | Admin |
| --- | --- | --- |
| `/`, `/products`, `/products/{slug}` | 200 | **403** |
| `/cart`, `/checkout`, `/orders` | 200 (login dulu) | **403** |
| `/dashboard` | tampil | redirect ke `/admin` |
| `/profile` | 200 | **403** |
| `/admin/*`, `/admin/settings/account` | 403 | 200 |

Ini diblokir di **route**, bukan cuma link-nya disembunyikan: middleware `customer` (`EnsureUserIsCustomer`) membungkus rute storefront **dan** rute profil. Nav, drawer, dan footer secara bersamaan menyembunyikan Katalog / Keranjang / Pesanan Saya untuk admin, dan link "Lihat di katalog" di layar admin dihapus — tautan yang tidak bisa diikuti pengguna halaman itu tetap bug. Logo juga mengarah ke `/admin` untuk admin, supaya menekan logo bukan jebakan 403.

**Akun admin ada di `/admin/settings/account`**, bukan di `/profile`: nama, email, ganti password (memakai rute `password.update` Breeze apa adanya). Tidak ada nomor HP / alamat (keduanya hanya prefill checkout) dan **tidak ada hapus akun** — aplikasi ini menanam tepat satu admin, `role` tidak di-`fillable`, dan tidak ada UI pembuat admin, jadi menghapus akun sendiri akan mengunci toko dari pengelola.

`AdminBoundaryTest` mengunci semua ini.

**Token** (`tailwind.config.js`): `parchment` (netral hangat, dasar), `espresso` (cokelat tua, teks & tombol), `cognac` (aksen jenuh), plus semantik `success`/`warning`/`danger`/`info`. Palet bawaan Tailwind sengaja dibiarkan utuh, tapi **tidak boleh dipakai** — `PolishTest` gagal kalau ada utilitas `gray-*`/`stone-*`/`indigo-*`/dsb. yang muncul di view.

**Primitif** (`resources/css/app.css`, `@layer components`): `.btn` (+ varian & `.btn-sm`/`.btn-lg`), `.field .label .help`, `.card .card-hover .card-media`, `.badge-{accent,success,warning,danger,info,neutral}`, `.table-wrap .table`, `.page-container`, `.eyebrow`, `.section-title`, `.grain-overlay`, `.stitch-frame`, `.nav-underline`, `.break-anywhere`, `.fade-rise`.

> **Jebakan Tailwind v3 — primitif tidak terkompil sampai dipakai.** Output `@layer components` di-*purge* sampai ada yang mereferensikannya. Primitif yang ditulis tapi tak terpakai lenyap diam-diam, `grain-breathe` pernah begitu). Sebaliknya, `@apply` yang salah di `@layer components` **tidak** menggagalkan `npm run build` sampai kelas itu dipakai — `border-espresso-200` lolos build lalu baru error saat dirender. Verifikasi dengan `grep` di `public/build/assets/app-*.css` setelah menambah primitif.

**Komponen**: `x-icon` (25 SVG tulis tangan, tanpa dependensi), `x-select`, `x-textarea`, `x-checkbox`, `x-order-status`, plus komponen Breeze yang ditulis ulang di atas primitif (`text-input`, `input-label`, `input-error`, `primary-button`, `dropdown`, `modal`, …).

> Nama kelas **harus utuh** — `badge-{{ $tone }}` tidak akan pernah dikompilasi. Tailwind tidak bisa melihat nama yang di-interpolasi. `x-icon` dan `x-order-status` memakai lengan `match` karena itu.

**Label dari model, bukan dari view.** `Order::statusLabels()`, `Order::paymentMethodLabels()`, dan `Product::materialLabels()` adalah satu-satunya sumber label. View memanggilnya; tidak menyalin sendiri.

**Halaman**: `/` landing (hero, strip kategori, 4 produk terbaru, trust bar), `/products` katalog dengan sidebar filter + chip filter yang membawa filter lain ikut, `/products/{slug}` dengan panel beli sticky, `/cart` dengan stepper `+`/`−` yang submit PATCH yang sama, `/checkout` 3 langkah bernomor, `/orders` dengan timeline 4 tahap, dan panel admin dengan stat card + tabel sticky.

**Ikon & font**: tidak ada dependensi baru. Fraunces (display) + Figtree (body) dimuat dari `fonts.bunny.net`; tanpa JS, halaman tetap utuh secara tipografi.

## Catatan implementasi

- `role` **tidak** ada di `User::$fillable` dan tidak pernah divalidasi dari request, sehingga pelanggan tidak bisa menaikkan dirinya sendiri lewat `PATCH /profile`. Ada test yang mengunci hal ini.
- Setiap kolom yang kosong divalidasi sebagai `required`, jadi submit kosong menghasilkan 422 dengan pesan, bukan 500. `tests/Feature/CheckoutTest.php` mengunci tiap field.
- `orders.status` tidak pernah diubah nilainya. Menambah status baru butuh rebuild tabel di SQLite.
- Produk yang pernah dipesan tidak dihapus (foreign key `order_items.product_id` restrictive) — dinonaktifkan lewat `is_active = false`. Kategori yang masih memiliki produk juga tidak bisa dihapus.
- Harga dan total berupa `integer` rupiah penuh, tanpa pecahan.
- `products.category_id` juga restrictive. Tombol hapus pada kategori yang masih punya produk di-*disable* di UI, bukan menunggu 500 — `AdminUiTest` mengunci itu.
- Navigasi lama `hidden sm:flex` tanpa hamburger membuat pengguna ponsel tidak punya jalan ke Katalog/Pesanan/Admin. Sekarang ada drawer Alpine, dan `LayoutShellTest` mengunci link-nya.
- `delete` tidak pernah memakai `onsubmit="return confirm(...)"` — dialog native tidak bisa di-style. Diganti modal Alpine.
- Tidak ada API. Jika dibutuhkan, tambahkan `api:` pada `withRouting()` di `bootstrap/app.php`.
