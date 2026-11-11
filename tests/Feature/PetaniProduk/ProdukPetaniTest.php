<?php

namespace Tests\Feature\PetaniProduk;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Anggota 1: produk milik petani (tambah, ubah, aktif/nonaktif, kepemilikan).
 */
class ProdukPetaniTest extends TestCase
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

    public function test_create_product_with_image_and_unique_slug(): void
    {
        Storage::fake('public');
        $this->actingAs($this->farmer);
        $payload = fn () => ['name' => 'Cabai Merah', 'category_id' => $this->cat->id, 'price' => 40000, 'stock' => 5,
            'unit' => 'kg', 'weight' => 1000, 'is_active' => 1, 'image' => UploadedFile::fake()->image('c.jpg')];

        $this->post('/petani/produk', $payload())->assertRedirect('/petani/produk');
        $this->post('/petani/produk', $payload());

        $this->assertSame(['cabai-merah', 'cabai-merah-2'], Product::orderBy('id')->where('name', 'Cabai Merah')->pluck('slug')->all());
        Storage::disk('public')->assertExists(Product::first()->image);
        $this->assertSame($this->farmer->id, Product::first()->user_id);
    }

    public function test_product_validation(): void
    {
        $this->actingAs($this->farmer)->post('/petani/produk', ['name' => '', 'price' => -1, 'stock' => 'x'])
            ->assertSessionHasErrors(['name', 'category_id', 'price', 'stock', 'unit', 'weight']);
    }

    public function test_farmer_cannot_edit_or_toggle_others_product(): void
    {
        $theirs = $this->product($this->other, 'Kol');
        $this->actingAs($this->farmer);

        $this->get("/petani/produk/{$theirs->slug}/edit")->assertNotFound();
        $this->put("/petani/produk/{$theirs->slug}", ['name' => 'Hack'])->assertNotFound();
        $this->patch("/petani/produk/{$theirs->slug}/toggle")->assertNotFound();
        $this->assertTrue($theirs->fresh()->is_active);
    }

    public function test_update_keeps_slug_and_toggle_deactivates(): void
    {
        $p = $this->product($this->farmer);
        $this->actingAs($this->farmer)->put("/petani/produk/{$p->slug}", [
            'name' => 'Nama Baru', 'category_id' => $this->cat->id, 'price' => 6000, 'stock' => 3, 'unit' => 'kg', 'weight' => 500,
        ])->assertRedirect('/petani/produk');

        $p->refresh();
        $this->assertSame('Nama Baru', $p->name);
        $this->assertStringStartsWith('bayam', $p->slug);
        $this->assertFalse($p->is_active); // checkbox tidak dikirim -> nonaktif

        $this->patch("/petani/produk/{$p->slug}/toggle");
        $this->assertTrue($p->fresh()->is_active);
        $this->get("/produk/{$p->slug}")->assertOk();
    }

    public function test_product_pages_render_for_farmer(): void
    {
        $this->actingAs($this->farmer);
        $this->get('/petani/produk')->assertOk();
        $this->get('/petani/produk/create')->assertOk();
    }

    public function test_other_roles_blocked_from_product_area(): void
    {
        $this->actingAs($this->buyer)->get('/petani/produk')->assertForbidden();
        $this->post('/petani/produk', [])->assertForbidden();
    }
}
