<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    public function index()
    {
        /**
         * BUG FIX 10: index() auto-tandai semua notifikasi sebagai dibaca
         * SEBELUM mengirim data ke view. Akibatnya view tidak pernah bisa
         * membedakan mana yang baru/belum dibaca ($notif->sudah_dibaca selalu true).
         *
         * Selain itu variabel yang dikirim ke view adalah $notifikasi
         * tapi view menggunakan $notifikasiList dan $belumDibaca — undefined variable.
         *
         * FIX: ambil data dulu (dengan flag asli), kirim ke view,
         * lalu tandai dibaca via AJAX/aksi terpisah agar bisa ditampilkan
         * dengan highlight yang benar. Untuk sekarang: kirim data dulu,
         * mark-as-read dilakukan SETELAH view menerima data yang benar.
         */
        $belumDibaca = Notifikasi::where('user_id', Auth::id())
            ->where('sudah_dibaca', false)
            ->count();

        $notifikasiList = Notifikasi::where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        // Tandai semua sebagai dibaca SETELAH data diambil
        Notifikasi::where('user_id', Auth::id())
            ->where('sudah_dibaca', false)
            ->update(['sudah_dibaca' => true, 'dibaca_pada' => now()]);

        return view('notifikasi.index', compact('notifikasiList', 'belumDibaca'));
    }

    // API: jumlah notifikasi belum dibaca (badge navbar)
    public function apiJumlah()
    {
        return response()->json([
            'jumlah' => Auth::user()->notifikasiBelumDibaca()->count(),
        ]);
    }

    // Tandai satu notifikasi sebagai dibaca
    public function read(Notifikasi $notifikasi)
    {
        if ($notifikasi->user_id !== Auth::id()) abort(403);
        $notifikasi->tandaiSudahDibaca();
        return back();
    }

    // Tandai semua sebagai dibaca
    public function readAll()
    {
        Notifikasi::where('user_id', Auth::id())
            ->where('sudah_dibaca', false)
            ->update(['sudah_dibaca' => true, 'dibaca_pada' => now()]);
        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    public function destroy(Notifikasi $notifikasi)
    {
        if ($notifikasi->user_id !== Auth::id()) abort(403);
        $notifikasi->delete();
        return back()->with('success', 'Notifikasi dihapus.');
    }

    public function hapusSemua()
    {
        Notifikasi::where('user_id', Auth::id())->delete();
        return back()->with('success', 'Semua notifikasi dihapus.');
    }
}