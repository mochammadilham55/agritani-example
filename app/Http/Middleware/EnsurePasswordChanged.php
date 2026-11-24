<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Anggota 1: pengguna yang login dengan password sementara dari admin
 * hanya boleh membuka halaman profil (untuk mengganti password) dan logout.
 */
class EnsurePasswordChanged
{
    private const ALLOWED_ROUTES = ['profile.edit', 'profile.password', 'logout'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->must_change_password && ! $request->routeIs(...self::ALLOWED_ROUTES)) {
            return redirect()->route('profile.edit')
                ->with('warning', 'Anda masuk dengan password sementara dari admin. Ganti password terlebih dahulu.');
        }

        return $next($request);
    }
}
