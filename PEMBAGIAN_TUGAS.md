# Pembagian Tugas AgriTani (4 Anggota)

Setiap anggota punya file route sendiri di `routes/modules/` dan folder test sendiri di `tests/Feature/`.
Jalankan test modul sendiri: `php artisan test tests/Feature/<Folder>`.

| Anggota | Modul | Route | Folder test |
|---|---|---|---|
| 1 | Petani & Produk | `routes/modules/1-petani-produk.php` | `tests/Feature/PetaniProduk` |
| 2 | Pesanan | `routes/modules/2-pesanan.php` | `tests/Feature/Pesanan` |
| 3 | Pembayaran & Pengiriman | `routes/modules/3-pembayaran-pengiriman.php` | `tests/Feature/PembayaranPengiriman` |
| 4 | Laporan, Dashboard & **Ulasan** | `routes/modules/4-laporan.php`, `4-ulasan.php` | `tests/Feature/Laporan`, `tests/Feature/Ulasan` |

Nama tabel di spesifikasi → tabel di proyek: petani/pembeli → `users` (kolom `role`), produk → `products`,
kategori_produk → `categories`, pesanan → `orders`, detail_pesanan → `order_items`,
pengiriman → `shipments`, pembayaran → `payments`.

---

## Anggota 1 — Petani & Produk
- **Model:** `User` (sisi petani), `Product`, `Category`
- **Migration:** `create_products_table`, `create_categories_table`, `add_suspension_to_products_table`
- **Controller:** `Petani/ProductController`, `Admin/FarmerController`, `Admin/ProductController`, `Admin/CategoryController`, `ProfileController`
- **View:** `petani/products/*`, `admin/farmers/*`, `admin/products/*`, `admin/categories/*`, `profile/edit`
- **Seeder:** `CategorySeeder`, `ProductSeeder`
- **Penjaga file inti** (lihat bagian bawah)

## Anggota 2 — Pesanan
- **Model:** `Order`, `OrderItem`, `Cart`
- **Migration:** `create_orders_table`, `create_order_items_table`, `create_carts_table`
- **Controller:** `CatalogController`, `Pembeli/CartController`, `Pembeli/CheckoutController`, `Pembeli/OrderController`, `Pembeli/DashboardController`, `Petani/OrderController`, `Admin/OrderController`
- **View:** `catalog/*`, `pembeli/cart`, `pembeli/checkout`, `pembeli/orders/*`, `pembeli/dashboard`, `petani/orders/*`, `admin/orders/*`, `layouts/public`, komponen `product-card` & `orders-table`
- **Config:** `config/agritani.php` (ongkir)

## Anggota 3 — Pembayaran & Pengiriman
- **Model:** `Payment`, `Shipment`
- **Migration:** `create_payments_table`, `create_shipments_table`
- **Controller:** `Petani/PaymentController`, `Petani/ShipmentController`
- **View:** `petani/payments/*`, `petani/shipments/*`
- **Bersama Anggota 2:** method `uploadProof` di `Pembeli/OrderController` (kode di modul 2, aturan bayar milik modul 3)

## Anggota 4 — Laporan, Dashboard, dan SEMUA fitur Ulasan
- **Model:** `Review`
- **Migration:** `create_reviews_table`
- **Controller:** `Pembeli/ReviewController`, `Admin/ReviewController`, `Petani/ReportController`, `Admin/ReportController`, `Petani/DashboardController`, `Admin/DashboardController`, trait `Concerns/HandlesReports`
- **View:** `pembeli/reviews/*`, `admin/reviews/*`, `petani/reports/*`, `admin/reports/*`, `petani/dashboard`, `admin/dashboard`, komponen `stat-card`
- **Seeder:** `DemoSeeder` (pesanan, pembayaran, ulasan contoh)
- **Fitur ulasan:** pembeli menulis/mengedit ulasan (hanya setelah pesanan Selesai), ulasan baru berstatus menunggu moderasi, admin menyetujui/menolak, ulasan yang disetujui tampil di halaman produk dan masuk hitungan rating.
- **Belum ada (tugas):** export laporan ke PDF/print, dan pilihan laporan per bulan.

---

## Titik singgung antar modul (koordinasi sebelum mengubah)
| Yang dipakai | Milik | Dipakai oleh |
|---|---|---|
| `catalog/show.blade.php` & `CatalogController::show` menampilkan ulasan disetujui | Anggota 2 | Anggota 4 |
| `Product::reviews()` & `Product::averageRating()` | Anggota 1 (model Product) | Anggota 4 |
| Status pesanan `selesai` membuka form ulasan | Anggota 2 | Anggota 4 |
| `Order::forFarmer()`, `grandTotal()` | Anggota 2 | Anggota 3, 4 |
| `Product::active()` / `isPurchasable()` | Anggota 1 | Anggota 2 |

## File inti (dipakai bersama — perubahan lewat pull request, direview Anggota 1)
`routes/web.php`, `layouts/app`, `layouts/guest`, `layouts/partials/sidebar`, komponen `badge`,
`RoleMiddleware`, `AuthController`, `UserSeeder`, migration `users`, `resources/css/app.css`, test di `tests/Feature/Core`.
Saat menambah menu sidebar atau route baru, tambahkan di file modul sendiri; sidebar hanya perlu satu baris di `sidebar.blade.php`.

## Aturan kerja
1. Satu branch per fitur, merge lewat pull request.
2. Jangan mengubah file modul anggota lain tanpa memberi tahu pemiliknya.
3. Sebelum push: `php artisan test` harus hijau (semua modul).
4. Migration baru diberi nama bertanggal; jangan mengedit migration yang sudah di-merge.
