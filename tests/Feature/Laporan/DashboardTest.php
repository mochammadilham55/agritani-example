<?php

namespace Tests\Feature\Laporan;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Anggota 4: dashboard petani dan admin (statistik hanya dari data milik sendiri).
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, string $email): User
    {
        return User::create(['name' => ucfirst($role), 'email' => $email, 'password' => 'password', 'role' => $role, 'is_active' => true]);
    }

    public function test_farmer_stats_only_count_own_completed_sales(): void
    {
        $farmer = $this->user('petani', 'p@t.test');
        $other = $this->user('petani', 'p2@t.test');
        $buyer = $this->user('pembeli', 'b@t.test');
        $cat = Category::create(['name' => 'Sayur', 'slug' => 'sayur']);

        $mine = Product::create(['user_id' => $farmer->id, 'category_id' => $cat->id, 'name' => 'Bayam',
            'slug' => 'bayam', 'price' => 5000, 'stock' => 3]);
        $theirs = Product::create(['user_id' => $other->id, 'category_id' => $cat->id, 'name' => 'Kol',
            'slug' => 'kol', 'price' => 7000, 'stock' => 99]);

        $done = Order::create(['user_id' => $buyer->id, 'total_price' => 10000, 'status' => 'selesai', 'shipping_address' => 'x']);
        $pending = Order::create(['user_id' => $buyer->id, 'total_price' => 5000, 'status' => 'pending', 'shipping_address' => 'x']);
        OrderItem::create(['order_id' => $done->id, 'product_id' => $mine->id, 'quantity' => 2, 'price' => 5000, 'subtotal' => 10000]);
        OrderItem::create(['order_id' => $done->id, 'product_id' => $theirs->id, 'quantity' => 9, 'price' => 7000, 'subtotal' => 63000]);
        OrderItem::create(['order_id' => $pending->id, 'product_id' => $mine->id, 'quantity' => 1, 'price' => 5000, 'subtotal' => 5000]);

        $this->actingAs($farmer)->get('/petani/dashboard')
            ->assertOk()
            ->assertViewHas('soldQty', 2)
            ->assertViewHas('revenue', fn ($v) => (float) $v === 10000.0)
            ->assertViewHas('lowStock', fn ($c) => $c->count() === 1 && $c->first()->name === 'Bayam');
    }

    public function test_farmer_and_admin_dashboards_render_with_sidebar(): void
    {
        $this->actingAs($this->user('admin', 'a@t.test'))->get('/admin/dashboard')
            ->assertOk()->assertSee('Daftar Petani')->assertSee('Moderasi Ulasan');
        $this->actingAs($this->user('petani', 'p@t.test'))->get('/petani/dashboard')
            ->assertOk()->assertSee('Produk Saya')->assertSee('Pembayaran')->assertSee('Stok Menipis');
    }
}
