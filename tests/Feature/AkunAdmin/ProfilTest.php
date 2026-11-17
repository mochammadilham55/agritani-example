<?php

namespace Tests\Feature\AkunAdmin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Anggota 1: profil pengguna (data diri dan foto) untuk semua role.
 */
class ProfilTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buyer = $this->user('pembeli', 'b@t.test');
    }

    private function user(string $role, string $email): User
    {
        return User::create(['name' => ucfirst($role), 'email' => $email, 'password' => 'password', 'role' => $role, 'is_active' => true]);
    }

    public function test_profile_update_cannot_change_role_or_active(): void
    {
        Storage::fake('public');
        $this->actingAs($this->buyer)->get('/profil')->assertOk();

        $this->put('/profil', [
            'name' => 'Nama Baru', 'email' => 'baru@t.test', 'phone' => '0812', 'address' => 'Jl. Baru',
            'photo' => UploadedFile::fake()->image('me.jpg'),
            'role' => 'admin', 'is_active' => 0,
        ])->assertSessionHas('success');

        $u = $this->buyer->fresh();
        $this->assertSame('Nama Baru', $u->name);
        $this->assertSame('baru@t.test', $u->email);
        $this->assertSame('pembeli', $u->role);
        $this->assertTrue($u->is_active);
        Storage::disk('public')->assertExists($u->photo);
    }

    public function test_profile_email_must_be_unique_but_own_email_is_allowed(): void
    {
        $this->user('pembeli', 'taken@t.test');
        $this->actingAs($this->buyer);

        $this->put('/profil', ['name' => 'X', 'email' => 'taken@t.test'])->assertSessionHasErrors('email');
        $this->put('/profil', ['name' => 'X', 'email' => 'b@t.test'])->assertSessionHasNoErrors();
    }

    public function test_password_change_requires_current_password(): void
    {
        $this->actingAs($this->buyer);

        $this->put('/profil/password', ['current_password' => 'salah', 'password' => 'passwordbaru1', 'password_confirmation' => 'passwordbaru1'])
            ->assertSessionHasErrors('current_password');
        $this->put('/profil/password', ['current_password' => 'password', 'password' => 'pendek', 'password_confirmation' => 'pendek'])
            ->assertSessionHasErrors('password');

        $this->put('/profil/password', ['current_password' => 'password', 'password' => 'passwordbaru1', 'password_confirmation' => 'passwordbaru1'])
            ->assertSessionHas('success');
        $this->assertTrue(Hash::check('passwordbaru1', $this->buyer->fresh()->password));
    }

    public function test_every_role_can_change_own_password_from_profile(): void
    {
        foreach (['pembeli', 'petani', 'admin'] as $role) {
            $user = $this->user($role, "$role-pw@t.test");
            $this->actingAs($user)->get('/profil')->assertOk()->assertSee('Ubah Password');
            $this->put('/profil/password', ['current_password' => 'password', 'password' => 'rahasia123', 'password_confirmation' => 'rahasia123'])
                ->assertSessionHas('success');
            $this->assertTrue(Hash::check('rahasia123', $user->fresh()->password));
        }
    }

    public function test_profile_is_available_to_all_roles_but_not_guests(): void
    {
        $this->get('/profil')->assertRedirect('/login');
        foreach (['petani', 'admin'] as $role) {
            $this->actingAs($this->user($role, "$role@x.test"))->get('/profil')->assertOk();
        }
    }
}
