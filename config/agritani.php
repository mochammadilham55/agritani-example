<?php

return [
    // Ongkos kirim flat per pesanan (satu pesanan = satu petani), dalam Rupiah.
    'shipping_cost' => (int) env('AGRITANI_SHIPPING_COST', 10000),

    // Pesanan berstatus dikirim otomatis selesai setelah sekian hari jika pembeli tidak menekan "Pesanan Diterima".
    'auto_complete_days' => (int) env('AGRITANI_AUTO_COMPLETE_DAYS', 7),
];
