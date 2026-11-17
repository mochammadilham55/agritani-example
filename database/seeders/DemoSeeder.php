<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Data contoh untuk mencoba dashboard: pesanan dalam berbagai status beserta pembayaran dan resi pengiriman.
 * Jalankan: php artisan db:seed --class=DemoSeeder  (aman diulang; dilewati jika pesanan sudah ada)
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Order::exists()) {
            $this->command?->warn('Pesanan sudah ada, DemoSeeder dilewati.');

            return;
        }

        $this->call([UserSeeder::class, CategorySeeder::class, ProductSeeder::class]);

        $buyer = User::where('email', 'pembeli@agritani.test')->firstOrFail();

        // [status, metode, status bayar, [[slug, qty], ...], hari lalu]
        // Satu pesanan = satu petani (produk berselang-seling per petani lihat ProductSeeder).
        $plans = [
            ['selesai', 'transfer', 'dikonfirmasi', [['bayam-hijau', 3], ['tomat-merah', 2]], 12],
            ['selesai', 'cod', 'dikonfirmasi', [['mangga-harum-manis', 2]], 9],
            ['selesai', 'transfer', 'dikonfirmasi', [['cabai-merah-keriting', 1]], 6],
            ['selesai', 'cod', 'dikonfirmasi', [['beras-pandan-wangi', 5]], 3],
            ['dikirim', 'transfer', 'dikonfirmasi', [['jagung-manis', 2]], 2],
            ['diproses', 'cod', 'menunggu', [['singkong', 4]], 1],
            ['pending', 'transfer', 'menunggu', [['kangkung', 2]], 0],
            ['dibatalkan', 'cod', 'menunggu', [['pisang-cavendish', 1]], 5],
        ];

        foreach ($plans as [$status, $method, $payStatus, $lines, $daysAgo]) {
            $products = collect($lines)->map(fn ($l) => [Product::where('slug', $l[0])->firstOrFail(), $l[1]]);

            // Pecah per petani agar sesuai aturan "satu pesanan = satu petani".
            foreach ($products->groupBy(fn ($p) => $p[0]->user_id) as $group) {
                $subtotal = $group->sum(fn ($p) => $p[0]->price * $p[1]);
                $order = Order::create([
                    'user_id' => $buyer->id,
                    'total_price' => $subtotal,
                    'status' => $status,
                    'shipping_address' => 'Jl. Contoh No. 1, Indonesia',
                    'shipping_cost' => config('agritani.shipping_cost'),
                ]);
                $order->forceFill(['created_at' => now()->subDays($daysAgo), 'updated_at' => now()->subDays($daysAgo)])->save();

                foreach ($group as [$product, $qty]) {
                    $order->items()->create([
                        'product_id' => $product->id, 'quantity' => $qty,
                        'price' => $product->price, 'subtotal' => $product->price * $qty,
                    ]);
                }

                Payment::create([
                    'order_id' => $order->id,
                    'amount' => $order->grandTotal(),
                    'method' => $method,
                    'status' => $payStatus,
                    'confirmed_at' => $payStatus === 'dikonfirmasi' ? now()->subDays($daysAgo) : null,
                ]);

                // Resi pengiriman disimpan langsung di tabel orders.
                if (in_array($status, ['dikirim', 'selesai'], true)) {
                    $order->forceFill([
                        'courier' => 'JNE',
                        'tracking_number' => 'JNE'.str_pad((string) $order->id, 8, '0', STR_PAD_LEFT),
                        'shipped_at' => now()->subDays(max($daysAgo - 1, 0)),
                    ])->save();
                }
            }
        }
    }
}
