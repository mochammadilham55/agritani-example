<?php

namespace Tests\Feature\Laporan;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Anggota 4: laporan penjualan petani (filter tanggal, statistik, export CSV).
 */
class LaporanPetaniTest extends TestCase
{
    use RefreshDatabase;

    private Category $cat;
    private User $farmer;
    private User $other;
    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cat = Category::create(['name' => 'Sayur', 'slug' => 'sayur']);
        $this->farmer = $this->user('petani', 'f@t.test');
        $this->other = $this->user('petani', 'f2@t.test');
        $this->buyer = $this->user('pembeli', 'b@t.test');
    }

    private function user(string $role, string $email): User
    {
        return User::create(['name' => ucfirst($role).' '.$email, 'email' => $email, 'password' => 'password', 'role' => $role, 'is_active' => true]);
    }

    private function product(User $farmer, string $name = 'Bayam', int $stock = 10): Product
    {
        return Product::create(['user_id' => $farmer->id, 'category_id' => $this->cat->id, 'name' => $name,
            'slug' => str($name)->slug().'-'.$farmer->id, 'price' => 5000, 'stock' => $stock, 'unit' => 'kg', 'is_active' => true]);
    }

    /** Pesanan pembeli berisi satu produk milik $farmer. */
    private function order(User $farmer, string $status = 'pending', string $method = 'transfer', ?string $proof = null, int $qty = 2): Order
    {
        $p = $this->product($farmer, 'P'.uniqid());
        $order = Order::create(['user_id' => $this->buyer->id, 'total_price' => 5000 * $qty, 'status' => $status,
            'shipping_address' => 'Jl. Mawar 5', 'shipping_cost' => 10000]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $p->id, 'quantity' => $qty, 'price' => 5000, 'subtotal' => 5000 * $qty]);
        Payment::create(['order_id' => $order->id, 'amount' => 5000 * $qty + 10000, 'method' => $method, 'status' => 'menunggu', 'proof_image' => $proof]);

        return $order;
    }

    private function completedSale(User $farmer, string $name, int $qty, string $date): void
    {
        $p = $this->product($farmer, $name);
        $o = Order::create(['user_id' => $this->buyer->id, 'total_price' => 5000 * $qty, 'status' => 'selesai', 'shipping_address' => 'x']);
        $o->forceFill(['created_at' => $date])->save();
        OrderItem::create(['order_id' => $o->id, 'product_id' => $p->id, 'quantity' => $qty, 'price' => 5000, 'subtotal' => 5000 * $qty]);
    }

    public function test_report_stats_range_and_isolation(): void
    {
        $this->completedSale($this->farmer, 'Bayam', 2, today()->subDays(1)->toDateTimeString());   // 10.000
        $this->completedSale($this->farmer, 'Kol', 4, today()->toDateTimeString());                  // 20.000
        $this->completedSale($this->farmer, 'Lama', 9, today()->subDays(90)->toDateTimeString());   // di luar rentang
        $this->completedSale($this->other, 'Milik Lain', 50, today()->toDateTimeString());           // petani lain
        $this->order($this->farmer); // pending: tidak dihitung

        $this->actingAs($this->farmer)->get('/petani/laporan')
            ->assertOk()
            ->assertViewHas('revenue', 30000.0)
            ->assertViewHas('orders', 2)
            ->assertViewHas('qty', 6)
            ->assertViewHas('average', 15000.0)
            ->assertViewHas('topProducts', fn ($c) => $c->first()->name === 'Kol')
            ->assertDontSee('Milik Lain')
            ->assertDontSee('Lama');

        $this->get('/petani/laporan?from='.today()->subDays(100)->toDateString().'&to='.today()->toDateString())
            ->assertViewHas('revenue', 75000.0);
        $this->get('/petani/laporan?from=2026-02-02&to=2026-01-01')->assertSessionHasErrors('to');
    }

    public function test_csv_export(): void
    {
        $this->completedSale($this->farmer, 'Bayam', 2, today()->toDateTimeString());
        $this->completedSale($this->other, 'Milik Lain', 5, today()->toDateTimeString());
        $this->buyer->update(['name' => '=HYPERLINK("x")']);

        $res = $this->actingAs($this->farmer)->get('/petani/laporan/export');
        $res->assertOk()->assertHeader('content-disposition');
        $csv = $res->streamedContent();

        $this->assertStringContainsString('Tanggal,Pembeli,Produk,Jumlah', $csv);
        $this->assertStringContainsString('Bayam', $csv);
        $this->assertStringNotContainsString('Milik Lain', $csv);
        $this->assertStringContainsString("\"'=HYPERLINK", $csv);
        $this->assertStringNotContainsString(',=HYPERLINK', $csv);
    }

    public function test_report_page_renders_for_farmer(): void
    {
        $this->actingAs($this->farmer)->get('/petani/laporan')->assertOk();
    }

    public function test_other_roles_blocked_from_farmer_reports(): void
    {
        $this->actingAs($this->buyer)->get('/petani/laporan')->assertForbidden();
        $this->get('/petani/laporan/export')->assertForbidden();
    }
}
