<?php

namespace Tests\Feature\PembayaranPengiriman;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Anggota 3: konfirmasi/penolakan pembayaran oleh petani.
 */
class PembayaranTest extends TestCase
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

    public function test_transfer_order_cannot_be_confirmed_before_payment(): void
    {
        $order = $this->order($this->farmer, proof: 'x.jpg');
        $this->actingAs($this->farmer)->patch("/petani/pesanan/{$order->id}/konfirmasi")->assertSessionHas('error');
        $this->assertSame('pending', $order->fresh()->status);

        $this->patch("/petani/pembayaran/{$order->payment->id}/konfirmasi")->assertSessionHas('success');
        $this->assertSame('dikonfirmasi', $order->payment->fresh()->status);
        $this->assertNotNull($order->payment->fresh()->confirmed_at);

        $this->patch("/petani/pesanan/{$order->id}/konfirmasi")->assertSessionHas('success');
        $this->assertSame('diproses', $order->fresh()->status);
        $this->assertSame('disiapkan', $order->shipment->status);
    }

    public function test_cannot_confirm_payment_without_proof_and_reject_allows_reupload(): void
    {
        $noProof = $this->order($this->farmer);
        $this->actingAs($this->farmer)->patch("/petani/pembayaran/{$noProof->payment->id}/konfirmasi")->assertSessionHas('error');

        $withProof = $this->order($this->farmer, proof: 'p.jpg');
        $this->patch("/petani/pembayaran/{$withProof->payment->id}/tolak")->assertSessionHas('success');
        $this->assertSame('ditolak', $withProof->payment->fresh()->status);

        Storage::fake('public');
        $this->actingAs($this->buyer)->post("/pembeli/pesanan/{$withProof->id}/bukti", ['proof_image' => UploadedFile::fake()->image('n.jpg')])
            ->assertSessionHas('success');
        $this->assertSame('menunggu', $withProof->payment->fresh()->status);
    }

    public function test_payment_page_renders_for_farmer(): void
    {
        $this->order($this->farmer, proof: 'x.jpg');
        $this->actingAs($this->farmer)->get('/petani/pembayaran')->assertOk()->assertSee('Konfirmasi');
    }

    public function test_farmer_cannot_touch_others_payment(): void
    {
        $theirs = $this->order($this->other, proof: 'x.jpg');
        $this->actingAs($this->farmer);

        $this->patch("/petani/pembayaran/{$theirs->payment->id}/konfirmasi")->assertNotFound();
        $this->patch("/petani/pembayaran/{$theirs->payment->id}/tolak")->assertNotFound();
        $this->assertSame('menunggu', $theirs->payment->fresh()->status);
    }
}
