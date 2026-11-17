# Panduan Belajar per Anggota — AgriTani

Setiap anggota cukup menguasai modulnya sendiri sampai bisa menjelaskan **alur dari klik di browser sampai data tersimpan**. Baca file sesuai urutan nomor (route → controller → model → view → test). Angka setelah titik dua adalah nomor baris di file, misalnya `AuthController.php:50`.

Cara cepat mengecek pemahaman: jalankan tes modul sendiri, lalu baca tesnya. Setiap nama tes menjelaskan satu aturan.

```bash
php artisan test tests/Feature/AkunAdmin     # anggota 1
php artisan test tests/Feature/Produk        # anggota 2
php artisan test tests/Feature/Belanja       # anggota 3
php artisan test tests/Feature/PesananMasuk  # anggota 4
php artisan test tests/Feature/Laporan       # anggota 1 dan 4
```

**Bagian bersama yang semua orang wajib paham** (cukup sekilas):
- `routes/web.php` — route login/dashboard dan pemanggilan `routes/modules/*.php`
- `resources/views/layouts/app.blade.php`, `public.blade.php`, `guest.blade.php`, `layouts/partials/sidebar.blade.php` — layout Blade (`@extends`, `@section`, `@yield`)
- `resources/views/components/badge.blade.php` — komponen Blade (`<x-badge>`)
- `public/js/agritani.js` — semua kode jQuery (tiap anggota punya bagiannya, lihat di bawah)

---

## Anggota 1 — Akun & Admin

**Materi kuliah yang dipegang:** Router & Middleware, autentikasi, Validation, Blade (form), jQuery (tombol lihat password, isian rekening).

| Urutan | File | Yang dipelajari |
| --- | --- | --- |
| 1 | `routes/modules/1-akun-admin.php` | Route lupa password (`throttle:6,1`), profil, grup `role:admin` dengan `prefix('admin')` dan `name('admin.')` |
| 2 | `app/Http/Middleware/RoleMiddleware.php:15` `handle()` | Cek login → cek `is_active` (logout jika ditangguhkan) → cek peran (`abort 403`) |
| 3 | `database/migrations/0001_01_01_000000_create_users_table.php` + `2026_02_01_000001_add_bank_account_to_users_table.php` | Kolom `role`, `is_active`, dan kolom rekening; `Schema::table` untuk menambah kolom |
| 4 | `app/Models/User.php` | `hasRole():39`, `isPetani():49`, `hasBankAccount():34`, relasi `products():70`, `orders():76`, `carts():81`, cast `password => hashed` |
| 5 | `app/Http/Controllers/Auth/AuthController.php` | `login():19` (`Auth::attempt`, tolak akun nonaktif), `register():50` (petani nonaktif + wajib rekening dengan `Rule::requiredIf`), `logout():86` |
| 6 | `app/Http/Controllers/Auth/PasswordResetController.php` | `sendLink():22` dan `reset():36` memakai `Password` broker bawaan Laravel |
| 7 | `app/Http/Controllers/ProfileController.php` | `update():18` (email unik kecuali milik sendiri, foto, rekening khusus petani), `password():58` (`current_password`) |
| 8 | `app/Http/Controllers/Admin/DashboardController.php:12` | Statistik + data grafik 7 hari |
| 9 | `app/Http/Controllers/Admin/FarmerController.php` | `index():12` (cari + filter), `toggle():29` (aktifkan/tangguhkan) |
| 10 | `app/Http/Controllers/Admin/OrderController.php` | `index():11`, `show():31` semua pesanan |
| 11 | `app/Http/Controllers/Admin/ReportController.php:14` | Memanggil trait `BuildsSalesReport` tanpa `$farmerId` (semua toko) |
| 12 | View: `auth/login`, `auth/register`, `auth/forgot-password`, `auth/reset-password`, `profile/edit`, `admin/dashboard`, `admin/farmers/index`, `admin/orders/*`, `admin/reports/index` | `@csrf`, `@error`, `old()`, `@if ($user->isPetani())` untuk bagian rekening |
| 13 | `components/password-input.blade.php` + `agritani.js` bagian `[data-toggle-password]` dan `#bank-fields` | jQuery: tombol Lihat password, isian rekening muncul saat memilih Petani |
| 14 | `database/seeders/UserSeeder.php` | Akun uji dan rekening contoh |
| 15 | Tes: `AuthTest`, `LupaPasswordTest`, `ProfilTest`, `KelolaPetaniTest`, `PesananAdminTest`, `LaporanPenjualanTest` (bagian admin) | |

**Pertanyaan latihan**
1. Apa yang terjadi kalau pembeli membuka `/admin/dashboard`? Baris mana yang menolaknya?
2. Kenapa petani baru tidak bisa langsung login?
3. Bagaimana rekening wajib untuk petani tapi tidak untuk pembeli? (`Rule::requiredIf`)
4. Kenapa saat ubah profil, email sendiri tidak dianggap duplikat? (`Rule::unique(...)->ignore($user->id)`)

---

## Anggota 2 — Produk & Katalog

**Materi kuliah yang dipegang:** Model & Relationship, Form Request, CRUD, upload file, jQuery (preview foto).

| Urutan | File | Yang dipelajari |
| --- | --- | --- |
| 1 | `routes/modules/2-produk.php` | Route katalog publik, `Route::resource` produk petani dan kategori admin, `{product:slug}` |
| 2 | `database/migrations/2026_01_01_000001_create_categories_table.php`, `..._000002_create_products_table.php` | `foreignId()->constrained()`, `restrictOnDelete`, `slug` unik |
| 3 | `app/Models/Category.php`, `app/Models/Product.php` | Relasi `farmer():26`, `category():31`; scope `active():47`, `search():63`, `sortPrice():69`, `lowStock():58`; `isPurchasable():53`; `getRouteKeyName():78` (URL pakai slug) |
| 4 | `app/Http/Controllers/CatalogController.php` | `index():11` (cari, filter kategori, urut, `paginate`), `show():27` (produk serupa) |
| 5 | `app/Http/Requests/ProductRequest.php` | `authorize():14` (bukan pemilik → 404), `rules():24`, `messages():38` |
| 6 | `app/Http/Controllers/Petani/ProductController.php` | `index():16`, `store():38`, `update():63` (simpan & hapus foto lama), `toggle():82`, `uniqueSlug():100`, `authorizeOwner():112` |
| 7 | `app/Http/Controllers/Admin/CategoryController.php` | CRUD kategori; `destroy():57` menolak hapus jika masih dipakai produk |
| 8 | View: `catalog/index`, `catalog/show`, `petani/products/index`, `petani/products/form`, `admin/categories/*`, `components/product-card` | `@foreach`, `@forelse`, komponen `<x-product-card>`, `enctype="multipart/form-data"` |
| 9 | `agritani.js` bagian `input[type=file][data-preview]` | jQuery + `FileReader` untuk preview foto |
| 10 | `database/seeders/CategorySeeder.php`, `ProductSeeder.php` | Data contoh |
| 11 | Tes: `ProdukPetaniTest`, `KategoriTest` | |

**Pertanyaan latihan**
1. Kenapa produk petani yang ditangguhkan tidak muncul di katalog? (`scopeActive`)
2. Bagaimana slug dibuat unik kalau ada dua produk bernama sama?
3. Apa beda validasi di `ProductRequest` dengan `$request->validate()` di controller?
4. Kenapa kategori yang masih punya produk tidak boleh dihapus?

---

## Anggota 3 — Belanja (sisi pembeli)

**Materi kuliah yang dipegang:** Controller/Request/Response, transaksi database, relasi Eloquent, jQuery (rekening saat Transfer).

| Urutan | File | Yang dipelajari |
| --- | --- | --- |
| 1 | `routes/modules/3-belanja.php` | Grup `auth` + `role:pembeli`, route keranjang, checkout, pesanan (`PATCH /batal`, `/selesai`, `POST /bukti`) |
| 2 | `database/migrations/..._000003_create_orders_table.php`, `..._000004_create_order_items_table.php`, `..._000006_create_carts_table.php` | `unique(['user_id','product_id'])` di keranjang, kolom status pesanan |
| 3 | `app/Models/Cart.php`, `Order.php`, `OrderItem.php` | `Order`: konstanta status, `buyer():38`, `items():43`, `payment():48`, `scopeForFarmer():59`, `grandTotal():65`; `Cart::getSubtotalAttribute():27` (accessor) |
| 4 | `config/agritani.php` | Ongkir dari `.env` |
| 5 | `app/Http/Controllers/Pembeli/CartController.php` | `store():23` (tambah/menjumlah item, cek stok), `update():43`, `destroy():55` |
| 6 | `app/Http/Controllers/Pembeli/CheckoutController.php` | `create():17` (kelompok per petani, total), `store():37` (validasi + `DB::transaction`), `placeOrders():64` (`lockForUpdate`, pecah per petani, harga dibekukan, kurangi stok, buat `Payment`) |
| 7 | `app/Http/Controllers/Pembeli/OrderController.php` | `index():15`, `show():27`, `uploadProof():36`, `cancel():69` (stok kembali), `complete():88`, `authorizeOwner():101` |
| 8 | `app/Http/Controllers/Pembeli/DashboardController.php:11` | Ringkasan pembeli |
| 9 | View: `pembeli/cart`, `pembeli/checkout` (kotak `#transfer-info` berisi rekening petani), `pembeli/orders/index`, `pembeli/orders/show` (timeline, rekening tujuan, unggah bukti), `pembeli/dashboard` | |
| 10 | `agritani.js` bagian `#transfer-info` | jQuery: rekening hanya tampil jika metode Transfer dipilih |
| 11 | Tes: `KatalogKeranjangTest`, `CheckoutPesananTest`, `BuktiBayarTest` | |

**Pertanyaan latihan**
1. Kenapa checkout dibungkus `DB::transaction`? Apa yang terjadi jika stok satu produk kurang?
2. Untuk apa `lockForUpdate()`?
3. Kenapa harga disalin ke `order_items`, tidak diambil dari `products`?
4. Keranjang berisi produk dari 2 petani menghasilkan berapa pesanan dan berapa ongkir?

---

## Anggota 4 — Pesanan Masuk & Laporan (sisi petani)

**Materi kuliah yang dipegang:** Controller & Response (redirect, unduhan CSV), Query Eloquent, Trait, jQuery (cari tabel, cetak).

| Urutan | File | Yang dipelajari |
| --- | --- | --- |
| 1 | `routes/modules/4-pesanan-masuk.php` | Grup `role:petani`, route pesanan, pembayaran, pengiriman, laporan |
| 2 | `database/migrations/..._000005_create_payments_table.php` | `enum` metode dan status, `cascadeOnDelete`, `proof_image` nullable |
| 3 | `app/Models/Payment.php` | Konstanta status, relasi `order():24` |
| 4 | `app/Http/Controllers/Petani/OrderController.php` | `index():14`, `show():26`, `confirm():34` (transfer harus dikonfirmasi dulu), `reject():52` (stok kembali), `findOrder():70` (`forFarmer` → 404 jika bukan miliknya) |
| 5 | `app/Http/Controllers/Petani/PaymentController.php` | `confirm():25`, `reject():38`, `blocked():58` (aturan kapan boleh dikonfirmasi) |
| 6 | `app/Http/Controllers/Petani/ShipmentController.php` | `update():25` (kurir + resi → `dikirim`) |
| 7 | `app/Http/Controllers/Petani/DashboardController.php:12` | Statistik petani, stok menipis |
| 8 | `app/Http/Controllers/Concerns/BuildsSalesReport.php` | `salesReport():18` (validasi filter, `with()` eager loading, `when($farmerId)`, ringkasan, produk terlaris); `salesCsv():82` (`streamDownload`, pemisah `;`) |
| 9 | `app/Http/Controllers/Petani/ReportController.php:14` | `?export=csv` → CSV, selain itu view |
| 10 | View: `petani/dashboard`, `petani/orders/*`, `petani/payments/index`, `petani/shipments/index`, `petani/reports/index`, `reports/_sales`, `components/stat-card` | `@include` partial laporan yang dipakai petani & admin |
| 11 | `agritani.js` bagian `data-filter-table` dan `[data-print]`; `agritani.css` bagian `@media print` | Cari tabel tanpa reload, cetak laporan |
| 12 | `database/seeders/DemoSeeder.php` | Pesanan contoh |
| 13 | Tes: `PesananPetaniTest`, `PembayaranTest`, `PengirimanTest`, `DashboardTest`, `LaporanPenjualanTest` | |

**Pertanyaan latihan**
1. Bagaimana petani hanya melihat pesanan berisi produknya? (`Order::forFarmer`)
2. Kenapa pesanan transfer tidak bisa dikonfirmasi sebelum pembayarannya dikonfirmasi?
3. Kenapa pendapatan hanya dihitung dari pesanan `selesai`?
4. Kenapa CSV memakai titik koma, bukan koma?

---

## Titik singgung antar anggota

| Bagian | Pemilik | Dipakai oleh |
| --- | --- | --- |
| `RoleMiddleware`, `User::isPetani()` | 1 | Semua |
| Rekening petani (`bank_*`, `hasBankAccount()`) | 1 | 3 (ditampilkan di checkout dan detail pesanan) |
| `Product::active()`, `isPurchasable()` | 2 | 3 |
| Tombol keranjang di `catalog/show` | 2 | 3 |
| Checkout membuat `Payment` | 3 | 4 |
| Unggah bukti (`uploadProof`) | 3 | 4 (yang mengonfirmasi) |
| `Order::forFarmer()`, `grandTotal()` | 3 | 1 dan 4 |
| Status `dikirim` memunculkan tombol Pesanan Diterima | 4 | 3 |
| `BuildsSalesReport` + `reports/_sales` | 4 | 1 (laporan admin) |
