<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Anggota 4: pesanan dikirim yang tidak dikonfirmasi pembeli selesai otomatis (lihat AutoCompleteOrders).
Schedule::command('orders:auto-complete')->hourly();
