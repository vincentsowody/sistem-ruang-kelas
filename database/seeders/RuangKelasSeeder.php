<?php

namespace Database\Seeders;

use App\Models\RuangKelas;
use Illuminate\Database\Seeder;

/**
 * Seed ruang kelas sesuai data Excel jadwal kampus.
 * Ruang JTE = Gedung Jurusan Teknik Elektro
 * Ruang KDK = Kelompok Data Keahlian (Laboratorium)
 */
class RuangKelasSeeder extends Seeder
{
    public function run(): void
    {
        $ruangList = [
            // ── Ruang Kelas Reguler (JTE) ─────────────────────────
            ['kode_ruang' => 'JTE-04', 'nama_ruang' => 'Ruang Kelas JTE 04', 'gedung' => 'JTE', 'lantai' => 1, 'kapasitas' => 40, 'jenis' => 'kelas'],
            ['kode_ruang' => 'JTE-05', 'nama_ruang' => 'Ruang Kelas JTE 05', 'gedung' => 'JTE', 'lantai' => 1, 'kapasitas' => 40, 'jenis' => 'kelas'],
            ['kode_ruang' => 'JTE-06', 'nama_ruang' => 'Ruang Kelas JTE 06', 'gedung' => 'JTE', 'lantai' => 1, 'kapasitas' => 40, 'jenis' => 'kelas'],
            ['kode_ruang' => 'JTE-07', 'nama_ruang' => 'Ruang Kelas JTE 07', 'gedung' => 'JTE', 'lantai' => 1, 'kapasitas' => 40, 'jenis' => 'kelas'],
            ['kode_ruang' => 'JTE-08', 'nama_ruang' => 'Ruang Kelas JTE 08', 'gedung' => 'JTE', 'lantai' => 1, 'kapasitas' => 40, 'jenis' => 'kelas'],
            ['kode_ruang' => 'JTE-09', 'nama_ruang' => 'Ruang Kelas JTE 09', 'gedung' => 'JTE', 'lantai' => 2, 'kapasitas' => 40, 'jenis' => 'kelas'],
            ['kode_ruang' => 'JTE-10', 'nama_ruang' => 'Ruang Kelas JTE 10', 'gedung' => 'JTE', 'lantai' => 2, 'kapasitas' => 40, 'jenis' => 'kelas'],
            ['kode_ruang' => 'JTE-13', 'nama_ruang' => 'Ruang Kelas JTE 13', 'gedung' => 'JTE', 'lantai' => 2, 'kapasitas' => 40, 'jenis' => 'kelas'],
            ['kode_ruang' => 'JTE-21', 'nama_ruang' => 'Ruang Kelas JTE 21', 'gedung' => 'JTE', 'lantai' => 3, 'kapasitas' => 50, 'jenis' => 'kelas'],
            ['kode_ruang' => 'JTE-22', 'nama_ruang' => 'Ruang Kelas JTE 22', 'gedung' => 'JTE', 'lantai' => 3, 'kapasitas' => 50, 'jenis' => 'kelas'],
            ['kode_ruang' => 'JTE-23', 'nama_ruang' => 'Ruang Kelas JTE 23', 'gedung' => 'JTE', 'lantai' => 3, 'kapasitas' => 50, 'jenis' => 'kelas'],

            // ── Laboratorium (KDK) ────────────────────────────────
            ['kode_ruang' => 'KDK-MM',  'nama_ruang' => 'Lab KDK Multimedia',              'gedung' => 'KDK', 'lantai' => 1, 'kapasitas' => 35, 'jenis' => 'laboratorium'],
            ['kode_ruang' => 'KDK-RPL', 'nama_ruang' => 'Lab KDK Rekayasa Perangkat Lunak','gedung' => 'KDK', 'lantai' => 1, 'kapasitas' => 35, 'jenis' => 'laboratorium'],
            ['kode_ruang' => 'KDK-TBD', 'nama_ruang' => 'Lab KDK Teknologi Basis Data',    'gedung' => 'KDK', 'lantai' => 1, 'kapasitas' => 35, 'jenis' => 'laboratorium'],
            ['kode_ruang' => 'KDK-TIK', 'nama_ruang' => 'Lab KDK Teknik Informatika',      'gedung' => 'KDK', 'lantai' => 1, 'kapasitas' => 35, 'jenis' => 'laboratorium'],
        ];

        foreach ($ruangList as $data) {
            RuangKelas::updateOrCreate(
                ['kode_ruang' => $data['kode_ruang']],
                array_merge($data, [
                    'fasilitas' => ['proyektor', 'whiteboard', 'ac'],
                    'status'    => 'aktif',
                ])
            );
        }

        $this->command->info('✅ ' . count($ruangList) . ' ruang kelas berhasil di-seed.');
    }
}