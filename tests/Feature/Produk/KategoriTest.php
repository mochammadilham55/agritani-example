<?php

namespace Tests\Feature\Produk;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Anggota 2: admin mengelola kategori produk.
 */
class KategoriTest extends TestCase
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

    private function user(string $role, string $email): User
    {
        return User::create(['name' => ucfirst($role).' '.$email, 'email' => $email, 'password' => 'password', 'role' => $role, 'is_active' => true]);
    }

    private function product(string $name = 'Bayam'): Product
    {
        return Product::create(['user_id' => $this->farmer->id, 'category_id' => $this->cat->id, 'name' => $name,
            'slug' => str($name)->slug(), 'price' => 5000, 'stock' => 10, 'unit' => 'kg', 'is_active' => true]);
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

    public function test_category_page_is_admin_only(): void
    {
        $this->actingAs($this->admin)->get('/admin/kategori')->assertOk();

        foreach ([$this->farmer, $this->buyer] as $u) {
            $this->actingAs($u)->get('/admin/kategori')->assertForbidden();
        }
    }
}
