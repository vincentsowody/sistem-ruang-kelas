<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Cek: sudah login, akun aktif, dan role sesuai.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // 1. Belum login → redirect ke login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // 2. Akun dinonaktifkan → logout paksa + redirect dengan pesan
        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('info', 'Akun Anda telah dinonaktifkan. Hubungi administrator untuk informasi lebih lanjut.');
        }

        // 3. Role tidak sesuai → 403
        if (!in_array($user->role, $roles)) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
