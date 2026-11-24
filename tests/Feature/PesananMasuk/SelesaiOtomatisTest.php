<?php

namespace Tests\Feature\PesananMasuk;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Anggota 4: pesanan dikirim otomatis selesai setelah batas hari (bawaan 7)
 * jika pembeli tidak menekan "Pesanan Diterima".
 */
class SelesaiOtomatisTest extends TestCase
{
    use RefreshDatabase;

    private User $farmer;
    private User $buyer;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        config(['agritani.auto_complete_days' => 7]);
        $cat = Category::create(['name' => 'Sayur', 'slug' => 'sayur']);
        $this->farmer = User::create(['name' => 'Petani', 'email' => 'f@t.test', 'password' => 'password', 'role' => 'petani', 'is_active' => true]);
        $this->buyer = User::create(['name' => 'Pembeli', 'email' => 'b@t.test', 'password' => 'password', 'role' => 'pembeli', 'is_active' => true]);
        $this->product = Product::create(['user_id' => $this->farmer->id, 'category_id' => $cat->id, 'name' => 'Bayam',
            'slug' => 'bayam', 'price' => 5000, 'stock' => 10, 'unit' => 'kg', 'is_active' => true]);
    }

    private function order(string $status, ?int $shippedDaysAgo, string $method = 'cod'): Order
    {
        $order = Order::create(['user_id' => $this->buyer->id, 'total_price' => 5000, 'status' => $status,
            'shipping_address' => 'Jl. Mawar 5', 'shipping_cost' => 10000, 'courier' => 'JNE', 'tracking_number' => 'JNE1',
            'shipped_at' => $shippedDaysAgo === null ? null : now()->subDays($shippedDaysAgo)]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $this->product->id, 'quantity' => 1, 'price' => 5000, 'subtotal' => 5000]);
        Payment::create(['order_id' => $order->id, 'amount' => 15000, 'method' => $method,
            'status' => $method === 'cod' ? 'menunggu' : 'dikonfirmasi']);

        return $order;
    }

    public function test_shipped_order_older_than_limit_is_completed_and_cod_paid(): void
    {
        $order = $this->order('dikirim', 8, 'cod');

        $this->artisan('orders:auto-complete')->expectsOutput('1 pesanan ditandai selesai otomatis.')->assertSuccessful();

        $this->assertSame('selesai', $order->fresh()->status);
        $this->assertSame('dikonfirmasi', $order->payment->fresh()->status);
        $this->assertNotNull($order->payment->fresh()->confirmed_at);
    }

    public function test_order_shipped_less_than_limit_is_not_completed(): void
    {
        $order = $this->order('dikirim', 6);

        $this->artisan('orders:auto-complete')->assertSuccessful();

        $this->assertSame('dikirim', $order->fresh()->status);
        $this->assertSame('menunggu', $order->payment->fresh()->status);
    }

    public function test_other_statuses_are_never_auto_completed(): void
    {
        $pending = $this->order('pending', null);
        $processed = $this->order('diproses', null);
        $cancelled = $this->order('dibatalkan', 30);

        $this->artisan('orders:auto-complete')->expectsOutput('0 pesanan ditandai selesai otomatis.');

        $this->assertSame('pending', $pending->fresh()->status);
        $this->assertSame('diproses', $processed->fresh()->status);
        $this->assertSame('dibatalkan', $cancelled->fresh()->status);
    }

    public function test_transfer_payment_stays_as_is(): void
    {
        $order = $this->order('dikirim', 10, 'transfer');
        $confirmedAt = $order->payment->confirmed_at;

        $this->artisan('orders:auto-complete');

        $this->assertSame('selesai', $order->fresh()->status);
        $this->assertSame('dikonfirmasi', $order->payment->fresh()->status);
        $this->assertEquals($confirmedAt, $order->payment->fresh()->confirmed_at);
    }

    public function test_limit_follows_config(): void
    {
        config(['agritani.auto_complete_days' => 3]);
        $order = $this->order('dikirim', 4);

        $this->artisan('orders:auto-complete');

        $this->assertSame('selesai', $order->fresh()->status);
    }

    public function test_command_is_scheduled_hourly(): void
    {
        $event = collect(app(Schedule::class)->events())
            ->first(fn ($e) => str_contains($e->command, 'orders:auto-complete'));

        $this->assertNotNull($event);
        $this->assertSame('0 * * * *', $event->expression);
    }

    public function test_order_pages_show_auto_complete_date(): void
    {
        $order = $this->order('dikirim', 2);
        $date = $order->shipped_at->copy()->addDays(7)->format('d M Y H:i');

        $this->actingAs($this->buyer)->get(route('pembeli.orders.show', $order))
            ->assertOk()->assertSee("otomatis selesai pada {$date}", false);
        $this->actingAs($this->farmer)->get(route('petani.orders.show', $order->id))
            ->assertOk()->assertSee("Otomatis selesai pada {$date}", false);
    }
}
