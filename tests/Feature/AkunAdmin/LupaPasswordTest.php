<?php

namespace Tests\Feature\AkunAdmin;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * Anggota 1: lupa password (minta tautan lewat email, lalu atur password baru).
 */
class LupaPasswordTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::create(['name' => ucfirst($role), 'email' => "$role@t.test", 'password' => 'password', 'role' => $role, 'is_active' => true]);
    }

    public function test_pages_render_and_login_links_to_forgot_page(): void
    {
        $this->get('/lupa-password')->assertOk()->assertSee('Lupa Password');
        $this->get('/reset-password/abc?email=a@t.test')->assertOk()->assertSee('Atur Ulang Password');
        $this->get('/login')->assertSee('Lupa password?')->assertSee(route('password.request'), false);
    }

    public function test_reset_link_is_sent_for_every_role(): void
    {
        Notification::fake();

        foreach (['pembeli', 'petani', 'admin'] as $role) {
            $user = $this->user($role);
            $this->post('/lupa-password', ['email' => $user->email])->assertSessionHas('status');
            Notification::assertSentTo($user, ResetPassword::class);
        }
    }

    public function test_email_link_points_to_our_reset_page(): void
    {
        Notification::fake();
        $user = $this->user('pembeli');

        $this->post('/lupa-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $n) use ($user) {
            $url = $n->toMail($user)->actionUrl;

            return str_contains($url, '/reset-password/'.$n->token) && str_contains($url, 'email='.urlencode($user->email));
        });
    }

    public function test_unknown_email_gets_same_message_and_no_notification(): void
    {
        Notification::fake();

        $this->post('/lupa-password', ['email' => 'tidakada@t.test'])->assertSessionHas('status');
        Notification::assertNothingSent();

        $this->post('/lupa-password', ['email' => 'bukan-email'])->assertSessionHasErrors('email');
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = $this->user('pembeli');
        $token = Password::createToken($user);

        $this->post('/reset-password', [
            'token' => $token, 'email' => $user->email,
            'password' => 'passwordbaru1', 'password_confirmation' => 'passwordbaru1',
        ])->assertRedirect('/login')->assertSessionHas('status');

        $this->assertTrue(Hash::check('passwordbaru1', $user->fresh()->password));

        // Token hanya berlaku sekali.
        $this->post('/reset-password', [
            'token' => $token, 'email' => $user->email,
            'password' => 'lainnya12345', 'password_confirmation' => 'lainnya12345',
        ])->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('passwordbaru1', $user->fresh()->password));
    }

    public function test_reset_rejects_wrong_token_and_weak_password(): void
    {
        $user = $this->user('petani');
        $token = Password::createToken($user);

        $this->post('/reset-password', [
            'token' => 'salah', 'email' => $user->email,
            'password' => 'passwordbaru1', 'password_confirmation' => 'passwordbaru1',
        ])->assertSessionHasErrors('email');

        $this->post('/reset-password', [
            'token' => $token, 'email' => $user->email,
            'password' => 'pendek', 'password_confirmation' => 'pendek',
        ])->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_logged_in_user_cannot_open_forgot_page(): void
    {
        $this->actingAs($this->user('pembeli'))->get('/lupa-password')->assertRedirect();
    }
}
