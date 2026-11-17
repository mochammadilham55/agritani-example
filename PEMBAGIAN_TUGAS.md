# Pembagian Tugas AgriTani (4 Anggota)

Setiap anggota punya file route sendiri di `routes/modules/` dan folder test sendiri di `tests/Feature/`.
Jalankan test modul sendiri: `php artisan test tests/Feature/<Folder>`.

| Anggota | Modul | Route | Folder test |
|---|---|---|---|
| 1 | Akun & Admin | `routes/modules/1-akun-admin.php` | `tests/Feature/AkunAdmin` |
| 2 | Produk & Katalog | `routes/modules/2-produk.php` | `tests/Feature/Produk` |
| 3 | Belanja (sisi pembeli) | `routes/modules/3-belanja.php` | `tests/Feature/Belanja` |
| 4 | Pesanan Masuk (sisi petani) | `routes/modules/4-pesanan-masuk.php` | `tests/Feature/PesananMasuk` |

Urutan pengerjaan (karena foreign key): **1 → 2 → 3 → 4**.

---

## Anggota 1 — Akun & Admin
**Tabel:** `users`
- **Model:** `User`
- **Migration:** `create_users_table` (bawaan Laravel, sudah memuat kolom `role` dan `is_active`)
- **Controller:** `Auth/AuthController`, `Auth/PasswordResetController` (lupa password), `ProfileController` (profil dan ubah password), `Admin/DashboardController`, `Admin/FarmerController`, `Admin/OrderController`
- **Middleware:** `RoleMiddleware` (membatasi akses per peran)
- **View:** `auth/*` (login, register, lupa dan reset password), `profile/edit`, `admin/dashboard`, `admin/farmers/*`, `admin/orders/*`
- **Seeder:** `UserSeeder`
- **Test:** `AuthTest`, `LupaPasswordTest`, `ProfilTest`, `KelolaPetaniTest`, `PesananAdminTest`
V
**Yang dijelaskan saat presentasi:** registrasi (petani menunggu aktivasi admin), lupa dan ubah password, login dan pengalihan ke dashboard sesuai peran, bagaimana `RoleMiddleware` menolak akses lintas peran, dan kenapa petani yang dinonaktifkan produknya hilang dari katalog.

## Anggota 2 — Produk & Katalog
**Tabel:** `categories`, `products`
- **Model:** `Category`, `Product`
- **Migration:** `create_categories_table`, `create_products_table`
- **Controller:** `CatalogController`, `Petani/ProductController`, `Admin/CategoryController`
- **View:** `catalog/*`, `petani/products/*`, `admin/categories/*`, komponen `product-card`
- **Seeder:** `CategorySeeder`, `ProductSeeder`
- **Test:** `ProdukPetaniTest`, `KategoriTest`

**Yang dijelaskan saat presentasi:** CRUD produk dengan upload foto, slug otomatis dan unik, pencarian dan filter di katalog, petani hanya bisa mengubah produk miliknya, kategori yang sedang dipakai tidak bisa dihapus.

## Anggota 3 — Belanja (sisi pembeli)
**Tabel:** `carts`, `orders`, `order_items`
- **Model:** `Cart`, `Order`, `OrderItem`
- **Migration:** `create_orders_table`, `create_order_items_table`, `create_carts_table`
- **Controller:** `Pembeli/CartController`, `Pembeli/CheckoutController`, `Pembeli/OrderController`, `Pembeli/DashboardController`
- **View:** `pembeli/*`
- **Config:** `config/agritani.php` (ongkir)
- **Test:** `KatalogKeranjangTest`, `CheckoutPesananTest`, `BuktiBayarTest`

**Yang dijelaskan saat presentasi:** alur keranjang sampai checkout, pesanan dipecah per petani, stok berkurang dalam satu transaksi database (gagal satu, batal semua), pembatalan mengembalikan stok, upload bukti transfer.

## Anggota 4 — Pesanan Masuk (sisi petani)
**Tabel:** `payments` (plus kolom `courier`, `tracking_number`, `shipped_at` di `orders`)
- **Model:** `Payment`
- **Migration:** `create_payments_table`
- **Controller:** `Petani/OrderController`, `Petani/PaymentController`, `Petani/ShipmentController`, `Petani/DashboardController`
- **View:** `petani/orders/*`, `petani/payments/*`, `petani/shipments/*`, `petani/dashboard`, komponen `stat-card`
- **Seeder:** `DemoSeeder` (pesanan contoh untuk dashboard)
- **Test:** `PesananPetaniTest`, `PembayaranTest`, `PengirimanTest`, `DashboardTest`

**Yang dijelaskan saat presentasi:** petani hanya melihat pesanan produk miliknya, transfer harus dikonfirmasi sebelum pesanan diproses, perubahan status `pending → diproses → dikirim`, pengisian resi, dan statistik dashboard.

---

## File inti (ubah hanya setelah disepakati bersama)
`routes/web.php`, `app/Http/Middleware/RoleMiddleware.php`, `resources/views/layouts/*` (termasuk `sidebar`), komponen `badge`, `resources/css/app.css`, `DatabaseSeeder`.

## Titik singgung antar modul (koordinasi sebelum mengubah)
| Yang dipakai | Milik | Dipakai oleh |
|---|---|---|
| `Product::active()` dan `isPurchasable()` | Anggota 2 | Anggota 3 |
| Halaman detail produk (`catalog/show`) memuat tombol keranjang | Anggota 2 | Anggota 3 |
| Checkout membuat `Payment` | Anggota 3 | Anggota 4 |
| Upload bukti (`Pembeli/OrderController::uploadProof`) | Anggota 3 | Anggota 4 (yang mengonfirmasi) |
| `Order::forFarmer()`, `grandTotal()` | Anggota 3 | Anggota 1 dan 4 |
| Status pesanan `dikirim` membuka tombol "Pesanan Diterima" | Anggota 4 | Anggota 3 |
| Petani nonaktif menyembunyikan produknya | Anggota 1 | Anggota 2 dan 3 |
