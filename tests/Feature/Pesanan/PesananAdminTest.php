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
 * Anggota 2: daftar dan detail pesanan di sisi admin (filter, pencarian).
 */
class PesananAdminTest extends TestCase
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

    public function test_orders_list_filters_by_status_and_search(): void
    {
        $a = $this->sale($this->product('Bayam'), 1, 'pending');
        $other = $this->user('petani', 'f2@t.test');
        $b = $this->sale($this->product('Mangga', $other), 1, 'selesai');
        $this->actingAs($this->admin);

        $this->get('/admin/pesanan?status=selesai')->assertSee("#{$b->id}")->assertDontSee("#{$a->id}<");
        $this->get('/admin/pesanan?q='.urlencode($other->name))->assertViewHas('orders', fn ($p) => $p->pluck('id')->all() === [$b->id]);
        $this->get("/admin/pesanan?q={$a->id}")->assertViewHas('orders', fn ($p) => $p->pluck('id')->contains($a->id));
    }

    public function test_order_pages_render_and_are_admin_only(): void
    {
        $o = $this->sale($this->product(), 1);
        $this->actingAs($this->admin);
        $this->get('/admin/pesanan')->assertOk();
        $this->get("/admin/pesanan/{$o->id}")->assertOk();

        foreach ([$this->farmer, $this->buyer] as $u) {
            $this->actingAs($u)->get('/admin/pesanan')->assertForbidden();
        }
    }
}
