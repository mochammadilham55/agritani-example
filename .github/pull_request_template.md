## Ringkasan
<!-- Apa yang diubah dan kenapa? Satu-dua kalimat. -->

## Modul
<!-- Centang satu: sesuai PEMBAGIAN_TUGAS.md -->
- [ ] Anggota 1: Petani & Produk
- [ ] Anggota 2: Pesanan
- [ ] Anggota 3: Pembayaran & Pengiriman
- [ ] Anggota 4: Laporan, Dashboard & Ulasan
- [ ] File inti (sudah didiskusikan dengan ketua tim)

## Cara mencoba
<!-- Langkah singkat agar reviewer bisa mencoba fitur ini. -->

## Checklist pembuat (wajib sebelum minta review)
- [ ] `php artisan test` hijau
- [ ] Hanya file modul saya yang berubah
- [ ] Tidak ada `.env`, `vendor/`, `node_modules/`, atau file unggahan
- [ ] Tidak ada password, token, atau kunci API di kode
- [ ] Migration baru dibuat sebagai file baru, bukan mengedit yang lama
- [ ] Fitur baru punya test
- [ ] Sudah `git pull origin main` dan tidak ada konflik

## Checklist reviewer
- [ ] Route baru ada di bawah `auth` dan `role:` yang benar
- [ ] Data pengguna lain tidak bisa diakses lewat ID (petani A vs petani B)
- [ ] Input divalidasi, termasuk upload file
- [ ] Perubahan stok/uang berada di dalam transaksi database
- [ ] Sudah dicoba di browser, bukan hanya dibaca
- [ ] Saat squash merge, pesan commit jelas dan tanpa baris `Co-Authored-By`
