<?php

namespace App\Console\Commands;

use App\Models\Notifikasi;
use Illuminate\Console\Command;

class BersihkanNotifikasiLama extends Command
{
    protected $signature   = 'notifikasi:bersihkan {--hari=30 : Hapus notifikasi yang sudah dibaca lebih dari N hari}';
    protected $description = 'Hapus notifikasi lama yang sudah dibaca untuk menjaga ukuran tabel';

    public function handle(): int
    {
        $hari    = (int) $this->option('hari');
        $batas   = now()->subDays($hari);

        // Hapus notifikasi yang sudah dibaca dan lebih lama dari batas
        $dihapus = Notifikasi::where('sudah_dibaca', true)
            ->where('dibaca_pada', '<', $batas)
            ->delete();

        // Hapus juga notifikasi belum dibaca yang sangat lama (90 hari)
        $dihapusTua = Notifikasi::where('sudah_dibaca', false)
            ->where('created_at', '<', now()->subDays(90))
            ->delete();

        $total = $dihapus + $dihapusTua;

        $this->info("✓ {$total} notifikasi berhasil dihapus ({$dihapus} sudah dibaca, {$dihapusTua} belum dibaca tapi kedaluwarsa).");

        return Command::SUCCESS;
    }
}
