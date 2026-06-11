<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        // Kalau sudah login, langsung redirect sesuai role
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user()->role);
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 6 karakter.',
        ]);

        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Cek apakah akun aktif
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.',
                ]);
            }

            return $this->redirectByRole($user->role);
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => 'Email atau password salah.',
            ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda berhasil logout.');
    }

    private function redirectByRole(string $role)
    {
        return match($role) {
            'admin'      => redirect()->route('admin.dashboard'),
            'dosen'      => redirect()->route('dosen.dashboard'),
            'mahasiswa'  => redirect()->route('mahasiswa.dashboard'),
            default      => redirect('/'),
        };
    }
}
