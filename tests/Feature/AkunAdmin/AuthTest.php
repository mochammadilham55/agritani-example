<?php

namespace Tests\Feature\AkunAdmin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, bool $active = true): User
    {
        return User::create([
            'name' => ucfirst($role), 'email' => "$role@t.test", 'password' => 'password',
            'role' => $role, 'is_active' => $active,
        ]);
    }

    public function test_login_and_register_pages_render(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }

    public function test_every_password_field_has_a_show_password_button(): void
    {
        // login: 1 field, register: 2, reset: 2, profil: 3 (saat ini, baru, konfirmasi).
        foreach (['/login' => 1, '/register' => 2, '/reset-password/abc' => 2] as $url => $count) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertSame($count, substr_count($html, 'aria-label="Tampilkan password"'), $url);
            $this->assertSame($count, substr_count($html, 'type="password"'), $url);
        }

        foreach (['pembeli', 'petani', 'admin'] as $role) {
            $html = $this->actingAs($this->user($role))->get('/profil')->assertOk()->getContent();
            $this->assertSame(3, substr_count($html, 'aria-label="Tampilkan password"'), $role);
            $this->assertStringContainsString('id="current_password" name="current_password"', $html);
            $this->post('/logout');
        }
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/admin/dashboard')->assertRedirect('/login');
    }

    public function test_login_redirects_each_role_to_own_dashboard(): void
    {
        foreach (['admin', 'petani', 'pembeli'] as $role) {
            $this->user($role);
            $this->post('/login', ['email' => "$role@t.test", 'password' => 'password'])
                ->assertRedirect('/dashboard');
            $this->get('/dashboard')->assertRedirect("/$role/dashboard");
            $this->get("/$role/dashboard")->assertOk();
            $this->post('/logout');
        }
    }

    public function test_role_cannot_access_other_role_area(): void
    {
        $this->actingAs($this->user('pembeli'))->get('/admin/dashboard')->assertForbidden();
        $this->actingAs(User::where('role', 'pembeli')->first())->get('/petani/dashboard')->assertForbidden();
    }

    public function test_wrong_password_and_inactive_account_rejected(): void
    {
        $this->user('petani', active: false);
        $this->post('/login', ['email' => 'petani@t.test', 'password' => 'salah'])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => 'petani@t.test', 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_suspended_user_is_kicked_out_by_middleware(): void
    {
        $this->actingAs($this->user('petani', active: false))
            ->get('/petani/dashboard')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_register_pembeli_is_active_and_logged_in(): void
    {
        $this->post('/register', [
            'name' => 'Budi', 'email' => 'b@t.test', 'role' => 'pembeli',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertTrue(User::where('email', 'b@t.test')->first()->is_active);
    }

    public function test_register_petani_needs_admin_verification(): void
    {
        $this->post('/register', [
            'name' => 'Tani', 'email' => 't@t.test', 'role' => 'petani',
            'bank_name' => 'BCA', 'bank_account_number' => '1234567890', 'bank_account_name' => 'Tani',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect('/login');
        $this->assertGuest();
        $u = User::where('email', 't@t.test')->first();
        $this->assertFalse($u->is_active);
        $this->assertSame('1234567890', $u->bank_account_number);
    }

    public function test_register_petani_requires_bank_account(): void
    {
        $this->post('/register', [
            'name' => 'Tani', 'email' => 't@t.test', 'role' => 'petani',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertSessionHasErrors(['bank_name', 'bank_account_number', 'bank_account_name']);
    }

    public function test_register_pembeli_does_not_need_bank_account(): void
    {
        $this->post('/register', [
            'name' => 'Budi', 'email' => 'b@t.test', 'role' => 'pembeli',
            'bank_account_number' => '999999',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect('/dashboard');
        $this->assertNull(User::where('email', 'b@t.test')->first()->bank_account_number);
    }

    public function test_cannot_register_as_admin(): void
    {
        $this->post('/register', [
            'name' => 'X', 'email' => 'x@t.test', 'role' => 'admin',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('role');
    }

    public function test_login_has_no_remember_me_and_never_sets_remember_cookie(): void
    {
        $this->get('/login')->assertOk()->assertDontSee('Ingat saya');

        User::create(['name' => 'Budi', 'email' => 'r@t.test', 'password' => 'password123', 'role' => 'pembeli', 'is_active' => true]);
        $response = $this->post('/login', ['email' => 'r@t.test', 'password' => 'password123', 'remember' => 'on']);

        $this->assertAuthenticated();
        foreach ($response->headers->getCookies() as $cookie) {
            $this->assertStringStartsNotWith('remember_web_', $cookie->getName());
        }
    }
}
