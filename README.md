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

## Panduan Git untuk Tim

Panduan ini untuk anggota yang baru memakai Git. Ikuti urutannya, jangan dilompati.

### Pengaturan awal (sekali saja)
```bash
git config --global user.name "Nama Anda"
git config --global user.email "email-github-anda@contoh.com"
```
Pakai email yang sama dengan akun GitHub Anda supaya kontribusi tercatat atas nama Anda.

### Alur kerja harian
```bash
# 1. Ambil perubahan terbaru dari main
git checkout main
git pull

# 2. Buat branch baru untuk satu fitur/perbaikan
git checkout -b fitur/nama-singkat        # contoh: fitur/filter-ulasan

# 3. Kerjakan kode, lalu cek apa yang berubah
git status
git diff

# 4. Simpan perubahan
git add app/Http/Controllers/Pembeli/ReviewController.php   # tambahkan file yang Anda ubah
git commit -m "Tambah filter rating di halaman ulasan"

# 5. Kirim branch Anda (bukan main)
git push -u origin fitur/nama-singkat
```
Setelah itu buka GitHub dan buat **Pull Request** dari branch Anda ke `main`. Minta satu anggota lain untuk me-review sebelum di-merge.

### Wajib sebelum push
1. **Jalankan semua test dan pastikan hijau:** `php artisan test`
2. **Periksa file yang akan dikirim:** `git status`. Pastikan tidak ada `.env`, `vendor/`, `node_modules/`, atau file foto/unggahan.
3. **Pastikan hanya file modul Anda yang berubah.** Kalau perlu mengubah file modul orang lain, beri tahu pemiliknya (lihat [PEMBAGIAN_TUGAS.md](PEMBAGIAN_TUGAS.md)).
4. **Ambil perubahan terbaru dulu:** `git pull origin main`, selesaikan bentrokan jika ada, lalu jalankan test lagi.
5. **Cek pesan commit** supaya jelas dan singkat, dalam Bahasa Indonesia, dengan kata kerja di depan ("Tambah ...", "Perbaiki ...", "Hapus ...").

### Aturan penting
- **Jangan push langsung ke `main`.** Selalu lewat branch dan Pull Request.
- **Satu branch untuk satu fitur.** Jangan mencampur beberapa pekerjaan dalam satu branch.
- **Jangan pernah commit `.env`.** File itu berisi password dan kunci aplikasi. Kalau terlanjur ter-push, beri tahu ketua tim segera karena kuncinya harus diganti.
- **Jangan memakai `git push --force`** dan jangan menulis ulang history yang sudah di-push. Ini bisa menghapus pekerjaan anggota lain.
- **Jangan memakai `git add .` secara buta.** Gunakan `git status` dulu, atau tambahkan file satu per satu.
- **Jangan mengedit migration yang sudah di-merge.** Buat migration baru: `php artisan make:migration nama_perubahan`.
- **Jangan mengubah file inti** (`routes/web.php`, layout, `RoleMiddleware`, `app.css`, dan sejenisnya) tanpa diskusi. Daftarnya ada di `PEMBAGIAN_TUGAS.md`.
- **Jangan menyimpan password, token, atau kunci API** di kode. Taruh di `.env`.
- **Commit sering dan kecil**, lebih baik beberapa commit kecil daripada satu commit besar di akhir.

### Kalau terjadi masalah
| Masalah | Yang dilakukan |
|---|---|
| Salah menulis pesan commit terakhir (belum di-push) | `git commit --amend -m "pesan baru"` |
| Ingin membatalkan perubahan satu file yang belum di-commit | `git restore nama-file` (perubahan di file itu hilang permanen) |
| Terlanjur commit di `main` (belum di-push) | Jangan push. Minta bantuan ketua tim |
| `git pull` menampilkan **CONFLICT** | Buka file yang ditandai, pilih isi yang benar, hapus tanda `<<<<<<<`, `=======`, `>>>>>>>`, lalu `git add` dan `git commit`. Kalau ragu, tanya dulu |
| Bingung dengan kondisi repo | Jalankan `git status` dan `git log --oneline -5`, lalu tanya tim. Jangan menebak dengan `reset --hard` |

### Untuk ketua tim (pengaturan GitHub, sekali saja)
Di GitHub: **Settings → Branches → Add branch ruleset** untuk `main`, lalu aktifkan *Require a pull request before merging* dan *Block force pushes*. Dengan itu aturan "jangan push ke main" ditegakkan otomatis, bukan hanya himbauan.

## Lisensi
Proyek ini dibangun di atas [Laravel](https://laravel.com), yang berlisensi [MIT](https://opensource.org/licenses/MIT).
