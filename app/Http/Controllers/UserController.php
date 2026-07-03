<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name',           'like', '%'.$request->search.'%')
                ->orWhere('email',        'like', '%'.$request->search.'%')
                ->orWhere('nip_nim',      'like', '%'.$request->search.'%')
                ->orWhere('program_studi','like', '%'.$request->search.'%');
            });
        }

        if ($request->filled('role'))   $query->where('role',      $request->role);
        if ($request->filled('status')) $query->where('is_active', $request->status === 'aktif' ? 1 : 0);

        $userList = $query->orderBy('role')->orderBy('name')->paginate(15)->withQueryString();

        $stats = [
            'total'     => User::count(),
            'admin'     => User::admin()->count(),
            'dosen'     => User::dosen()->count(),
            'mahasiswa' => User::mahasiswa()->count(),
            'nonaktif'  => User::where('is_active', false)->count(),
        ];

        return view('admin.users.index', compact('userList', 'stats'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'email'         => 'required|email|unique:users,email',
            'password'      => ['required', 'confirmed', PasswordRule::min(8)],
            'role'          => 'required|in:admin,dosen,mahasiswa',
            'nip_nim'       => 'nullable|string|max:30|unique:users,nip_nim',
            'program_studi' => 'nullable|string|max:100',
            'no_hp'         => 'nullable|string|max:20',
            'is_active'     => 'boolean',
        ], [
            'email.unique'   => 'Email sudah digunakan.',
            'nip_nim.unique' => 'NIP/NIM sudah digunakan.',
        ]);

        $validated['password']  = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', "Pengguna {$validated['name']} berhasil ditambahkan.");
    }

    public function show(User $user)
    {
        $user->load('reservasi.ruangKelas', 'jadwalTetap.ruangKelas');

        $stats = [
            'total_reservasi'     => $user->reservasi()->count(),
            'reservasi_disetujui' => $user->reservasi()->disetujui()->count(),
            'total_jadwal'        => $user->isDosen() ? $user->jadwalTetap()->aktif()->count() : 0,
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'email'         => 'required|email|unique:users,email,'.$user->id,
            'role'          => 'required|in:admin,dosen,mahasiswa',
            'nip_nim'       => 'nullable|string|max:30|unique:users,nip_nim,'.$user->id,
            'program_studi' => 'nullable|string|max:100',
            'no_hp'         => 'nullable|string|max:20',
            'is_active'     => 'boolean',
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => ['confirmed', PasswordRule::min(8)]]);
            $validated['password'] = Hash::make($request->password);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        if ($user->id === auth()->id() && !$validated['is_active']) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', "Data {$user->name} berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        if ($user->reservasi()->whereIn('status', ['menunggu','disetujui'])->exists()) {
            return back()->with('error', "Pengguna {$user->name} masih memiliki reservasi aktif.");
        }

        $nama = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "Pengguna {$nama} berhasil dihapus.");
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat mengubah status akun sendiri.');
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Akun {$user->name} berhasil {$status}.");
    }

    /**
     * FIX KRITIS: Reset password via Laravel built-in broker
     * Kirim link reset ke email user, bukan set 'password' hardcoded
     */
    public function resetPassword(User $user)
    {
        // Gunakan Laravel Password Broker — kirim link reset ke email
        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success',
                "Link reset password telah dikirim ke email {$user->email}."
            );
        }

        return back()->with('error',
            "Gagal mengirim link reset password. Pastikan konfigurasi email sudah benar."
        );
    }
}