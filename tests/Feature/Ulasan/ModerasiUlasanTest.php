<?php

namespace Tests\Feature\Ulasan;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Anggota 4: moderasi ulasan oleh admin (setujui, tolak).
 */
class ModerasiUlasanTest extends TestCase
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

    private function review(bool $approved = false): Review
    {
        $o = $this->sale($this->product(), 1);

        return Review::create(['order_id' => $o->id, 'user_id' => $this->buyer->id, 'product_id' => $o->items->first()->product_id,
            'rating' => 5, 'comment' => 'Bagus banget', 'is_approved' => $approved]);
    }

    public function test_approve_review_publishes_it(): void
    {
        $r = $this->review();
        $this->actingAs($this->admin)->get('/admin/ulasan')->assertSee('Bagus banget');
        $this->get('/produk/bayam')->assertDontSee('Bagus banget');

        $this->patch("/admin/ulasan/{$r->id}/setujui")->assertSessionHas('success');
        $this->assertTrue($r->fresh()->is_approved);
        $this->get('/produk/bayam')->assertSee('Bagus banget');
        $this->get('/admin/ulasan')->assertDontSee('Bagus banget'); // antrean "menunggu" kosong
        $this->get('/admin/ulasan?status=disetujui')->assertSee('Bagus banget');

        $this->patch("/admin/ulasan/{$r->id}/setujui")->assertSessionHas('error');
    }

    public function test_reject_review_deletes_it_and_buyer_can_rewrite(): void
    {
        $r = $this->review();
        $orderId = $r->order_id;
        $this->actingAs($this->admin)->delete("/admin/ulasan/{$r->id}")->assertSessionHas('success');
        $this->assertSame(0, Review::count());

        $this->actingAs($this->buyer)->post("/pembeli/pesanan/{$orderId}/ulasan/bayam", ['rating' => 4, 'comment' => 'Revisi'])
            ->assertRedirect('/pembeli/ulasan');
        $this->assertSame(1, Review::count());
    }

    public function test_moderation_page_renders_and_is_admin_only(): void
    {
        $r = $this->review();
        $this->actingAs($this->admin)->get('/admin/ulasan')->assertOk()->assertSee('Setujui');

        foreach ([$this->farmer, $this->buyer] as $u) {
            $this->actingAs($u)->get('/admin/ulasan')->assertForbidden();
            $this->patch("/admin/ulasan/{$r->id}/setujui")->assertForbidden();
        }
        $this->assertFalse($r->fresh()->is_approved);
    }
}
