<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Review;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Data contoh untuk mencoba dashboard/laporan: pesanan dalam berbagai status, pembayaran, pengiriman, dan ulasan.
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

        $completed = collect();

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

                if (in_array($status, ['diproses', 'dikirim', 'selesai'], true)) {
                    Shipment::create([
                        'order_id' => $order->id,
                        'courier' => $status === 'diproses' ? null : 'JNE',
                        'tracking_number' => $status === 'diproses' ? null : 'JNE'.str_pad((string) $order->id, 8, '0', STR_PAD_LEFT),
                        'shipped_at' => $status === 'diproses' ? null : now()->subDays(max($daysAgo - 1, 0)),
                        'estimated_arrival' => $status === 'dikirim' ? now()->addDays(2)->toDateString() : null,
                        'status' => match ($status) {
                            'diproses' => 'disiapkan', 'dikirim' => 'dikirim', default => 'tiba',
                        },
                    ]);
                }

                if ($status === 'selesai') {
                    $completed->push($order->load('items'));
                }
            }
        }

        // Ulasan: sebagian sudah disetujui, sebagian menunggu moderasi.
        foreach ($completed->values() as $i => $order) {
            $item = $order->items->first();
            Review::create([
                'order_id' => $order->id,
                'user_id' => $buyer->id,
                'product_id' => $item->product_id,
                'rating' => [5, 4, 5, 3][$i % 4],
                'comment' => ['Segar dan cepat sampai!', 'Kualitas bagus, harga oke.', 'Mantap, pasti beli lagi.', 'Lumayan, kemasan bisa lebih rapi.'][$i % 4],
                'is_approved' => $i % 2 === 0,
            ]);
        }
    }
}
