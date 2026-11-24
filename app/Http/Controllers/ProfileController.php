<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'bank_name' => [Rule::requiredIf($user->isPetani()), 'nullable', 'string', 'max:50'],
            'bank_account_number' => [Rule::requiredIf($user->isPetani()), 'nullable', 'regex:/^[0-9]{6,30}$/'],
            'bank_account_name' => [Rule::requiredIf($user->isPetani()), 'nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'bank_name.required' => 'Nama bank wajib diisi untuk petani.',
            'bank_account_number.required' => 'Nomor rekening wajib diisi untuk petani.',
            'bank_account_number.regex' => 'Nomor rekening harus berupa angka 6-30 digit.',
            'bank_account_name.required' => 'Nama pemilik rekening wajib diisi untuk petani.',
        ]);

        // Pembeli dan admin tidak menyimpan data rekening.
        if (! $user->isPetani()) {
            unset($data['bank_name'], $data['bank_account_number'], $data['bank_account_name']);
        }

        unset($data['photo']);

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $data['photo'] = $request->file('photo')->store('photos', 'public');
        }

        $user->update($data); // role & is_active tidak ada di $data, jadi tidak bisa diubah dari sini

        return back()->with('success', 'Profil diperbarui.');
    }

    /** Ubah password: wajib mengisi password saat ini. */
    public function password(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.current_password' => 'Password saat ini salah.',
        ]);

        $request->user()->update(['password' => $data['password'], 'must_change_password' => false]);

        return back()->with('success', 'Password diperbarui.');
    }
}
