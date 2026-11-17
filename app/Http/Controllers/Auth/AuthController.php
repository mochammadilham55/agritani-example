<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
        }

        if (! Auth::user()->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Akun Anda belum aktif atau ditangguhkan. Hubungi admin.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in([User::ROLE_PEMBELI, User::ROLE_PETANI])],
            'bank_name' => [Rule::requiredIf($request->input('role') === User::ROLE_PETANI), 'nullable', 'string', 'max:50'],
            'bank_account_number' => [Rule::requiredIf($request->input('role') === User::ROLE_PETANI), 'nullable', 'regex:/^[0-9]{6,30}$/'],
            'bank_account_name' => [Rule::requiredIf($request->input('role') === User::ROLE_PETANI), 'nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'bank_name.required' => 'Nama bank wajib diisi untuk petani.',
            'bank_account_number.required' => 'Nomor rekening wajib diisi untuk petani.',
            'bank_account_number.regex' => 'Nomor rekening harus berupa angka 6-30 digit.',
            'bank_account_name.required' => 'Nama pemilik rekening wajib diisi untuk petani.',
        ]);

        if ($data['role'] !== User::ROLE_PETANI) {
            unset($data['bank_name'], $data['bank_account_number'], $data['bank_account_name']);
        }

        // Petani harus diverifikasi admin dulu; pembeli langsung aktif.
        $user = User::create($data + ['is_active' => $data['role'] === User::ROLE_PEMBELI]);

        if (! $user->is_active) {
            return redirect()->route('login')
                ->with('status', 'Pendaftaran berhasil. Akun petani Anda akan aktif setelah diverifikasi admin.');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
