# AgriTani

Marketplace hasil pertanian berbasis Laravel 12 dengan tiga peran: **Admin**, **Petani**, dan **Pembeli**.
Pembagian tugas tim ada di [PEMBAGIAN_TUGAS.md](PEMBAGIAN_TUGAS.md).

## Kebutuhan
- PHP 8.3+ dan Composer
- Node.js 20+ dan npm
- MySQL (disarankan Laragon di Windows)

## Setup

```bash
# 1. Clone dan masuk ke folder proyek
git clone https://github.com/mochammadilham55/agritani-example.git
cd agritani-example

# 2. Pasang dependensi
composer install
npm install

# 3. Siapkan environment
cp .env.example .env
php artisan key:generate
```

4. Buat database kosong di MySQL, misalnya `agritani`, lalu ubah bagian database di `.env`
   (secara bawaan `.env.example` memakai SQLite, jadi baris ini harus diganti):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=agritani
DB_USERNAME=root
DB_PASSWORD=
```

5. Jalankan migration, seeder, dan tautan storage:

```bash
php artisan migrate --seed
php artisan storage:link
```

6. Jalankan aplikasi (dua terminal):

```bash
php artisan serve
npm run dev
```

Buka http://127.0.0.1:8000. Di Laragon, aplikasi juga bisa diakses lewat virtual host-nya.

`storage:link` wajib dijalankan supaya foto produk dan bukti transfer tampil.

## Akun uji
Semua akun memakai password `password`.

| Peran | Email |
|---|---|
| Admin | admin@agritani.test |
| Petani | petani@agritani.test |
| Petani 2 | petani2@agritani.test |
| Pembeli | pembeli@agritani.test |

Hanya untuk pengembangan lokal. Ganti atau hapus akun ini sebelum aplikasi dipasang di server sungguhan.

## Data contoh (opsional)
Untuk mengisi dashboard dan laporan dengan pesanan, pembayaran, pengiriman, dan ulasan contoh:

```bash
php artisan db:seed --class=DemoSeeder
```

Seeder ini aman diulang dan dilewati jika pesanan sudah ada.

## Menjalankan test
Test memakai SQLite in-memory, jadi tidak menyentuh database MySQL Anda.

```bash
php artisan test                           # semua modul
php artisan test tests/Feature/Ulasan      # satu modul saja
```

Folder test: `Core`, `PetaniProduk`, `Pesanan`, `PembayaranPengiriman`, `Laporan`, `Ulasan`.

## Konfigurasi
| Variabel `.env` | Fungsi | Bawaan |
|---|---|---|
| `AGRITANI_SHIPPING_COST` | Ongkos kirim tetap per pesanan (Rp) | 10000 |

## Struktur route
`routes/web.php` hanya memuat login, register, dan logout. Route fitur dipisah per modul di `routes/modules/`:
`1-petani-produk.php`, `2-pesanan.php`, `3-pembayaran-pengiriman.php`, `4-laporan.php`, `4-ulasan.php`.

## Lisensi
Proyek ini dibangun di atas [Laravel](https://laravel.com), yang berlisensi [MIT](https://opensource.org/licenses/MIT).
