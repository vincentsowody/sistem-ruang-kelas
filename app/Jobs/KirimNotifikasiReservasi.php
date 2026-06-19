<?php

namespace App\Jobs;

use App\Models\Notifikasi;
use App\Models\Reservasi;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class KirimNotifikasiReservasi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah retry jika job gagal
     */
    public int $tries = 3;

    /**
     * Timeout job dalam detik
     */
    public int $timeout = 30;

    public function __construct(
        public readonly Reservasi $reservasi,
        public readonly string    $tipe,       // reservasi_baru | disetujui | ditolak | dibatalkan
        public readonly ?int      $targetUserId = null, // null = kirim ke semua admin
    ) {}

    public function handle(): void
    {
        match($this->tipe) {
            'reservasi_baru'   => $this->kirimKeAdmin(),
            'disetujui'        => $this->kirimKePemohon('disetujui'),
            'ditolak'          => $this->kirimKePemohon('ditolak'),
            'dibatalkan'       => $this->kirimKePemohon('dibatalkan'),
            'rekomendasi_ruang'=> $this->kirimRekomendasiRuang(),
            default            => null,
        };
    }

    private function kirimKeAdmin(): void
    {
        $reservasi = $this->reservasi->load(['pemohon', 'ruangKelas']);

        $judul = 'Reservasi Baru 📋';
        $pesan = "Ada pengajuan reservasi baru dari {$reservasi->pemohon->name}: ".
                 "{$reservasi->keperluan} di {$reservasi->ruangKelas->kode_ruang} ".
                 "pada {$reservasi->tanggal->locale('id')->isoFormat('D MMM Y')}.";

        User::admin()->active()->each(function (User $admin) use ($reservasi, $judul, $pesan) {
            Notifikasi::create([
                'user_id'      => $admin->id,
                'reservasi_id' => $reservasi->id,
                'tipe'         => 'reservasi_baru',
                'judul'        => $judul,
                'pesan'        => $pesan,
            ]);
        });
    }

    private function kirimKePemohon(string $tipe): void
    {
        $reservasi = $this->reservasi->load(['ruangKelas']);

        [$judul, $pesan] = match($tipe) {
            'disetujui' => [
                'Reservasi Disetujui ✅',
                "Reservasi Anda ({$reservasi->kode_reservasi}) untuk {$reservasi->keperluan} ".
                "di {$reservasi->ruangKelas->kode_ruang} pada ".
                "{$reservasi->tanggal->locale('id')->isoFormat('D MMM Y')} telah disetujui.",
            ],
            'ditolak' => [
                'Reservasi Ditolak ❌',
                "Reservasi Anda ({$reservasi->kode_reservasi}) tidak dapat disetujui. ".
                ($reservasi->catatan_admin ? "Alasan: {$reservasi->catatan_admin}" : ''),
            ],
            'dibatalkan' => [
                'Reservasi Dibatalkan 🚫',
                "Reservasi Anda ({$reservasi->kode_reservasi}) untuk {$reservasi->keperluan} ".
                "telah dibatalkan.",
            ],
            default => ['Notifikasi Reservasi', ''],
        };

        Notifikasi::create([
            'user_id'      => $reservasi->pemohon_id,
            'reservasi_id' => $reservasi->id,
            'tipe'         => $tipe,
            'judul'        => $judul,
            'pesan'        => $pesan,
        ]);
    }

    /**
     * Tangani job yang gagal setelah semua retry habis.
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error("KirimNotifikasiReservasi gagal", [
            'reservasi_id' => $this->reservasi->id,
            'tipe'         => $this->tipe,
            'error'        => $exception->getMessage(),
        ]);
    }

    /**
     * Kirim notifikasi rekomendasi ruang terbaik ke pemohon.
     * Dipanggil setelah reservasi berhasil disimpan, jika ada ruang saran dari greedy.
     */
    private function kirimRekomendasiRuang(): void
    {
        $reservasi = $this->reservasi->load(['ruangKelas', 'ruangSaran']);

        if (!$reservasi->ruangSaran) return;

        $pesan = "Reservasi Anda ({$reservasi->kode_reservasi}) berhasil diajukan. " .
                 "Ruang pilihan Anda ({$reservasi->ruangKelas->kode_ruang}) bentrok. " .
                 "Sistem merekomendasikan ruang <strong>{$reservasi->ruangSaran->kode_ruang}</strong> " .
                 "({$reservasi->ruangSaran->nama_ruang}, {$reservasi->ruangSaran->kapasitas} kursi) " .
                 "sebagai alternatif terbaik. Admin akan memverifikasi pilihan ini.";

        Notifikasi::create([
            'user_id'      => $reservasi->pemohon_id,
            'reservasi_id' => $reservasi->id,
            'tipe'         => 'rekomendasi_ruang',
            'judul'        => '💡 Rekomendasi Ruang Alternatif',
            'pesan'        => $pesan,
        ]);
    }
}