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
 * Laporan penjualan: petani (anggota 4) dan admin (anggota 1).
 */
class LaporanPenjualanTest extends TestCase
{
    use RefreshDatabase;

    private User $farmer;
    private User $other;
    private User $buyer;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $make = fn ($role, $email) => User::create(['name' => ucfirst($role).' '.$email, 'email' => $email,
            'password' => 'password', 'role' => $role, 'is_active' => true]);
        $this->farmer = $make('petani', 'p@t.test');
        $this->other = $make('petani', 'p2@t.test');
        $this->buyer = $make('pembeli', 'b@t.test');
        $this->admin = $make('admin', 'a@t.test');

        $cat = Category::create(['name' => 'Sayur', 'slug' => 'sayur']);
        $mine = Product::create(['user_id' => $this->farmer->id, 'category_id' => $cat->id, 'name' => 'Bayam', 'slug' => 'bayam', 'price' => 5000, 'stock' => 9]);
        $theirs = Product::create(['user_id' => $this->other->id, 'category_id' => $cat->id, 'name' => 'Kol', 'slug' => 'kol', 'price' => 7000, 'stock' => 9]);

        // Pesanan selesai milik petani (2 × 5000), pesanan selesai petani lain (3 × 7000), dan satu dibatalkan.
        $this->order($mine, 2, 'selesai');
        $this->order($theirs, 3, 'selesai');
        $this->order($mine, 1, 'dibatalkan');
    }

    private function order(Product $product, int $qty, string $status): void
    {
        $order = Order::create(['user_id' => $this->buyer->id, 'total_price' => $product->price * $qty,
            'shipping_cost' => 0, 'status' => $status, 'shipping_address' => 'Jl. Test']);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => $qty,
            'price' => $product->price, 'subtotal' => $product->price * $qty]);
    }

    public function test_farmer_report_only_contains_own_sales(): void
    {
        $this->actingAs($this->farmer)->get(route('petani.reports.index'))
            ->assertOk()
            ->assertSee('Laporan Penjualan')
            ->assertSee('Rp 10.000')      // pendapatan: 2 × 5000 dari pesanan selesai
            ->assertSee('Bayam')
            ->assertDontSee('Kol');
    }

    public function test_admin_report_covers_all_farmers(): void
    {
        $this->actingAs($this->admin)->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Rp 31.000')      // 10.000 + 21.000
            ->assertSee('Bayam')
            ->assertSee('Kol');
    }

    public function test_status_filter_limits_rows(): void
    {
        $this->actingAs($this->farmer)->get(route('petani.reports.index', ['status' => 'dibatalkan']))
            ->assertOk()
            ->assertSee('Bayam ×1');
    }

    public function test_csv_export_downloads_rows(): void
    {
        $response = $this->actingAs($this->farmer)->get(route('petani.reports.index', ['export' => 'csv']));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Bayam x2', $csv);
        $this->assertStringNotContainsString('Kol', $csv);
    }

    public function test_invalid_date_range_is_rejected(): void
    {
        $this->actingAs($this->farmer)
            ->from(route('petani.reports.index'))
            ->get(route('petani.reports.index', ['dari' => '2026-05-10', 'sampai' => '2026-05-01']))
            ->assertRedirect(route('petani.reports.index'))
            ->assertSessionHasErrors('sampai');
    }

    public function test_reports_are_limited_by_role(): void
    {
        $this->actingAs($this->buyer)->get(route('petani.reports.index'))->assertForbidden();
        $this->actingAs($this->farmer)->get(route('admin.reports.index'))->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.reports.index'))->assertRedirect(route('login'));
        $this->get(route('petani.reports.index'))->assertRedirect(route('login'));
    }
}
