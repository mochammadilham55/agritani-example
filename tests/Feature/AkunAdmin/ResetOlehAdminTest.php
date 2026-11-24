<?php

namespace Tests\Feature\AkunAdmin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Anggota 1: tombol "Hubungi Admin via WhatsApp", reset password oleh admin,
 * dan kewajiban mengganti password sementara.
 */
class ResetOlehAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->user('admin', 'admin@t.test');
        $this->buyer = $this->user('pembeli', 'b@t.test', '081234567890');
    }

    private function user(string $role, string $email, ?string $phone = null): User
    {
        return User::create(['name' => ucfirst($role), 'email' => $email, 'password' => 'password-lama',
            'role' => $role, 'phone' => $phone, 'is_active' => true]);
    }

    public function test_whatsapp_button_uses_configured_number(): void
    {
        config(['agritani.admin_whatsapp' => '6287800000000']);

        $this->get('/lupa-password')->assertOk()
            ->assertSee('Hubungi Admin via WhatsApp')
            ->assertSee('https://wa.me/6287800000000?text=', false);
        $this->get('/login')->assertOk()->assertSee('https://wa.me/6287800000000', false);
    }

    public function test_whatsapp_button_hidden_without_number(): void
    {
        config(['agritani.admin_whatsapp' => '']);

        $this->get('/lupa-password')->assertOk()->assertDontSee('wa.me', false);
        $this->get('/login')->assertOk()->assertDontSee('wa.me', false);
    }

    public function test_admin_sees_buyers_and_farmers_and_can_search(): void
    {
        $farmer = $this->user('petani', 'f@t.test');

        $this->actingAs($this->admin)->get('/admin/pengguna')->assertOk()
            ->assertSee('b@t.test')->assertSee('f@t.test')->assertDontSee('admin@t.test');

        $this->actingAs($this->admin)->get('/admin/pengguna?q=081234567890')->assertOk()
            ->assertSee('b@t.test')->assertDontSee('f@t.test');

        $this->actingAs($this->admin)->get('/admin/pengguna?role=petani')->assertOk()
            ->assertSee($farmer->email)->assertDontSee('b@t.test');
    }

    public function test_admin_resets_password_and_sees_temporary_password_once(): void
    {
        $response = $this->actingAs($this->admin)
            ->from('/admin/pengguna')
            ->patch("/admin/pengguna/{$this->buyer->id}/reset-password")
            ->assertSessionHas('success');

        $temp = session('temporary_password')['password'];
        $this->assertMatchesRegularExpression('/^Tani-[A-Z0-9]{4}\d{4}$/', $temp);

        $fresh = $this->buyer->fresh();
        $this->assertTrue($fresh->must_change_password);
        $this->assertTrue(Hash::check($temp, $fresh->password));
        $this->assertFalse(Hash::check('password-lama', $fresh->password));

        $this->followRedirects($response)->assertSee($temp);
        $this->actingAs($this->admin)->get('/admin/pengguna')->assertDontSee($temp);
    }

    public function test_admin_account_cannot_be_reset_and_non_admin_cannot_reset(): void
    {
        $otherAdmin = $this->user('admin', 'admin2@t.test');
        $this->actingAs($this->admin)->patch("/admin/pengguna/{$otherAdmin->id}/reset-password")->assertNotFound();

        $this->actingAs($this->buyer)->get('/admin/pengguna')->assertForbidden();
        $this->actingAs($this->buyer)->patch("/admin/pengguna/{$this->buyer->id}/reset-password")->assertForbidden();
    }

    public function test_user_with_temporary_password_must_change_it_first(): void
    {
        $this->buyer->forceFill(['password' => 'Tani-ABCD1234', 'must_change_password' => true])->save();

        $this->post('/login', ['email' => 'b@t.test', 'password' => 'Tani-ABCD1234']);
        $this->get('/pembeli/dashboard')->assertRedirect('/profil')->assertSessionHas('warning');
        $this->get('/')->assertRedirect('/profil');
        $this->get('/profil')->assertOk()->assertSee('password sementara');

        $this->put('/profil/password', [
            'current_password' => 'Tani-ABCD1234',
            'password' => 'password-baru-123',
            'password_confirmation' => 'password-baru-123',
        ])->assertSessionHas('success');

        $this->assertFalse($this->buyer->fresh()->must_change_password);
        $this->get('/pembeli/dashboard')->assertOk();
    }

    public function test_temporary_password_user_can_still_logout(): void
    {
        $this->buyer->forceFill(['must_change_password' => true])->save();

        $this->actingAs($this->buyer)->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }
}
