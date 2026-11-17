# PRD — AgriTani (Marketplace Hasil Pertanian)

Dokumen ini dibuat dari kode repository `mochammadilham55/agritani-example` (branch `main`). Isinya menggambarkan produk **sebagaimana sudah diimplementasikan**, bukan rencana. Fakta di sini bisa dicek ke file yang disebutkan di tiap bagian.

> **Petunjuk untuk AI agent yang memakai dokumen ini.** Gunakan dokumen ini sebagai satu-satunya sumber fakta tentang proyek. Jangan menambah fitur, angka, atau statistik yang tidak tertulis di sini. Bagian yang belum ada (nama anggota, NIM, logo, referensi ilmiah) harus ditandai sebagai placeholder, bukan dikarang. Susunan laporan yang diminta dosen ada di bagian 15.

---

## 1. Ringkasan

| Item | Isi |
| --- | --- |
| Nama produk | AgriTani |
| Jenis | Aplikasi web marketplace hasil pertanian |
| Konteks | Project akhir matakuliah Pemrograman Berbasis Website, semester Gasal 2026/2027, Informatika, Fakultas Ilmu Komputer, Universitas Jember |
| Tim | 4 mahasiswa, tiap anggota memegang satu modul |
| Stack | Laravel 12, PHP 8.3, MySQL, Blade, Bootstrap 5.3, jQuery 3.7 |
| Status | Semua fitur di dokumen ini sudah berjalan. 83 tes otomatis lolos |

**Masalah yang diselesaikan.** Petani kecil umumnya menjual lewat tengkulak atau pasar dan mencatat stok serta pesanan secara manual. Pembeli sulit membandingkan produk dan harga dalam satu tempat. AgriTani memberi petani etalase digital dan alat kelola pesanan, serta memberi pembeli katalog, keranjang, pembayaran, dan pelacakan pesanan.

**Rumusan masalah proyek.**
1. Bagaimana petani memasarkan produk dan mengelola stok serta pesanan masuk secara online?
2. Bagaimana pembeli mencari produk, berbelanja, membayar, dan memantau pesanan?
3. Bagaimana admin memverifikasi petani, mengelola kategori, dan memantau seluruh transaksi?
4. Bagaimana data penjualan disajikan sebagai laporan yang bisa difilter, dicetak, dan diunduh?
5. Bagaimana akses tiap peran dibatasi agar pengguna hanya mengelola datanya sendiri?

## 2. Tujuan dan non-tujuan

**Tujuan**
- Menyediakan alur belanja lengkap: katalog, keranjang, checkout, pembayaran, pengiriman, penerimaan.
- Memisahkan hak akses tiga peran dengan middleware.
- Menyediakan laporan penjualan untuk petani dan admin.
- Menerapkan seluruh materi matakuliah dan dapat dijelaskan oleh tiap anggota.

**Non-tujuan (belum dibuat)**
- Payment gateway. Pembayaran transfer bersifat manual (unggah bukti, petani konfirmasi) dan COD.
- Ongkos kirim dinamis. Ongkir tetap per pesanan (`AGRITANI_SHIPPING_COST`, bawaan Rp 10.000).
- Ulasan dan rating, chat, notifikasi email atau WhatsApp nyata (email lupa password memakai mailer `log` saat pengembangan).
- Deploy ke server produksi. Aplikasi dijalankan lokal.
- Laporan selain laporan penjualan. Tidak ada ekspor PDF.

## 3. Pengguna (persona)

| Peran | Cara mendapat akun | Tujuan |
| --- | --- | --- |
| Pembeli | Daftar sendiri, langsung aktif | Mencari produk, berbelanja, membayar, memantau pesanan |
| Petani | Daftar sendiri, `is_active = false` sampai admin memverifikasi | Menjual produk, memproses pesanan, mengonfirmasi pembayaran, mengisi resi, melihat laporan |
| Admin | Dibuat lewat seeder | Verifikasi dan tangguhkan petani, kelola kategori, pantau semua pesanan dan laporan |

Pengunjung tanpa login hanya dapat melihat katalog dan detail produk.

## 4. Alur utama (end-to-end)

1. Petani mendaftar, admin mengaktifkan akunnya.
2. Petani menambahkan produk (kategori, harga, stok, satuan, berat, foto).
3. Pembeli mencari produk di katalog dan menambahkannya ke keranjang.
4. Checkout: pembeli mengisi alamat dan memilih transfer atau COD. Keranjang dipecah menjadi **satu pesanan per petani**. Stok berkurang dan pembayaran berstatus `menunggu`.
5. Transfer: pembeli mengunggah bukti, petani mengonfirmasi atau menolak pembayaran. COD: bisa langsung diproses.
6. Petani mengonfirmasi pesanan → status `diproses`.
7. Petani mengisi kurir dan nomor resi → status `dikirim`.
8. Pembeli menekan Pesanan Diterima → status `selesai`.
9. Selama `pending`, pembeli dapat membatalkan atau petani dapat menolak → status `dibatalkan`, stok dikembalikan.
10. Petani dan admin melihat dashboard dan laporan penjualan.

**Status pesanan:** `pending → diproses → dikirim → selesai`, dan `pending → dibatalkan`.
**Status pembayaran:** `menunggu → dikonfirmasi | ditolak`. Metode: `transfer`, `cod`.

## 5. Kebutuhan fungsional

Kolom "Modul" menunjuk anggota pemilik fitur (lihat bagian 12).

### 5.1 Akun dan hak akses (Modul 1)
| ID | Kebutuhan | Kriteria penerimaan |
| --- | --- | --- |
| FR-1.1 | Register pembeli atau petani | Validasi nama, email unik, password min. 8 karakter dan konfirmasi. Petani baru tidak aktif dan wajib mengisi rekening bank |
| FR-1.2 | Login dan logout | Pengguna dialihkan ke dashboard sesuai peran. Akun ditangguhkan di-logout dengan pesan |
| FR-1.3 | Lupa password | Tautan reset dikirim ke email (dicatat di log saat pengembangan). Pengiriman dibatasi `throttle:6,1` |
| FR-1.4 | Ubah password di profil | Wajib mengisi password saat ini |
| FR-1.5 | Profil | Ubah nama, email, telepon, alamat, foto. Petani juga mengisi rekening (nama bank, nomor 6-30 digit, atas nama) |
| FR-1.6 | Tombol lihat password | Menampilkan atau menyembunyikan isi input (jQuery) |
| FR-1.7 | Pembatasan peran | Akses lintas peran menghasilkan 403. Belum login diarahkan ke login |
| FR-1.8 | Kelola petani (admin) | Daftar dengan pencarian dan filter status. Verifikasi atau tangguhkan. Petani ditangguhkan: produknya hilang dari katalog |
| FR-1.9 | Daftar dan detail semua pesanan (admin) | Pencarian dan filter status |
| FR-1.10 | Dashboard admin | Total pengguna, produk aktif, pesanan hari ini, pendapatan, grafik 7 hari |
| FR-1.11 | Laporan penjualan admin | Lihat FR-4.7 |

### 5.2 Produk dan katalog (Modul 2)
| ID | Kebutuhan | Kriteria penerimaan |
| --- | --- | --- |
| FR-2.1 | Katalog publik | Hanya produk aktif milik petani aktif. Pencarian nama, filter kategori, urut harga, pagination |
| FR-2.2 | Detail produk | Harga, stok, berat, deskripsi, petani, produk serupa. Stok 0 tidak bisa dibeli |
| FR-2.3 | CRUD produk petani | Tambah, ubah, aktifkan atau nonaktifkan (tanpa hapus). Slug otomatis dan unik. Foto maks. 2 MB (jpg, png, webp) |
| FR-2.4 | Kepemilikan | Petani hanya bisa membuka dan mengubah produk miliknya, selain itu 404 |
| FR-2.5 | Validasi produk | Lewat `ProductRequest` dengan pesan error bahasa Indonesia |
| FR-2.6 | Preview foto | Pratinjau gambar sebelum diunggah (jQuery) |
| FR-2.7 | CRUD kategori (admin) | Kategori yang masih dipakai produk tidak bisa dihapus |

### 5.3 Belanja sisi pembeli (Modul 3)
| ID | Kebutuhan | Kriteria penerimaan |
| --- | --- | --- |
| FR-3.1 | Keranjang | Tambah, ubah jumlah, hapus. Satu baris per produk per pengguna. Penanda produk tidak tersedia atau stok kurang |
| FR-3.2 | Checkout | Alamat wajib (10–500 karakter), metode transfer atau COD, catatan opsional. Memilih transfer menampilkan rekening tiap petani (juga di detail pesanan) |
| FR-3.3 | Pesanan dipecah per petani | Ongkir dikenakan per pesanan |
| FR-3.4 | Stok atomik | Seluruh checkout dalam satu transaksi database dengan penguncian baris produk. Satu produk kurang membatalkan semua |
| FR-3.5 | Harga dibekukan | Harga disalin ke `order_items` saat checkout |
| FR-3.6 | Daftar dan detail pesanan | Filter status, timeline status, rincian, resi |
| FR-3.7 | Upload bukti transfer | Hanya untuk transfer berstatus `pending` yang belum dikonfirmasi. Bisa diunggah ulang jika ditolak |
| FR-3.8 | Batalkan pesanan | Hanya saat `pending`. Stok dikembalikan |
| FR-3.9 | Pesanan diterima | Hanya saat `dikirim`, status menjadi `selesai` |
| FR-3.10 | Dashboard pembeli | Pesanan aktif, selesai, total belanja, riwayat terbaru |

### 5.4 Pesanan masuk dan laporan sisi petani (Modul 4)
| ID | Kebutuhan | Kriteria penerimaan |
| --- | --- | --- |
| FR-4.1 | Pesanan masuk | Hanya pesanan yang berisi produk milik petani, filter status |
| FR-4.2 | Konfirmasi pesanan | Transfer harus sudah `dikonfirmasi`. COD boleh langsung. Status → `diproses` |
| FR-4.3 | Tolak pesanan | Saat `pending`. Stok dikembalikan, status → `dibatalkan` |
| FR-4.4 | Konfirmasi atau tolak pembayaran | Hanya jika COD atau bukti sudah ada |
| FR-4.5 | Pengiriman | Isi kurir dan nomor resi, status → `dikirim`, `shipped_at` terisi |
| FR-4.6 | Dashboard petani | Jumlah produk, terjual, pendapatan, pesanan baru, stok menipis |
| FR-4.7 | **Laporan penjualan** | Filter tanggal (`dari`, `sampai`) dan status. Ringkasan: jumlah pesanan, selesai, dibatalkan, pendapatan (dari pesanan selesai). Rincian pesanan, 5 produk terlaris, pencarian tabel langsung (jQuery), unduh CSV, cetak. Petani melihat hanya miliknya, admin melihat semua. Validasi: `sampai` tidak boleh sebelum `dari` |

## 6. Aturan bisnis
- Satu pesanan hanya berisi produk dari satu petani.
- Total dibayar = `total_price` (subtotal barang) + `shipping_cost`. `total_price` tidak termasuk ongkir.
- Pendapatan dihitung dari pesanan `selesai`. Untuk petani, hanya subtotal item miliknya.
- Petani nonaktif menyembunyikan produknya dari katalog dan keranjang tidak bisa dibeli.
- Ekspor CSV memakai pemisah titik koma dan baris `sep=;` agar terbaca per kolom di Excel regional Indonesia.

## 7. Model data

7 tabel utama. Tabel bawaan Laravel (`sessions`, `cache`, `jobs`, `password_reset_tokens`) tidak dibahas.

| Tabel | Kolom penting | Relasi |
| --- | --- | --- |
| `users` | id, name, email (unik), password, role (`admin`/`petani`/`pembeli`), phone, address, bank_name, bank_account_number, bank_account_name (rekening petani), photo, is_active | hasMany products, orders, carts |
| `categories` | id, name, slug (unik), description, image | hasMany products |
| `products` | id, user_id (FK, petani), category_id (FK), name, slug (unik), description, price, stock, unit, weight (gram), image, is_active | belongsTo farmer, category; hasMany order_items, carts |
| `carts` | id, user_id (FK), product_id (FK), quantity. Unik (user_id, product_id) | belongsTo user, product |
| `orders` | id, user_id (FK, pembeli), total_price, status, shipping_address, shipping_cost, notes, courier, tracking_number, shipped_at | belongsTo buyer; hasMany items; hasOne payment |
| `order_items` | id, order_id (FK), product_id (FK), quantity, price, subtotal | belongsTo order, product |
| `payments` | id, order_id (FK), amount, method, status, proof_image, confirmed_at | belongsTo order |

Aturan hapus: `products.user_id` dan `carts` cascade, `products.category_id` dan `order_items.product_id` restrict, `order_items.order_id` dan `payments.order_id` cascade.

## 8. Peta route (ringkas)

| Peran | Prefix | Fitur |
| --- | --- | --- |
| Publik | `/`, `/produk/{slug}` | Katalog, detail produk |
| Tamu | `/login`, `/register`, `/lupa-password`, `/reset-password` | Autentikasi dan reset password |
| Semua peran login | `/profil`, `/profil/password` | Profil dan ubah password |
| `role:pembeli` | `/pembeli/...` | dashboard, keranjang, checkout, pesanan (bukti, batal, selesai) |
| `role:petani` | `/petani/...` | dashboard, produk (CRUD, toggle), pesanan, pembayaran, pengiriman, laporan |
| `role:admin` | `/admin/...` | dashboard, petani, kategori (CRUD), pesanan, laporan |

File route: `routes/web.php` (autentikasi) dan `routes/modules/1-akun-admin.php` sampai `4-pesanan-masuk.php`.

## 9. Arsitektur
Pola MVC Laravel. Alur request: Route → Middleware (`auth`, `guest`, `role`, `throttle`) → Controller (+ Form Request) → Model Eloquent → View Blade atau redirect/respons CSV.

| Lapisan | Lokasi |
| --- | --- |
| Controller per peran | `app/Http/Controllers/{Admin,Petani,Pembeli,Auth}` |
| Logika laporan bersama | `app/Http/Controllers/Concerns/BuildsSalesReport.php` |
| Form Request | `app/Http/Requests/ProductRequest.php` |
| Middleware peran | `app/Http/Middleware/RoleMiddleware.php` |
| Model | `app/Models` (User, Category, Product, Cart, Order, OrderItem, Payment) |
| View | `resources/views` (layout `app`, `guest`, `public`; komponen `x-badge`, `x-stat-card`, `x-product-card`, `x-password-input`, `x-orders-table`) |
| Aset | `public/vendor/bootstrap`, `public/vendor/jquery`, `public/css/agritani.css`, `public/js/agritani.js` |
| Konfigurasi | `config/agritani.php` (ongkir) |

Tidak memakai Node.js, npm, atau Vite. Bootstrap dan jQuery disimpan lokal.

## 10. Kebutuhan non-fungsional
- **Keamanan:** CSRF pada semua form, password di-hash, pembatasan peran dengan middleware, kepemilikan data diperiksa di controller (404 untuk data milik orang lain), `throttle` pada permintaan reset password.
- **Konsistensi data:** checkout dalam transaksi dengan `lockForUpdate`.
- **Responsif:** Bootstrap 5, sidebar menjadi offcanvas di layar kecil.
- **Kemudahan setup:** hanya butuh PHP, Composer, dan MySQL. Jalan offline.
- **Bahasa:** antarmuka dan pesan validasi berbahasa Indonesia.
- **Keterbatasan diketahui:** laporan memuat seluruh baris pada periode terpilih tanpa pagination.

## 11. Pengujian
PHPUnit, 83 tes dan 374 assertion, basis data SQLite in-memory, semua lolos. Dijalankan dengan `php artisan test`.

| Folder tes | Cakupan |
| --- | --- |
| `tests/Feature/AkunAdmin` | Login, register, lupa dan ubah password, profil, peran, kelola petani, pesanan admin |
| `tests/Feature/Produk` | CRUD produk, kepemilikan, kategori |
| `tests/Feature/Belanja` | Katalog, keranjang, checkout, pembatalan, bukti bayar |
| `tests/Feature/PesananMasuk` | Pesanan petani, pembayaran, pengiriman, dashboard |
| `tests/Feature/Laporan` | Laporan petani dan admin, filter, validasi tanggal, CSV, hak akses |
| `tests/Feature/Core` | Komponen bersama |

## 12. Pembagian modul

| Anggota | Modul | Route | Tabel utama | Catatan materi |
| --- | --- | --- | --- | --- |
| 1 | Akun & Admin (termasuk laporan admin) | `1-akun-admin.php` | `users` | `RoleMiddleware`, autentikasi, lupa password |
| 2 | Produk & Katalog | `2-produk.php` | `categories`, `products` | `ProductRequest`, upload foto, preview jQuery |
| 3 | Belanja (sisi pembeli) | `3-belanja.php` | `carts`, `orders`, `order_items` | Transaksi checkout, pecah pesanan |
| 4 | Pesanan Masuk (sisi petani, termasuk laporan petani) | `4-pesanan-masuk.php` | `payments` | Alur pembayaran dan pengiriman, `BuildsSalesReport` |

Detail file per anggota ada di `PEMBAGIAN_TUGAS.md`.

## 13. Pemetaan materi matakuliah

| Materi | Implementasi |
| --- | --- |
| Router dan Middleware | Route per modul, `RoleMiddleware`, `auth`, `guest`, `throttle` |
| Migration dan Seeder | 7 migration. Seeder: `UserSeeder`, `CategorySeeder`, `ProductSeeder`, `DemoSeeder` |
| Model, ORM, Relationship | 7 model, relasi `hasMany`/`belongsTo`/`hasOne`, scope `forFarmer`, `search`, `active` |
| Controller, Request, Response | Controller per peran, `ProductRequest`, redirect dengan flash, respons CSV |
| Blade | 3 layout bersama dan komponen |
| Validation | `$request->validate()` dan Form Request |
| Bootstrap, JavaScript, jQuery | Bootstrap 5, jQuery: lihat password, preview foto, cari tabel, cetak |
| CRUD dan laporan | CRUD produk dan kategori, laporan penjualan |

## 14. Cara menjalankan dan akun uji

```bash
git clone https://github.com/mochammadilham55/agritani-example.git
cd agritani-example
composer install
cp .env.example .env
php artisan key:generate
# buat database MySQL "agritani", lalu:
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Akun uji (password `password`): `admin@agritani.test`, `petani@agritani.test`, `petani2@agritani.test`, `pembeli@agritani.test`.

## 15. Susunan laporan yang diminta dosen
Pengumpulan di Microsoft Teams beserta link GitHub. Isi laporan:
1. **Sampul:** Laporan Project, Matakuliah Pemrograman Berbasis Website, logo UNEJ, nama anggota dan NIM, Prodi Informatika, Fakultas Ilmu Komputer Universitas Jember, tahun ajaran.
2. **Latar belakang dan rumusan masalah** (bagian 1–2 dokumen ini).
3. **Spesifikasi:** fungsi utama, pengguna, fitur (bagian 3–6).
4. **Desain sistem:** Use Case Diagram dan ERD. ERD harus sama dengan migration (bagian 3, 5, 7).
5. **Hasil implementasi:** screenshot tiap halaman beserta penjelasan singkat (bagian 5 dan 8). Screenshot harus diambil dari aplikasi yang berjalan.
6. **Lampiran:** link GitHub `https://github.com/mochammadilham55/agritani-example`.

Struktur yang dipakai kelompok: Bab 1 Pendahuluan (latar belakang, rumusan masalah, tujuan, batasan project), Bab 2 Perancangan Sistem, Bab 3 Implementasi dan Hasil Proyek (materi kuliah, fitur utama, source code, hasil tampilan, pengujian), Bab 4 Kesimpulan, Lampiran.

**Placeholder yang harus diisi manusia:** nama dan NIM anggota, logo UNEJ, screenshot, dan referensi ilmiah (jika diminta).
