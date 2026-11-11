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
 * Anggota 4: laporan platform admin (pendapatan, status, terlaris, export CSV).
 */
class LaporanAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $farmer;
    private User $buyer;
    private Category $cat;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->user('admin', 'a@t.test');
        $this->farmer = $this->user('petani', 'f@t.test');
        $this->buyer = $this->user('pembeli', 'b@t.test');
        $this->cat = Category::create(['name' => 'Sayur', 'slug' => 'sayur']);
    }

    private function user(string $role, string $email, bool $active = true): User
    {
        return User::create(['name' => ucfirst($role).' '.$email, 'email' => $email, 'password' => 'password', 'role' => $role, 'is_active' => $active]);
    }

    private function product(string $name = 'Bayam', ?User $farmer = null): Product
    {
        return Product::create(['user_id' => ($farmer ?? $this->farmer)->id, 'category_id' => $this->cat->id, 'name' => $name,
            'slug' => str($name)->slug(), 'price' => 5000, 'stock' => 10, 'unit' => 'kg', 'is_active' => true]);
    }

    private function sale(Product $p, int $qty, string $status = 'selesai', ?string $date = null): Order
    {
        $o = Order::create(['user_id' => $this->buyer->id, 'total_price' => 5000 * $qty, 'status' => $status, 'shipping_address' => 'x', 'shipping_cost' => 10000]);
        if ($date) {
            $o->forceFill(['created_at' => $date])->save();
        }
        OrderItem::create(['order_id' => $o->id, 'product_id' => $p->id, 'quantity' => $qty, 'price' => 5000, 'subtotal' => 5000 * $qty]);
        Payment::create(['order_id' => $o->id, 'amount' => 5000 * $qty + 10000, 'method' => 'cod', 'status' => 'menunggu']);

        return $o;
    }

    public function test_platform_report_numbers(): void
    {
        $other = $this->user('petani', 'f2@t.test');
        $this->sale($this->product('Bayam'), 2, 'selesai', today()->subDay()->toDateTimeString());          // 10.000
        $this->sale($this->product('Mangga', $other), 4, 'selesai', today()->toDateTimeString());           // 20.000
        $this->sale($this->product('Kol'), 1, 'pending', today()->toDateTimeString());
        $this->sale($this->product('Apel'), 1, 'dibatalkan', today()->toDateTimeString());
        $this->sale($this->product('Lama'), 9, 'selesai', today()->subDays(90)->toDateTimeString());        // di luar rentang

        $this->actingAs($this->admin)->get('/admin/laporan')
            ->assertOk()
            ->assertViewHas('revenue', 30000.0)
            ->assertViewHas('completed', 2)
            ->assertViewHas('qty', 6)
            ->assertViewHas('byStatus', fn ($s) => $s['selesai'] === 2 && $s['pending'] === 1 && $s['dibatalkan'] === 1 && $s['diproses'] === 0)
            ->assertViewHas('topProducts', fn ($t) => $t->first()->name === 'Mangga' && $t->first()->farmer === $other->name && $t->count() === 2);

        $this->get('/admin/laporan?from=2026-02-02&to=2026-01-01')->assertSessionHasErrors('to');
    }

    public function test_platform_csv_export_includes_all_statuses_and_escapes_formulas(): void
    {
        $this->buyer->update(['name' => '@evil']);
        $this->sale($this->product('Bayam'), 2, 'selesai', today()->toDateTimeString());
        $this->sale($this->product('Kol'), 1, 'dibatalkan', today()->toDateTimeString());

        $res = $this->actingAs($this->admin)->get('/admin/laporan/export');
        $res->assertOk();
        $csv = $res->streamedContent();

        $this->assertStringContainsString('Metode Bayar', $csv);
        $this->assertStringContainsString('selesai', $csv);
        $this->assertStringContainsString('dibatalkan', $csv);
        $this->assertStringContainsString("'@evil", $csv);
        $this->assertStringContainsString('20000', $csv); // 2 x 5000 + 10000 ongkir
    }

    public function test_report_and_dashboard_pages_render_and_are_admin_only(): void
    {
        $this->sale($this->product(), 1);
        $this->actingAs($this->admin);
        $this->get('/admin/dashboard')->assertOk();
        $this->get('/admin/laporan')->assertOk();

        foreach ([$this->farmer, $this->buyer] as $u) {
            $this->actingAs($u)->get('/admin/laporan')->assertForbidden();
            $this->get('/admin/laporan/export')->assertForbidden();
            $this->get('/admin/dashboard')->assertForbidden();
        }
    }
}
