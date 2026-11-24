<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Anggota 1: daftar semua pembeli dan petani, serta reset password oleh admin
 * (dipakai saat pengguna menghubungi admin lewat WhatsApp karena lupa password).
 */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::whereIn('role', [User::ROLE_PEMBELI, User::ROLE_PETANI])
            ->when($request->query('q'), fn ($q, $term) => $q->where(
                fn ($w) => $w->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
            ))
            ->when(in_array($request->query('role'), [User::ROLE_PEMBELI, User::ROLE_PETANI], true),
                fn ($q) => $q->where('role', $request->query('role')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', ['users' => $users]);
    }

    /** Buat password sementara. Ditampilkan sekali ke admin; pengguna wajib menggantinya saat login. */
    public function resetPassword(User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 404);

        $temporary = 'Tani-'.Str::upper(Str::random(4)).random_int(1000, 9999);

        $user->forceFill([
            'password' => $temporary, // di-hash otomatis oleh cast
            'must_change_password' => true,
            'remember_token' => Str::random(60),
        ])->save();

        return back()
            ->with('success', "Password {$user->name} direset. Sampaikan password sementara di bawah ke pengguna.")
            ->with('temporary_password', ['name' => $user->name, 'email' => $user->email, 'password' => $temporary]);
    }
}
