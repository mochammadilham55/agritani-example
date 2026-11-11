<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KatalogKeranjangTest extends TestCase
{
    use RefreshDatabase;

    private Category $cat;
    private User $farmer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cat = Category::create(['name' => 'Sayur', 'slug' => 'sayur']);
        $this->farmer = $this->user('petani', 'f@t.test');
    }

    private function user(string $role, string $email): User
    {
        return User::create(['name' => ucfirst($role), 'email' => $email, 'password' => 'password', 'role' => $role, 'is_active' => true]);
    }

    private function product(string $name, int $price = 5000, int $stock = 10, bool $active = true, ?Category $cat = null): Product
    {
        return Product::create([
            'user_id' => $this->farmer->id, 'category_id' => ($cat ?? $this->cat)->id,
            'name' => $name, 'slug' => str($name)->slug(), 'price' => $price, 'stock' => $stock,
            'unit' => 'kg', 'weight' => 1000, 'is_active' => $active,
        ]);
    }

    public function test_catalog_is_public_and_hides_inactive_products(): void
    {
        $this->product('Bayam');
        $this->product('Rahasia', active: false);

        $this->get('/')->assertOk()->assertSee('Bayam')->assertDontSee('Rahasia');
    }

    public function test_catalog_filters_search_and_sort(): void
    {
        $buah = Category::create(['name' => 'Buah', 'slug' => 'buah']);
        $this->product('Bayam', 5000);
        $this->product('Mangga', 25000, cat: $buah);
        $this->product('Apel', 15000, cat: $buah);

        $this->get('/?kategori=buah')->assertSee('Mangga')->assertDontSee('Bayam');
        $this->get('/?q=bay')->assertSee('Bayam')->assertDontSee('Mangga');
        $this->get('/?urut=desc')->assertSeeInOrder(['Mangga', 'Apel', 'Bayam']);
        $this->get('/?urut=asc')->assertSeeInOrder(['Bayam', 'Apel', 'Mangga']);
    }

    public function test_product_detail_and_inactive_404(): void
    {
        $this->product('Bayam');
        $this->product('Rahasia', active: false);

        $this->get('/produk/bayam')->assertOk()->assertSee('Bayam')->assertSee('Tambah ke Keranjang');
        $this->get('/produk/rahasia')->assertNotFound();
    }

    public function test_guest_adding_to_cart_is_sent_to_login(): void
    {
        $this->product('Bayam');
        $this->post('/pembeli/keranjang/bayam')->assertRedirect('/login');
    }

    public function test_farmer_cannot_use_cart(): void
    {
        $this->product('Bayam');
        $this->actingAs($this->farmer)->post('/pembeli/keranjang/bayam')->assertForbidden();
    }

    public function test_add_accumulates_and_is_capped_by_stock(): void
    {
        $p = $this->product('Bayam', stock: 5);
        $buyer = $this->user('pembeli', 'b@t.test');

        $this->actingAs($buyer)->post('/pembeli/keranjang/bayam', ['quantity' => 3]);
        $this->post('/pembeli/keranjang/bayam', ['quantity' => 4])->assertSessionHas('success');

        $this->assertSame(1, Cart::count());
        $this->assertSame(5, Cart::first()->quantity);
    }

    public function test_cannot_add_out_of_stock_or_inactive(): void
    {
        $this->product('Habis', stock: 0);
        $this->product('Mati', active: false);
        $buyer = $this->user('pembeli', 'b@t.test');

        $this->actingAs($buyer)->post('/pembeli/keranjang/habis')->assertSessionHas('error');
        $this->post('/pembeli/keranjang/mati')->assertSessionHas('error');
        $this->assertSame(0, Cart::count());
    }

    public function test_update_delete_and_total(): void
    {
        $p = $this->product('Bayam', 5000, 10);
        $buyer = $this->user('pembeli', 'b@t.test');
        $cart = Cart::create(['user_id' => $buyer->id, 'product_id' => $p->id, 'quantity' => 1]);

        $this->actingAs($buyer)->patch("/pembeli/keranjang/{$cart->id}", ['quantity' => 99]);
        $this->assertSame(10, $cart->fresh()->quantity);

        $this->patch("/pembeli/keranjang/{$cart->id}", ['quantity' => 2]);
        $this->get('/pembeli/keranjang')->assertOk()->assertSee('Rp 10.000');

        $this->delete("/pembeli/keranjang/{$cart->id}");
        $this->assertSame(0, Cart::count());
    }

    public function test_cannot_touch_another_buyers_cart(): void
    {
        $p = $this->product('Bayam');
        $owner = $this->user('pembeli', 'o@t.test');
        $intruder = $this->user('pembeli', 'i@t.test');
        $cart = Cart::create(['user_id' => $owner->id, 'product_id' => $p->id, 'quantity' => 1]);

        $this->actingAs($intruder)->patch("/pembeli/keranjang/{$cart->id}", ['quantity' => 5])->assertNotFound();
        $this->delete("/pembeli/keranjang/{$cart->id}")->assertNotFound();
        $this->assertSame(1, $cart->fresh()->quantity);
    }
}
