<?php

namespace Tests\Feature\Pesanan;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Anggota 2: pesanan masuk di sisi petani (konfirmasi, tolak, kepemilikan).
 */
class PesananPetaniTest extends TestCase
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

    public function test_cod_order_can_be_confirmed_directly(): void
    {
        $order = $this->order($this->farmer, method: 'cod');
        $this->actingAs($this->farmer)->patch("/petani/pesanan/{$order->id}/konfirmasi")->assertSessionHas('success');
        $this->assertSame('diproses', $order->fresh()->status);
    }

    public function test_reject_order_restores_stock(): void
    {
        $order = $this->order($this->farmer, qty: 3);
        $product = $order->items->first()->product;
        $before = $product->stock;

        $this->actingAs($this->farmer)->patch("/petani/pesanan/{$order->id}/tolak")->assertSessionHas('success');
        $this->assertSame('dibatalkan', $order->fresh()->status);
        $this->assertSame($before + 3, $product->fresh()->stock);

        $this->patch("/petani/pesanan/{$order->id}/tolak")->assertSessionHas('error');
        $this->assertSame($before + 3, $product->fresh()->stock);
    }

    public function test_order_pages_render_for_farmer(): void
    {
        $o = $this->order($this->farmer);
        $this->actingAs($this->farmer);
        $this->get('/petani/pesanan')->assertOk();
        $this->get("/petani/pesanan/{$o->id}")->assertOk();
    }

    public function test_farmer_only_sees_own_orders(): void
    {
        $mine = $this->order($this->farmer);
        $theirs = $this->order($this->other);
        $this->actingAs($this->farmer);

        $this->get('/petani/pesanan')->assertSee("#{$mine->id}")->assertDontSee("#{$theirs->id}</td>", false);
        $this->get("/petani/pesanan/{$mine->id}")->assertOk();
        $this->get("/petani/pesanan/{$theirs->id}")->assertNotFound();
        $this->patch("/petani/pesanan/{$theirs->id}/konfirmasi")->assertNotFound();
        $this->patch("/petani/pesanan/{$theirs->id}/tolak")->assertNotFound();
    }
}
