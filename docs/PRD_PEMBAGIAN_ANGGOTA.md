# PRD Pembagian Anggota — Tanimart

Dokumen ini adalah peta kepemilikan kode per anggota. Ditulis untuk anggota tim **dan** untuk agent AI (Claude Code, dll.) yang bekerja di repo ini, supaya setiap perubahan bisa dikaitkan dengan anggota yang bertanggung jawab.

- **Produk:** Tanimart, marketplace hasil tani. Petani menjual langsung ke pembeli; admin mengelola akun, kategori, dan memantau pesanan.
- **Mata kuliah:** Pemrograman Berbasis Website, Informatika, Fakultas Ilmu Komputer, Universitas Jember, Gasal 2026/2027. Tugas akhir kelompok 4 orang.
- **Stack:** Laravel 12, PHP 8.3, MySQL; Bootstrap 5.3 dan jQuery 3.7 disajikan lokal dari `public/vendor` (tanpa Node/npm/build). Test: PHPUnit dengan SQLite in-memory.
- **Peran pengguna:** `admin`, `petani`, `pembeli`. Akses dibatasi `RoleMiddleware` lewat `role:<peran>` di route.

## Ringkasan pembagian

| Anggota | Modul | File route | Folder test | Tabel utama |
|---|---|---|---|---|
| 1 | Akun & Admin | `routes/modules/1-akun-admin.php` | `tests/Feature/AkunAdmin` | `users` |
| 2 | Produk & Katalog | `routes/modules/2-produk.php` | `tests/Feature/Produk` | `categories`, `products` |
| 3 | Belanja (sisi pembeli) | `routes/modules/3-belanja.php` | `tests/Feature/Belanja` | `carts`, `orders`, `order_items` |
| 4 | Pesanan Masuk & Laporan (sisi petani) | `routes/modules/4-pesanan-masuk.php` | `tests/Feature/PesananMasuk` | `payments` (+ kolom pengiriman di `orders`) |

Urutan ketergantungan (foreign key): **1 → 2 → 3 → 4**. Laporan penjualan dipakai bersama anggota 1 (admin) dan 4 (petani), test-nya di `tests/Feature/Laporan`.

---

## Anggota 1 — Akun & Admin

**Fitur**
- Register pembeli dan petani. Pembeli langsung aktif; petani dibuat **nonaktif** dan baru bisa login/berjualan setelah diaktifkan admin. Petani wajib mengisi rekening bank (`bank_name`, `bank_account_number`, `bank_account_name`).
- Login (tanpa "Ingat saya"), logout, pengalihan ke dashboard sesuai peran.
- Profil: ubah nama, email, no. HP, alamat; petani juga ubah rekening. Ubah password. (Foto profil sudah dihapus.)
- Lupa password lewat admin: halaman lupa password hanya berisi tombol WhatsApp ke admin (`wa.me/<TANIMART_ADMIN_WHATSAPP>`, email terisi otomatis lewat jQuery). Admin menekan Reset Password di Kelola Pengguna, mendapat password sementara `Tani-XXXX9999`, dan pengguna wajib menggantinya (`must_change_password` + middleware `EnsurePasswordChanged`). Route reset via email masih ada di backend tetapi tidak ditautkan.
- Admin: dashboard (angka ringkas, grafik pesanan 7 hari, pesanan terbaru), daftar petani (aktifkan/nonaktifkan), kelola pengguna (cari, filter peran, reset password), daftar pesanan (hanya lihat), laporan penjualan admin.

**File**
- Controller: `Auth/AuthController`, `Auth/PasswordResetController`, `ProfileController`, `Admin/DashboardController`, `Admin/FarmerController`, `Admin/UserController`, `Admin/OrderController`, `Admin/ReportController`
- Middleware: `RoleMiddleware`, `EnsurePasswordChanged` (didaftarkan di `bootstrap/app.php`)
- Model: `User` (konstanta peran, `hasBankAccount()`)
- Migration: `0001_01_01_000000_create_users_table`, `2026_02_01_000001_add_bank_account_to_users_table`, `2026_03_01_000001_add_must_change_password_to_users_table`, `2026_03_02_000001_drop_photo_from_users_table`
- View: `auth/*`, `profile/edit`, `admin/dashboard`, `admin/farmers/*`, `admin/users/*`, `admin/orders/*`, `admin/reports/*`, komponen `password-input`
- Seeder: `UserSeeder` (akun demo `@tanimart.test`)
- Config: `tanimart.admin_whatsapp`
- Test: `AuthTest`, `LupaPasswordTest`, `ProfilTest`, `KelolaPetaniTest`, `ResetOlehAdminTest`, `PesananAdminTest`, `Laporan/LaporanPenjualanTest` (bersama anggota 4)

## Anggota 2 — Produk & Katalog

**Fitur**
- Katalog publik (`/`): pencarian, filter kategori, urut harga, paginasi 12. Hanya produk aktif dari petani aktif (`Product::active()`).
- Detail produk (`/produk/{slug}`) dengan tombol tambah ke keranjang.
- Produk milik petani: tambah, ubah, aktifkan/nonaktifkan. Slug otomatis dan unik. Foto JPG/PNG/WEBP maks. 2 MB dengan preview jQuery. Petani hanya bisa mengubah produk miliknya.
- Kategori (admin): CRUD; kategori yang dipakai produk tidak bisa dihapus.

**File**
- Controller: `CatalogController`, `Petani/ProductController`, `Admin/CategoryController`
- Request: `Requests/ProductRequest`
- Model: `Category`, `Product` (`scopeActive()`, `isPurchasable()`)
- Migration: `2026_01_01_000001_create_categories_table`, `2026_01_01_000002_create_products_table`
- View: `catalog/*`, `petani/products/*`, `admin/categories/*`, komponen `product-card`
- Seeder: `CategorySeeder`, `ProductSeeder`
- JS: bagian `input[type=file][data-preview]` di `public/js/tanimart.js`
- Test: `ProdukPetaniTest`, `KategoriTest`

## Anggota 3 — Belanja (sisi pembeli)

**Fitur**
- Keranjang: tambah, ubah jumlah, hapus; dicek terhadap stok.
- Checkout: pesanan dipecah per petani; ongkir flat per pesanan (`tanimart.shipping_cost`); metode `transfer` atau `cod`. Untuk transfer, rekening petani ditampilkan. Stok dikurangi dalam satu transaksi database (gagal satu, batal semua).
- Pesanan saya: daftar dengan filter status, detail, upload bukti transfer, batalkan (stok dikembalikan; pembayaran COD jadi `ditolak`), tombol "Pesanan Diterima" saat status `dikirim`.
- Dashboard pembeli.

**File**
- Controller: `Pembeli/CartController`, `Pembeli/CheckoutController`, `Pembeli/OrderController`, `Pembeli/DashboardController`
- Model: `Cart`, `Order` (`forFarmer()`, `grandTotal()`, `markCompleted()`), `OrderItem`
- Migration: `2026_01_01_000003_create_orders_table`, `2026_01_01_000004_create_order_items_table`, `2026_01_01_000006_create_carts_table`
- View: `pembeli/*`
- Config: `tanimart.shipping_cost`
- JS: tampilkan info rekening saat memilih transfer (`#transfer-info`)
- Test: `KatalogKeranjangTest`, `CheckoutPesananTest`, `BuktiBayarTest`

## Anggota 4 — Pesanan Masuk & Laporan (sisi petani)

**Fitur**
- Pesanan masuk: petani hanya melihat pesanan produknya; konfirmasi atau tolak (tolak: stok kembali, pembayaran COD jadi `ditolak`). Transfer harus dikonfirmasi dulu sebelum pesanan diproses.
- Pembayaran: konfirmasi/tolak bukti transfer. Pembayaran COD tidak dikonfirmasi manual; mengikuti status pesanan.
- Pengiriman: isi kurir dan nomor resi, status `diproses → dikirim`, `shipped_at` dicatat.
- Selesai otomatis: perintah `orders:auto-complete` (dijadwalkan tiap jam di `routes/console.php`) menyelesaikan pesanan `dikirim` setelah `tanimart.auto_complete_days` hari (default 7). Butuh `php artisan schedule:work` agar berjalan.
- Dashboard petani (pesanan menunggu konfirmasi, angka ringkas, stok menipis) dan laporan penjualan petani (filter periode/status, produk terlaris, CSV, cetak).

**File**
- Controller: `Petani/OrderController`, `Petani/PaymentController`, `Petani/ShipmentController`, `Petani/DashboardController`, `Petani/ReportController`
- Logika bersama laporan: `Controllers/Concerns/BuildsSalesReport` (dipakai juga `Admin/ReportController`)
- Command: `app/Console/Commands/AutoCompleteOrders.php`, jadwal di `routes/console.php`
- Model: `Payment`; di `Order`: `autoCompleteAt()`, `scopeReadyToAutoComplete()`
- Migration: `2026_01_01_000005_create_payments_table`
- View: `petani/dashboard`, `petani/orders/*`, `petani/payments/*`, `petani/shipments/*`, `petani/reports/*`, `reports/_sales`, komponen `stat-card`
- Seeder: `DemoSeeder` (pesanan contoh)
- Config: `tanimart.auto_complete_days`
- Test: `PesananPetaniTest`, `PembayaranTest`, `PengirimanTest`, `DashboardTest`, `SelesaiOtomatisTest`, `Laporan/LaporanPenjualanTest` (bersama anggota 1)

---

## Aturan status (lintas modul)

| Status pesanan | Diubah oleh | Efek ke pembayaran COD |
|---|---|---|
| `pending` | Checkout (anggota 3) | `menunggu` |
| `diproses` | Petani konfirmasi (anggota 4) | tetap `menunggu` |
| `dikirim` | Petani isi resi (anggota 4) | tetap `menunggu` |
| `selesai` | Pembeli tekan "Pesanan Diterima" (anggota 3) atau otomatis setelah 7 hari (anggota 4) | `dikonfirmasi` (lewat `Order::markCompleted()`) |
| `dibatalkan` | Pembeli batal (anggota 3) atau petani tolak (anggota 4) | `ditolak` |

Status pembayaran: `menunggu`, `dikonfirmasi`, `ditolak`. Metode: `transfer`, `cod`.

## Titik singgung antar anggota (koordinasi sebelum mengubah)

| Yang dipakai | Pemilik | Dipakai oleh |
|---|---|---|
| `Product::active()`, `isPurchasable()` | 2 | 3 |
| Tombol keranjang di `catalog/show` | 2 | 3 |
| Checkout membuat `Payment` | 3 | 4 |
| Upload bukti (`Pembeli/OrderController::uploadProof`) | 3 | 4 (yang mengonfirmasi) |
| `Order::forFarmer()`, `grandTotal()`, `markCompleted()` | 3 | 1 dan 4 |
| Status `dikirim` membuka tombol "Pesanan Diterima" | 4 | 3 |
| Rekening bank petani ditampilkan di checkout dan detail pesanan pembeli | 1 | 3 |
| Petani nonaktif menyembunyikan produknya | 1 | 2 dan 3 |
| `BuildsSalesReport` dan `reports/_sales` | 4 | 1 |

## File inti (milik bersama — ubah hanya setelah disepakati)

`routes/web.php`, `bootstrap/app.php`, `app/Http/Middleware/RoleMiddleware.php`, `resources/views/layouts/*` (termasuk `partials/sidebar`, `alerts`, `head`, `scripts`), komponen `badge` dan `orders-table`, `public/css/tanimart.css`, `public/js/tanimart.js`, `config/tanimart.php`, `database/seeders/DatabaseSeeder.php`, `tests/Feature/Core/*`.

---

## Petunjuk untuk agent AI di repo ini

1. **Sebutkan pemilik.** Setiap kali menjelaskan, mengusulkan, atau membuat perubahan, sebutkan file yang diubah dan anggota pemiliknya (1–4 atau "file inti"), berdasarkan peta di atas.
2. **Jaga batas modul.** Route baru masuk ke file `routes/modules/` milik anggota yang bersangkutan, test baru ke folder test anggota itu. Jika perubahan menyentuh baris di tabel "Titik singgung" atau "File inti", sebutkan bahwa perlu koordinasi.
3. **Jangan ubah migration lama.** Database anggota sudah menjalankannya; perubahan skema selalu lewat migration baru, dan ingatkan bahwa anggota lain perlu `php artisan migrate` setelah pull.
4. **Rahasia tetap di `.env`.** Nomor WhatsApp admin dan kredensial tidak boleh ditulis di kode atau di-commit. `.env` tidak pernah di-commit.
5. **Jalankan test** (`php artisan test`, atau `php artisan test tests/Feature/<Folder>` per modul) sebelum menyatakan selesai.
6. **Bahasa:** komunikasi dan teks antarmuka dalam Bahasa Indonesia.
7. **Commit/push hanya bila diminta.**

## Dokumen terkait

- `PEMBAGIAN_TUGAS.md` — ringkasan pembagian dan materi presentasi per anggota.
- `docs/PANDUAN_BELAJAR.md` — panduan belajar per anggota dengan nomor baris.
- `docs/PRD_Tanimart.md` — PRD lengkap dan alur sistem.
- `docs/erd/tanimart_erd.sql`, `docs/erd/tanimart_erd.dbml` — skema database untuk ERD.
