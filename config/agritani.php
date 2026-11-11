<?php

return [
    // Ongkos kirim flat per pesanan (satu pesanan = satu petani), dalam Rupiah.
    'shipping_cost' => (int) env('AGRITANI_SHIPPING_COST', 10000),
];
