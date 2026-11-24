<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

/**
 * Anggota 4: pesanan yang sudah dikirim tetapi tidak dikonfirmasi pembeli
 * otomatis ditandai selesai setelah batas hari (bawaan 7 hari).
 * Dijadwalkan di routes/console.php; bisa juga dijalankan manual:
 *   php artisan orders:auto-complete
 */
class AutoCompleteOrders extends Command
{
    protected $signature = 'orders:auto-complete';

    protected $description = 'Tandai selesai pesanan yang sudah dikirim lebih dari batas hari tanpa konfirmasi pembeli';

    public function handle(): int
    {
        $orders = Order::readyToAutoComplete()->with('payment')->get();

        foreach ($orders as $order) {
            $order->markCompleted();
        }

        $this->info("{$orders->count()} pesanan ditandai selesai otomatis.");

        return self::SUCCESS;
    }
}
