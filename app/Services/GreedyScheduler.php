<?php

namespace App\Services;

use App\Models\JadwalTetap;
use App\Models\Reservasi;
use App\Models\RuangKelas;
use Carbon\Carbon;

class GreedyScheduler
{
    /**
     * Cek konflik ruang pada tanggal & jam tertentu.
     */
    public function cekKonflik(
        int $ruangId,
        string $tanggal,
        string $jamMulai,
        string $jamSelesai,
        ?int $kecualiReservasiId = null
    ): array {
        $ruang = RuangKelas::find($ruangId);
        if (!$ruang) {
            return ['konflik' => true, 'detail' => 'Ruang tidak ditemukan.'];
        }

        $hari = $this->tanggalKeHari($tanggal);

        // Cek jadwal tetap
        $jadwalBentrok = JadwalTetap::where('ruang_kelas_id', $ruangId)
            ->where('hari', $hari)
            ->where('status', 'aktif')
            ->where(fn($q) => $q->where('jam_mulai', '<', $jamSelesai)->where('jam_selesai', '>', $jamMulai))
            ->first();

        if ($jadwalBentrok) {
            return [
                'konflik' => true,
                'detail'  => "Bentrok dengan jadwal tetap {$jadwalBentrok->mata_kuliah} ({$jadwalBentrok->jam_mulai}–{$jadwalBentrok->jam_selesai})",
            ];
        }

        // Cek reservasi yang sudah disetujui
        $query = Reservasi::where('ruang_kelas_id', $ruangId)
            ->where('tanggal', $tanggal)
            ->where('status', 'disetujui')
            ->where(fn($q) => $q->where('jam_mulai', '<', $jamSelesai)->where('jam_selesai', '>', $jamMulai));

        if ($kecualiReservasiId) {
            $query->where('id', '!=', $kecualiReservasiId);
        }

        $rsvBentrok = $query->first();
        if ($rsvBentrok) {
            return [
                'konflik' => true,
                'detail'  => "Bentrok dengan reservasi {$rsvBentrok->kode_reservasi} ({$rsvBentrok->jam_mulai}–{$rsvBentrok->jam_selesai})",
            ];
        }

        return ['konflik' => false, 'detail' => ''];
    }

    /**
     * Algoritma Greedy Best-Fit:
     * Pilih ruang terkecil yang muat dan tersedia.
     */
    public function cariRuangTerbaik(
        string $tanggal,
        string $jamMulai,
        string $jamSelesai,
        int $jumlahPeserta,
        array $fasilitasDibutuhkan = [],
        ?int $kecualiRuangId = null
    ): ?RuangKelas {
        $query = RuangKelas::aktif()
            ->where('kapasitas', '>=', $jumlahPeserta)
            ->orderBy('kapasitas', 'asc');

        if ($kecualiRuangId) {
            $query->where('id', '!=', $kecualiRuangId);
        }

        foreach ($query->get() as $ruang) {
            if (!empty($fasilitasDibutuhkan)) {
                $fasilitasRuang = $ruang->fasilitas ?? [];
                if (!empty(array_diff($fasilitasDibutuhkan, $fasilitasRuang))) continue;
            }

            if ($ruang->tersediaPada($tanggal, $jamMulai, $jamSelesai)) {
                return $ruang;
            }
        }

        return null;
    }

    /**
     * Jadwalkan batch jadwal ke ruang yang tersedia (Greedy First-Fit).
     *
     * BUG FIX 11: jadwalkanBatch() mengalokasikan ruang tapi tidak memperhitungkan
     * kapasitas ruang vs jumlah mahasiswa — ruang 10 kursi bisa dialokasikan
     * untuk 80 mahasiswa. Juga tidak cek konflik antar item dalam batch yang sama
     * (jika 2 item punya hari & jam sama, item ke-2 akan dapat ruang yang sama
     * karena belum ada di DB saat dicek).
     * FIX: cek kapasitas + track alokasi sesi ini di memory.
     */
    public function jadwalkanBatch(array $jadwalInput = []): array
    {
        $hasil = ['berhasil' => [], 'gagal' => []];

        if (empty($jadwalInput)) return $hasil;

        $ruangList = RuangKelas::aktif()
            ->orderBy('kapasitas', 'asc')
            ->get();

        // Track ruang yang sudah dialokasikan dalam batch ini
        // format: "ruang_id|hari|jam_mulai|jam_selesai" => true
        $alokasiBatch = [];

        foreach ($jadwalInput as $item) {
            $item = array_merge([
                'mata_kuliah'      => '',
                'kelas'            => 'A',
                'program_studi'    => 'Teknik Informatika',
                'semester'         => 1,
                'dosen_id'         => null,
                'hari'             => null,
                'jam_mulai'        => null,
                'jam_selesai'      => null,
                'sks'              => 2,
                'jumlah_mahasiswa' => 30,
            ], $item);

            $ruangDipilih = null;

            foreach ($ruangList as $ruang) {
                // FIX: cek kapasitas
                if ($ruang->kapasitas < (int)($item['jumlah_mahasiswa'] ?? 1)) continue;

                // Cek konflik di DB (jadwal tetap yang sudah ada)
                $bentrokDB = JadwalTetap::where('ruang_kelas_id', $ruang->id)
                    ->where('hari', $item['hari'])
                    ->where(fn($q) => $q
                        ->where('jam_mulai', '<', $item['jam_selesai'])
                        ->where('jam_selesai', '>', $item['jam_mulai'])
                    )->exists();

                if ($bentrokDB) continue;

                // FIX: cek konflik dengan alokasi batch saat ini (belum di-commit ke DB)
                $keyBatch = "{$ruang->id}|{$item['hari']}|{$item['jam_mulai']}|{$item['jam_selesai']}";
                if (isset($alokasiBatch[$keyBatch])) continue;

                // Ruang cocok — tandai dan pilih
                $alokasiBatch[$keyBatch] = true;
                $ruangDipilih = $ruang;
                break;
            }

            if ($ruangDipilih) {
                $item['ruang_dialokasikan'] = $ruangDipilih;
                $hasil['berhasil'][] = $item;
            } else {
                $hasil['gagal'][] = $item;
            }
        }

        return $hasil;
    }

    // ── Helper ────────────────────────────────────────────

    private function tanggalKeHari(string $tanggal): string
    {
        $map = [1=>'senin',2=>'selasa',3=>'rabu',4=>'kamis',5=>'jumat',6=>'sabtu',7=>'minggu'];
        return $map[Carbon::parse($tanggal)->dayOfWeekIso] ?? 'senin';
    }
}   