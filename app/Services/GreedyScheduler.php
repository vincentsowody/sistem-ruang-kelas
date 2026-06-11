<?php

namespace App\Services;

use App\Models\RuangKelas;
use App\Models\JadwalTetap;
use App\Models\Reservasi;

class GreedyScheduler
{
    /**
     * Cek apakah ruang tertentu konflik pada tanggal & jam tertentu.
     * Mengembalikan ['konflik' => bool, 'detail' => string].
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

        // Cek bentrok dengan jadwal tetap
        $hariMap = [1=>'senin',2=>'selasa',3=>'rabu',4=>'kamis',5=>'jumat',6=>'sabtu',7=>'minggu'];
        $carbonDate = \Carbon\Carbon::parse($tanggal);
        $hari = $hariMap[$carbonDate->dayOfWeekIso] ?? strtolower($carbonDate->locale('id')->dayName);

        $jadwalBentrok = JadwalTetap::where('ruang_kelas_id', $ruangId)
            ->where('hari', $hari)
            ->where('status', 'aktif')
            ->where(function ($q) use ($jamMulai, $jamSelesai) {
                $q->where('jam_mulai', '<', $jamSelesai)
                  ->where('jam_selesai', '>', $jamMulai);
            })
            ->first();

        if ($jadwalBentrok) {
            return [
                'konflik' => true,
                'detail'  => "Bentrok dengan jadwal tetap {$jadwalBentrok->mata_kuliah} "
                            ."({$jadwalBentrok->jam_mulai}–{$jadwalBentrok->jam_selesai})",
            ];
        }

        // Cek bentrok dengan reservasi yang sudah disetujui
        $query = Reservasi::where('ruang_kelas_id', $ruangId)
            ->where('tanggal', $tanggal)
            ->where('status', 'disetujui')
            ->where(function ($q) use ($jamMulai, $jamSelesai) {
                $q->where('jam_mulai', '<', $jamSelesai)
                  ->where('jam_selesai', '>', $jamMulai);
            });

        if ($kecualiReservasiId) {
            $query->where('id', '!=', $kecualiReservasiId);
        }

        $reservasiBentrok = $query->first();

        if ($reservasiBentrok) {
            return [
                'konflik' => true,
                'detail'  => "Bentrok dengan reservasi {$reservasiBentrok->kode_reservasi} "
                            ."({$reservasiBentrok->jam_mulai}–{$reservasiBentrok->jam_selesai})",
            ];
        }

        return ['konflik' => false, 'detail' => ''];
    }

    /**
     * Algoritma Greedy Best-Fit:
     * Cari ruang terkecil yang muat (kapasitas >= jumlahPeserta) dan tersedia.
     * Mengembalikan RuangKelas atau null jika tidak ada.
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
            ->orderBy('kapasitas', 'asc'); // best-fit: pilih yang paling pas

        if ($kecualiRuangId) {
            $query->where('id', '!=', $kecualiRuangId);
        }

        $ruangList = $query->get();

        foreach ($ruangList as $ruang) {
            // Filter fasilitas jika ada kebutuhan spesifik
            if (!empty($fasilitasDibutuhkan)) {
                $fasilitasRuang = $ruang->fasilitas ?? [];
                $terpenuhi = empty(array_diff($fasilitasDibutuhkan, $fasilitasRuang));
                if (!$terpenuhi) continue;
            }

            // Cek ketersediaan
            if ($ruang->tersediaPada($tanggal, $jamMulai, $jamSelesai)) {
                return $ruang;
            }
        }

        return null;
    }

    /**
     * Jadwalkan batch jadwal ke ruang-ruang yang tersedia (Greedy First-Fit).
     */
    public function jadwalkanBatch($jadwalInput = []): array
    {
        $hasil = [
            'berhasil' => [],
            'gagal'    => [],
        ];

        if (!$jadwalInput || !is_array($jadwalInput)) {
            return $hasil;
        }

        $ruangList = RuangKelas::where('status', 'aktif')
            ->orderBy('kapasitas', 'asc')
            ->get();

        foreach ($jadwalInput as $item) {
            $item = array_merge([
                'mata_kuliah'      => null,
                'kelas'            => null,
                'program_studi'    => null,
                'semester'         => null,
                'dosen_id'         => null,
                'hari'             => null,
                'jam_mulai'        => null,
                'jam_selesai'      => null,
                'sks'              => 2,
                'jumlah_mahasiswa' => 30,
            ], $item);

            $ruangDipilih = null;

            foreach ($ruangList as $ruang) {
                $bentrok = JadwalTetap::where('ruang_kelas_id', $ruang->id)
                    ->where('hari', $item['hari'])
                    ->where(function ($q) use ($item) {
                        $q->where('jam_mulai', '<', $item['jam_selesai'])
                          ->where('jam_selesai', '>', $item['jam_mulai']);
                    })
                    ->exists();

                if (!$bentrok) {
                    $ruangDipilih = $ruang;
                    break;
                }
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
}
