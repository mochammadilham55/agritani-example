<?php

namespace Tests\Feature\PembayaranPengiriman;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Anggota 3: resi, status pengiriman, dan alur pesanan sampai selesai.
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

    public function test_shipment_requires_diproses_and_valid_input(): void
    {
        $pending = $this->order($this->farmer);
        $this->actingAs($this->farmer)->put("/petani/pengiriman/{$pending->id}", ['courier' => 'JNE', 'tracking_number' => '123'])
            ->assertSessionHas('error');

        $order = $this->order($this->farmer, 'diproses', 'cod');
        $this->put("/petani/pengiriman/{$order->id}", ['courier' => ''])->assertSessionHasErrors(['courier', 'tracking_number']);
        $this->put("/petani/pengiriman/{$order->id}", ['courier' => 'JNE', 'tracking_number' => '1', 'estimated_arrival' => '2000-01-01'])
            ->assertSessionHasErrors('estimated_arrival');
    }

    public function test_full_flow_pending_to_selesai(): void
    {
        $order = $this->order($this->farmer, method: 'cod');
        $this->actingAs($this->farmer);

        $this->patch("/petani/pesanan/{$order->id}/konfirmasi");
        $this->put("/petani/pengiriman/{$order->id}", ['courier' => 'JNE', 'tracking_number' => 'RESI123', 'estimated_arrival' => today()->addDays(2)->toDateString()])
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('dikirim', $order->status);
        $this->assertSame('RESI123', $order->shipment->tracking_number);
        $this->assertNotNull($order->shipment->shipped_at);

        $this->patch("/petani/pengiriman/{$order->id}/tiba")->assertSessionHas('success');
        $this->assertSame('tiba', $order->shipment->fresh()->status);

        // Ubah resi setelah dikirim tidak mengubah status pesanan.
        $this->put("/petani/pengiriman/{$order->id}", ['courier' => 'JNE', 'tracking_number' => 'RESI999']);
        $this->assertSame('dikirim', $order->fresh()->status);

        $this->actingAs($this->buyer)->get("/pembeli/pesanan/{$order->id}")->assertSee('RESI999');
        $this->patch("/pembeli/pesanan/{$order->id}/selesai")->assertSessionHas('success');
        $this->assertSame('selesai', $order->fresh()->status);
    }

    public function test_shipment_page_renders_for_farmer(): void
    {
        $this->order($this->farmer, 'diproses', 'cod');
        $this->actingAs($this->farmer)->get('/petani/pengiriman')->assertOk()->assertSee('Kirim Pesanan');
    }

    public function test_farmer_cannot_ship_others_order(): void
    {
        $theirs = $this->order($this->other, 'diproses', 'cod');
        $this->actingAs($this->farmer);

        $this->put("/petani/pengiriman/{$theirs->id}", ['courier' => 'JNE', 'tracking_number' => '1'])->assertNotFound();
        $this->patch("/petani/pengiriman/{$theirs->id}/tiba")->assertNotFound();
        $this->assertSame('diproses', $theirs->fresh()->status);
    }
}
