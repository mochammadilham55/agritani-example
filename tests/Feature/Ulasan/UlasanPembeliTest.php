<?php

namespace Tests\Feature\Ulasan;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Anggota 4: ulasan pembeli (buat, edit, validasi, tampil di halaman produk).
 */
class UlasanPembeliTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $farmer = $this->user('petani', 'f@t.test');
        $this->buyer = $this->user('pembeli', 'b@t.test');
        $cat = Category::create(['name' => 'Sayur', 'slug' => 'sayur']);
        $this->product = Product::create(['user_id' => $farmer->id, 'category_id' => $cat->id, 'name' => 'Bayam',
            'slug' => 'bayam', 'price' => 5000, 'stock' => 10, 'unit' => 'kg', 'is_active' => true]);
    }

    private function user(string $role, string $email): User
    {
        return User::create(['name' => ucfirst($role), 'email' => $email, 'password' => 'password', 'role' => $role, 'is_active' => true]);
    }

    private function order(string $status = 'selesai', ?User $buyer = null): Order
    {
        $o = Order::create(['user_id' => ($buyer ?? $this->buyer)->id, 'total_price' => 10000, 'status' => $status, 'shipping_address' => 'x']);
        OrderItem::create(['order_id' => $o->id, 'product_id' => $this->product->id, 'quantity' => 2, 'price' => 5000, 'subtotal' => 10000]);

        return $o;
    }

    public function test_review_flow_creates_unapproved_review(): void
    {
        $o = $this->order();
        $this->actingAs($this->buyer);

        $this->get('/pembeli/ulasan')->assertOk()->assertSee('Beri Ulasan');
        $this->get("/pembeli/pesanan/{$o->id}/ulasan/bayam")->assertOk();
        $this->post("/pembeli/pesanan/{$o->id}/ulasan/bayam", ['rating' => 4, 'comment' => 'Segar'])
            ->assertRedirect('/pembeli/ulasan');

        $r = Review::first();
        $this->assertSame(4, $r->rating);
        $this->assertFalse($r->is_approved);
        $this->assertSame($this->buyer->id, $r->user_id);

        $this->get('/pembeli/ulasan')->assertSee('Menunggu moderasi')->assertDontSee('Beri Ulasan');
        $this->get("/produk/bayam")->assertDontSee('Segar'); // belum disetujui -> tidak publik
    }

    public function test_approved_review_shows_on_product_page(): void
    {
        $o = $this->order();
        Review::create(['order_id' => $o->id, 'user_id' => $this->buyer->id, 'product_id' => $this->product->id,
            'rating' => 5, 'comment' => 'Mantap sekali', 'is_approved' => true]);

        $this->get('/produk/bayam')->assertSee('Mantap sekali');
    }

    public function test_cannot_review_unfinished_foreign_or_unrelated(): void
    {
        $pending = $this->order('dikirim');
        $this->actingAs($this->buyer)->get("/pembeli/pesanan/{$pending->id}/ulasan/bayam")->assertForbidden();
        $this->post("/pembeli/pesanan/{$pending->id}/ulasan/bayam", ['rating' => 5])->assertForbidden();

        $other = $this->user('pembeli', 'o@t.test');
        $foreign = $this->order('selesai', $other);
        $this->get("/pembeli/pesanan/{$foreign->id}/ulasan/bayam")->assertNotFound();

        $cat = Category::first();
        $unrelated = Product::create(['user_id' => $this->product->user_id, 'category_id' => $cat->id, 'name' => 'Kol',
            'slug' => 'kol', 'price' => 1000, 'stock' => 1, 'unit' => 'kg', 'is_active' => true]);
        $mine = $this->order();
        $this->get("/pembeli/pesanan/{$mine->id}/ulasan/{$unrelated->slug}")->assertNotFound();

        $this->assertSame(0, Review::count());
    }

    public function test_no_duplicate_review_and_rating_validation(): void
    {
        $o = $this->order();
        $this->actingAs($this->buyer);

        foreach ([0, 6, 'x', null] as $bad) {
            $this->post("/pembeli/pesanan/{$o->id}/ulasan/bayam", ['rating' => $bad])->assertSessionHasErrors('rating');
        }

        $this->post("/pembeli/pesanan/{$o->id}/ulasan/bayam", ['rating' => 5]);
        $this->post("/pembeli/pesanan/{$o->id}/ulasan/bayam", ['rating' => 1])->assertSessionHas('error');
        $this->assertSame(1, Review::count());
        $this->assertSame(5, Review::first()->rating);
    }

    public function test_editing_resets_approval_and_is_owner_only(): void
    {
        $o = $this->order();
        $r = Review::create(['order_id' => $o->id, 'user_id' => $this->buyer->id, 'product_id' => $this->product->id,
            'rating' => 5, 'comment' => 'ok', 'is_approved' => true]);

        $other = $this->user('pembeli', 'o@t.test');
        $this->actingAs($other)->get("/pembeli/ulasan/{$r->id}/edit")->assertNotFound();
        $this->put("/pembeli/ulasan/{$r->id}", ['rating' => 1])->assertNotFound();

        $this->actingAs($this->buyer)->put("/pembeli/ulasan/{$r->id}", ['rating' => 3, 'comment' => 'biasa'])
            ->assertRedirect('/pembeli/ulasan');
        $r->refresh();
        $this->assertSame(3, $r->rating);
        $this->assertFalse($r->is_approved);
    }

    public function test_order_page_links_to_review_only_when_selesai(): void
    {
        $done = $this->order();
        $shipping = $this->order('dikirim');
        $this->actingAs($this->buyer);

        $this->get("/pembeli/pesanan/{$done->id}")->assertSee('Beri ulasan');
        $this->get("/pembeli/pesanan/{$shipping->id}")->assertDontSee('Beri ulasan');
    }
}
