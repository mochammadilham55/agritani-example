<?php

return [
    // Ongkos kirim flat per pesanan (satu pesanan = satu petani), dalam Rupiah.
    'shipping_cost' => (int) env('TANIMART_SHIPPING_COST', 10000),

    // Pesanan berstatus dikirim otomatis selesai setelah sekian hari jika pembeli tidak menekan "Pesanan Diterima".
    'auto_complete_days' => (int) env('TANIMART_AUTO_COMPLETE_DAYS', 7),

    // Nomor WhatsApp admin untuk tombol "Hubungi Admin" (format internasional tanpa +, mis. 628123456789).
    // Kosong = tombol disembunyikan. Isi di .env masing-masing, jangan di-commit.
    'admin_whatsapp' => preg_replace('/\D/', '', (string) env('TANIMART_ADMIN_WHATSAPP', '')),
];
