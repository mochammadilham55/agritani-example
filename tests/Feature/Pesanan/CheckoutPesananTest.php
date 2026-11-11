<?php

namespace Tests\Feature\Pesanan;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Anggota 2: checkout (pecah per petani), pembatalan, konfirmasi terima, dashboard pembeli.
 */
class CheckoutPesananTest extends TestCase
{
    use RefreshDatabase;

    private Category $cat;
    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();
        config(['agritani.shipping_cost' => 10000]);
        $this->cat = Category::create(['name' => 'Sayur', 'slug' => 'sayur']);
        $this->buyer = $this->user('pembeli', 'b@t.test');
    }

    private function user(string $role, string $email): User
    {
        return User::create(['name' => ucfirst($role).$email, 'email' => $email, 'password' => 'password', 'role' => $role, 'is_active' => true]);
    }

    private function product(User $farmer, string $name, int $price, int $stock): Product
    {
        return Product::create(['user_id' => $farmer->id, 'category_id' => $this->cat->id, 'name' => $name,
            'slug' => str($name)->slug(), 'price' => $price, 'stock' => $stock, 'unit' => 'kg', 'is_active' => true]);
    }

    private function addToCart(Product $p, int $qty): void
    {
        Cart::create(['user_id' => $this->buyer->id, 'product_id' => $p->id, 'quantity' => $qty]);
    }

    private function checkout(string $method = 'transfer')
    {
        return $this->actingAs($this->buyer)->post('/pembeli/checkout', [
            'shipping_address' => 'Jl. Mawar No. 5, Bandung', 'method' => $method,
        ]);
    }

    public function test_checkout_creates_order_payment_reduces_stock_and_clears_cart(): void
    {
        $farmer = $this->user('petani', 'f@t.test');
        $p = $this->product($farmer, 'Bayam', 5000, 10);
        $this->addToCart($p, 3);

        $order = null;
        $this->checkout()->assertRedirect();
        $order = Order::first();

        $this->assertSame('pending', $order->status);
        $this->assertEquals(15000, $order->total_price);
        $this->assertEquals(25000, $order->grandTotal());
        $this->assertEquals(25000, $order->payment->amount);
        $this->assertSame('menunggu', $order->payment->status);
        $this->assertSame(7, $p->fresh()->stock);
        $this->assertSame(0, Cart::count());
    }

    public function test_cart_with_two_farmers_is_split_into_two_orders(): void
    {
        $f1 = $this->user('petani', 'f1@t.test');
        $f2 = $this->user('petani', 'f2@t.test');
        $this->addToCart($this->product($f1, 'Bayam', 5000, 10), 1);
        $this->addToCart($this->product($f2, 'Mangga', 25000, 10), 2);

        $this->checkout('cod')->assertRedirect('/pembeli/pesanan');

        $this->assertSame(2, Order::count());
        $this->assertSame(2, Payment::where('method', 'cod')->count());
        $this->assertEqualsCanonicalizing([5000, 50000], Order::pluck('total_price')->map(fn ($v) => (int) $v)->all());
    }

    public function test_insufficient_stock_rolls_everything_back(): void
    {
        $farmer = $this->user('petani', 'f@t.test');
        $ok = $this->product($farmer, 'Bayam', 5000, 10);
        $low = $this->product($farmer, 'Kol', 7000, 2);
        $this->addToCart($ok, 1);
        $this->addToCart($low, 5);

        $this->checkout()->assertRedirect('/pembeli/keranjang')->assertSessionHas('error');

        $this->assertSame(0, Order::count());
        $this->assertSame(10, $ok->fresh()->stock);
        $this->assertSame(2, Cart::count());
    }

    public function test_checkout_validates_input_and_empty_cart(): void
    {
        $this->actingAs($this->buyer)->post('/pembeli/checkout', ['method' => 'bitcoin'])
            ->assertSessionHasErrors(['shipping_address', 'method']);
        $this->get('/pembeli/checkout')->assertRedirect('/pembeli/keranjang');
    }

    public function test_checkout_page_renders(): void
    {
        $this->addToCart($this->product($this->user('petani', 'f@t.test'), 'Bayam', 5000, 10), 2);
        $this->actingAs($this->buyer)->get('/pembeli/checkout')->assertOk()->assertSee('Rp 20.000');
    }

    public function test_buyer_only_sees_own_orders(): void
    {
        $farmer = $this->user('petani', 'f@t.test');
        $this->addToCart($this->product($farmer, 'Bayam', 5000, 10), 1);
        $this->checkout();
        $order = Order::first();

        $this->get("/pembeli/pesanan/{$order->id}")->assertOk()->assertSee('Bayam');
        $this->get('/pembeli/pesanan')->assertOk()->assertSee("Pesanan #{$order->id}");

        $other = $this->user('pembeli', 'x@t.test');
        $this->actingAs($other)->get("/pembeli/pesanan/{$order->id}")->assertNotFound();
        $this->patch("/pembeli/pesanan/{$order->id}/batal")->assertNotFound();
    }

    public function test_cancel_restores_stock_only_when_pending(): void
    {
        $p = $this->product($this->user('petani', 'f@t.test'), 'Bayam', 5000, 10);
        $this->addToCart($p, 4);
        $this->checkout();
        $order = Order::first();
        $this->assertSame(6, $p->fresh()->stock);

        $this->patch("/pembeli/pesanan/{$order->id}/batal")->assertSessionHas('success');
        $this->assertSame('dibatalkan', $order->fresh()->status);
        $this->assertSame(10, $p->fresh()->stock);

        // Batal dua kali tidak menambah stok lagi.
        $this->patch("/pembeli/pesanan/{$order->id}/batal")->assertSessionHas('error');
        $this->assertSame(10, $p->fresh()->stock);
    }

    public function test_complete_only_from_dikirim(): void
    {
        $this->addToCart($this->product($this->user('petani', 'f@t.test'), 'Bayam', 5000, 10), 1);
        $this->checkout();
        $order = Order::first();

        $this->patch("/pembeli/pesanan/{$order->id}/selesai")->assertSessionHas('error');
        $this->assertSame('pending', $order->fresh()->status);

        $order->update(['status' => 'dikirim']);
        $this->patch("/pembeli/pesanan/{$order->id}/selesai")->assertSessionHas('success');
        $this->assertSame('selesai', $order->fresh()->status);
    }

    public function test_pembeli_dashboard_renders_with_sidebar(): void
    {
        $this->actingAs($this->buyer)->get('/pembeli/dashboard')
            ->assertOk()->assertSee('Pesanan Saya')->assertSee('Ulasan Saya');
    }
}
