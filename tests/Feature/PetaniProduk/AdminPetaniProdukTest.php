<?php

namespace Tests\Feature\PetaniProduk;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Anggota 1: admin mengelola petani (verifikasi/tangguhkan), produk (turunkan), dan kategori.
 */
class AdminPetaniProdukTest extends TestCase
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

    public function test_verify_and_suspend_farmer(): void
    {
        $new = $this->user('petani', 'new@t.test', active: false);
        $this->actingAs($this->admin);

        $this->patch("/admin/petani/{$new->id}/toggle")->assertSessionHas('success');
        $this->assertTrue($new->fresh()->is_active);

        $this->patch("/admin/petani/{$new->id}/toggle");
        $this->assertFalse($new->fresh()->is_active);
    }

    public function test_toggle_only_applies_to_farmers(): void
    {
        $this->actingAs($this->admin)->patch("/admin/petani/{$this->buyer->id}/toggle")->assertNotFound();
        $this->patch("/admin/petani/{$this->admin->id}/toggle")->assertNotFound();
        $this->assertTrue($this->buyer->fresh()->is_active);
        $this->assertTrue($this->admin->fresh()->is_active);
    }

    public function test_farmer_list_filters(): void
    {
        $this->user('petani', 'mati@t.test', active: false);
        $this->actingAs($this->admin);

        $this->get('/admin/petani?status=nonaktif')->assertSee('mati@t.test')->assertDontSee('f@t.test');
        $this->get('/admin/petani?status=aktif')->assertSee('f@t.test')->assertDontSee('mati@t.test');
        $this->get('/admin/petani?q=mati')->assertSee('mati@t.test')->assertDontSee('f@t.test');
    }

    public function test_suspended_farmer_products_vanish_from_catalog_cart_and_checkout(): void
    {
        $p = $this->product();
        Cart::create(['user_id' => $this->buyer->id, 'product_id' => $p->id, 'quantity' => 2]);
        $this->get('/')->assertSee('Bayam');

        $this->actingAs($this->admin)->patch("/admin/petani/{$this->farmer->id}/toggle");

        $this->get('/')->assertDontSee('Bayam');
        $this->get('/produk/bayam')->assertNotFound();

        $this->actingAs($this->buyer)->post('/pembeli/keranjang/bayam')->assertSessionHas('error');
        $this->post('/pembeli/checkout', ['shipping_address' => 'Jl. Mawar 5 Bandung', 'method' => 'cod'])
            ->assertRedirect('/pembeli/keranjang')->assertSessionHas('error');
        $this->assertSame(0, Order::count());
        $this->assertSame(10, $p->fresh()->stock);
    }

    public function test_suspend_product_requires_reason_and_hides_it(): void
    {
        $p = $this->product();
        $this->actingAs($this->admin);

        $this->patch("/admin/produk/{$p->id}/toggle")->assertSessionHasErrors('reason');
        $this->assertFalse($p->fresh()->is_suspended);

        $this->patch("/admin/produk/{$p->id}/toggle", ['reason' => 'Foto tidak sesuai'])->assertSessionHas('success');
        $p->refresh();
        $this->assertTrue($p->is_suspended);
        $this->assertSame('Foto tidak sesuai', $p->suspend_reason);

        $this->get('/')->assertDontSee('Bayam');
        $this->get('/produk/bayam')->assertNotFound();
    }

    public function test_farmer_cannot_reactivate_suspended_product_but_admin_can_restore(): void
    {
        $p = $this->product();
        $this->actingAs($this->admin)->patch("/admin/produk/{$p->id}/toggle", ['reason' => 'Palsu']);

        $this->actingAs($this->farmer)->patch("/petani/produk/{$p->slug}/toggle")->assertSessionHas('error');
        $this->get('/petani/produk')->assertSee('Diturunkan admin')->assertSee('Palsu');
        // Edit biasa dengan is_active dicentang pun tidak memulihkan produk.
        $this->put("/petani/produk/{$p->slug}", ['name' => 'Bayam', 'category_id' => $this->cat->id, 'price' => 5000,
            'stock' => 5, 'unit' => 'kg', 'weight' => 100, 'is_active' => 1]);
        $this->assertTrue($p->fresh()->is_suspended);
        $this->get('/')->assertDontSee('Bayam');

        $this->actingAs($this->admin)->patch("/admin/produk/{$p->id}/toggle")->assertSessionHas('success');
        $this->assertFalse($p->fresh()->is_suspended);
        $this->assertNull($p->fresh()->suspend_reason);
        $this->get('/')->assertSee('Bayam');
    }

    public function test_category_crud_with_unique_slug_and_name(): void
    {
        $this->actingAs($this->admin);

        $this->post('/admin/kategori', ['name' => 'Buah Segar'])->assertSessionHas('success');
        $this->post('/admin/kategori', ['name' => 'Buah Segar'])->assertSessionHasErrors('name');
        $this->post('/admin/kategori', ['name' => 'Buah  Segar!'])->assertSessionHas('success'); // slug bentrok -> -2
        $this->assertSame(['buah-segar', 'buah-segar-2'], Category::where('slug', 'like', 'buah%')->orderBy('id')->pluck('slug')->all());

        $c = Category::where('slug', 'buah-segar')->first();
        $this->get("/admin/kategori/{$c->slug}/edit")->assertOk();
        $this->put("/admin/kategori/{$c->slug}", ['name' => 'Buah Pilihan', 'description' => 'baru'])->assertRedirect('/admin/kategori');
        $this->assertSame('Buah Pilihan', $c->fresh()->name);
        $this->assertSame('buah-segar', $c->fresh()->slug);

        $this->put("/admin/kategori/{$c->slug}", ['name' => 'Sayur'])->assertSessionHasErrors('name'); // nama milik kategori lain
        $this->put("/admin/kategori/{$c->slug}", ['name' => 'Buah Pilihan'])->assertSessionHasNoErrors(); // nama sendiri boleh
    }

    public function test_cannot_delete_category_in_use_but_can_delete_empty(): void
    {
        $this->product();
        $empty = Category::create(['name' => 'Kosong', 'slug' => 'kosong']);
        $this->actingAs($this->admin);

        $this->delete("/admin/kategori/{$this->cat->slug}")->assertSessionHas('error');
        $this->assertNotNull(Category::find($this->cat->id));

        $this->delete("/admin/kategori/{$empty->slug}")->assertSessionHas('success');
        $this->assertNull(Category::find($empty->id));
    }

    public function test_admin_pages_render_and_are_admin_only(): void
    {
        $this->product();
        $this->actingAs($this->admin);
        foreach (['petani', 'produk', 'kategori'] as $p) {
            $this->get("/admin/$p")->assertOk();
        }

        foreach ([$this->farmer, $this->buyer] as $u) {
            $this->actingAs($u)->get('/admin/petani')->assertForbidden();
            $this->get('/admin/produk')->assertForbidden();
            $this->get('/admin/kategori')->assertForbidden();
        }
    }
}
