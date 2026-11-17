<?php

namespace Tests\Feature\PesananMasuk;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Anggota 4: resi pengiriman dan alur pesanan sampai selesai.
 */
class PengirimanTest extends TestCase
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

    /** Pesanan pembeli berisi satu produk milik $farmer. */
    private function order(User $farmer, string $status = 'pending', string $method = 'cod'): Order
    {
        $p = Product::create(['user_id' => $farmer->id, 'category_id' => $this->cat->id, 'name' => 'P'.uniqid(),
            'slug' => 'p-'.uniqid(), 'price' => 5000, 'stock' => 10, 'unit' => 'kg', 'is_active' => true]);
        $order = Order::create(['user_id' => $this->buyer->id, 'total_price' => 10000, 'status' => $status,
            'shipping_address' => 'Jl. Mawar 5', 'shipping_cost' => 10000]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $p->id, 'quantity' => 2, 'price' => 5000, 'subtotal' => 10000]);
        Payment::create(['order_id' => $order->id, 'amount' => 20000, 'method' => $method, 'status' => 'menunggu']);

        return $order;
    }

    public function test_shipment_requires_diproses_and_valid_input(): void
    {
        $pending = $this->order($this->farmer);
        $this->actingAs($this->farmer)->put("/petani/pengiriman/{$pending->id}", ['courier' => 'JNE', 'tracking_number' => '123'])
            ->assertSessionHas('error');
        $this->assertSame('pending', $pending->fresh()->status);

        $order = $this->order($this->farmer, 'diproses');
        $this->put("/petani/pengiriman/{$order->id}", ['courier' => ''])->assertSessionHasErrors(['courier', 'tracking_number']);
        $this->assertSame('diproses', $order->fresh()->status);
    }

    public function test_full_flow_pending_to_selesai(): void
    {
        $order = $this->order($this->farmer);
        $this->actingAs($this->farmer);

        $this->patch("/petani/pesanan/{$order->id}/konfirmasi");
        $this->assertSame('diproses', $order->fresh()->status);

        $this->put("/petani/pengiriman/{$order->id}", ['courier' => 'JNE', 'tracking_number' => 'RESI123'])
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('dikirim', $order->status);
        $this->assertSame('JNE', $order->courier);
        $this->assertSame('RESI123', $order->tracking_number);
        $this->assertNotNull($order->shipped_at);

        // Ubah resi setelah dikirim tidak mengubah status pesanan.
        $this->put("/petani/pengiriman/{$order->id}", ['courier' => 'JNE', 'tracking_number' => 'RESI999']);
        $this->assertSame('dikirim', $order->fresh()->status);

        $this->actingAs($this->buyer)->get("/pembeli/pesanan/{$order->id}")->assertSee('RESI999');
        $this->patch("/pembeli/pesanan/{$order->id}/selesai")->assertSessionHas('success');
        $this->assertSame('selesai', $order->fresh()->status);
    }

    public function test_shipment_page_renders_for_farmer(): void
    {
        $this->order($this->farmer, 'diproses');
        $this->actingAs($this->farmer)->get('/petani/pengiriman')->assertOk()->assertSee('Kirim Pesanan');
    }

    public function test_farmer_cannot_ship_others_order(): void
    {
        $theirs = $this->order($this->other, 'diproses');
        $this->actingAs($this->farmer)->put("/petani/pengiriman/{$theirs->id}", ['courier' => 'JNE', 'tracking_number' => '1'])->assertNotFound();
        $this->assertSame('diproses', $theirs->fresh()->status);
    }
}
