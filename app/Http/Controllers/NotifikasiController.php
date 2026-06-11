<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    public function index()
    {
        $notifikasi = Notifikasi::with('reservasi')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        // Tandai semua sebagai sudah dibaca
        Notifikasi::where('user_id', Auth::id())
            ->where('sudah_dibaca', false)
            ->update(['sudah_dibaca' => true, 'dibaca_pada' => now()]);

        return view('notifikasi.index', compact('notifikasi'));
    }

    // API: jumlah notifikasi belum dibaca (untuk badge navbar)
    public function apiJumlah()
    {
        return response()->json([
            'jumlah' => Auth::user()->notifikasiBelumDibaca()->count(),
        ]);
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
