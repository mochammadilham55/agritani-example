<?php

namespace Tests\Feature\PembayaranPengiriman;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Anggota 3: upload bukti transfer oleh pembeli (validasi, penyimpanan, COD).
 */
class BuktiBayarTest extends TestCase
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

    public function test_upload_proof_validates_and_stores(): void
    {
        Storage::fake('public');
        $farmer = $this->user('petani', 'f@t.test');
        $this->addToCart($this->product($farmer, 'Bayam', 5000, 10), 1);
        $this->checkout();
        $order = Order::first();

        $this->post("/pembeli/pesanan/{$order->id}/bukti", ['proof_image' => UploadedFile::fake()->create('doc.pdf', 10)])
            ->assertSessionHasErrors('proof_image');
        $this->post("/pembeli/pesanan/{$order->id}/bukti", ['proof_image' => UploadedFile::fake()->image('b.jpg')->size(3000)])
            ->assertSessionHasErrors('proof_image');

        $this->post("/pembeli/pesanan/{$order->id}/bukti", ['proof_image' => UploadedFile::fake()->image('b.jpg')])
            ->assertSessionHas('success');

        $path = $order->payment->fresh()->proof_image;
        Storage::disk('public')->assertExists($path);
    }

    public function test_cannot_upload_proof_for_cod(): void
    {
        Storage::fake('public');
        $this->addToCart($this->product($this->user('petani', 'f@t.test'), 'Bayam', 5000, 10), 1);
        $this->checkout('cod');
        $order = Order::first();

        $this->post("/pembeli/pesanan/{$order->id}/bukti", ['proof_image' => UploadedFile::fake()->image('b.jpg')])
            ->assertSessionHas('error');
        $this->assertNull($order->payment->fresh()->proof_image);
    }
}
