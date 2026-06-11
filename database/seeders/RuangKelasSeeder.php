<?php

namespace Database\Seeders;

use App\Models\RuangKelas;
use Illuminate\Database\Seeder;

/**
 * RuangKelasSeeder
 * Data ruang diambil dari file Excel jadwal PSTI Ganjil 2023/2024
 * Jalankan: php artisan db:seed --class=RuangKelasSeeder
 */
class RuangKelasSeeder extends Seeder
{
    public function run(): void
    {
        // Jangan duplikat jika sudah ada
        if (RuangKelas::where('kode_ruang', 'JTE-04')->exists()) {
            $this->command->info('RuangKelasSeeder: data sudah ada, dilewati.');
            return;
        }

        $ruangList = [
            [
                'kode_ruang'  => 'JTE-04',
                'nama_ruang'  => 'Ruang JTE-04',
                'gedung'      => 'JTE',
                'lantai'      => 0,
                'kapasitas'   => 40,
                'jenis'       => 'kelas',
                'fasilitas'   => ['proyektor', 'whiteboard', 'ac'],
                'status'      => 'aktif',
            ],
            [
                'kode_ruang'  => 'JTE-05',
                'nama_ruang'  => 'Ruang JTE-05',
                'gedung'      => 'JTE',
                'lantai'      => 0,
                'kapasitas'   => 40,
                'jenis'       => 'kelas',
                'fasilitas'   => ['proyektor', 'whiteboard', 'ac'],
                'status'      => 'aktif',
            ],
            [
                'kode_ruang'  => 'JTE-06',
                'nama_ruang'  => 'Ruang JTE-06',
                'gedung'      => 'JTE',
                'lantai'      => 0,
                'kapasitas'   => 40,
                'jenis'       => 'kelas',
                'fasilitas'   => ['proyektor', 'whiteboard', 'ac'],
                'status'      => 'aktif',
            ],
            [
                'kode_ruang'  => 'JTE-07',
                'nama_ruang'  => 'Ruang JTE-07',
                'gedung'      => 'JTE',
                'lantai'      => 0,
                'kapasitas'   => 40,
                'jenis'       => 'kelas',
                'fasilitas'   => ['proyektor', 'whiteboard', 'ac'],
                'status'      => 'aktif',
            ],
            [
                'kode_ruang'  => 'JTE-08',
                'nama_ruang'  => 'Ruang JTE-08',
                'gedung'      => 'JTE',
                'lantai'      => 0,
                'kapasitas'   => 40,
                'jenis'       => 'kelas',
                'fasilitas'   => ['proyektor', 'whiteboard', 'ac'],
                'status'      => 'aktif',
            ],
            [
                'kode_ruang'  => 'JTE-22',
                'nama_ruang'  => 'Ruang JTE-22',
                'gedung'      => 'JTE',
                'lantai'      => 2,
                'kapasitas'   => 40,
                'jenis'       => 'kelas',
                'fasilitas'   => ['proyektor', 'whiteboard', 'ac'],
                'status'      => 'aktif',
            ],
            [
                'kode_ruang'  => 'KDK-RPL',
                'nama_ruang'  => 'Ruang KDK-RPL',
                'gedung'      => 'KDK',
                'lantai'      => 1,
                'kapasitas'   => 40,
                'jenis'       => 'laboratorium',
                'fasilitas'   => ['komputer', 'proyektor', 'ac', 'whiteboard'],
                'status'      => 'aktif',
            ],
            [
                'kode_ruang'  => 'KDK-TBD',
                'nama_ruang'  => 'Ruang KDK-TBD',
                'gedung'      => 'KDK',
                'lantai'      => 1,
                'kapasitas'   => 40,
                'jenis'       => 'laboratorium',
                'fasilitas'   => ['komputer', 'proyektor', 'ac', 'whiteboard'],
                'status'      => 'aktif',
            ],
            [
                'kode_ruang'  => 'KDK-MM',
                'nama_ruang'  => 'Ruang KDK-MM',
                'gedung'      => 'KDK',
                'lantai'      => 1,
                'kapasitas'   => 40,
                'jenis'       => 'laboratorium',
                'fasilitas'   => ['komputer', 'proyektor', 'ac', 'whiteboard'],
                'status'      => 'aktif',
            ],
            [
                'kode_ruang'  => 'JTE-09',
                'nama_ruang'  => 'Ruang JTE-09',
                'gedung'      => 'JTE',
                'lantai'      => 0,
                'kapasitas'   => 40,
                'jenis'       => 'kelas',
                'fasilitas'   => ['proyektor', 'whiteboard', 'ac'],
                'status'      => 'aktif',
            ],
            [
                'kode_ruang'  => 'JTE-10',
                'nama_ruang'  => 'Ruang JTE-10',
                'gedung'      => 'JTE',
                'lantai'      => 1,
                'kapasitas'   => 40,
                'jenis'       => 'kelas',
                'fasilitas'   => ['proyektor', 'whiteboard', 'ac'],
                'status'      => 'aktif',
            ],
            [
                'kode_ruang'  => 'JTE-21',
                'nama_ruang'  => 'Ruang JTE-21',
                'gedung'      => 'JTE',
                'lantai'      => 2,
                'kapasitas'   => 40,
                'jenis'       => 'kelas',
                'fasilitas'   => ['proyektor', 'whiteboard', 'ac'],
                'status'      => 'aktif',
            ],
            [
                'kode_ruang'  => 'KDK-TIK',
                'nama_ruang'  => 'Ruang KDK-TIK',
                'gedung'      => 'KDK',
                'lantai'      => 1,
                'kapasitas'   => 40,
                'jenis'       => 'laboratorium',
                'fasilitas'   => ['komputer', 'proyektor', 'ac', 'whiteboard'],
                'status'      => 'aktif',
            ],
            [
                'kode_ruang'  => 'JTE-23',
                'nama_ruang'  => 'Ruang JTE-23',
                'gedung'      => 'JTE',
                'lantai'      => 2,
                'kapasitas'   => 40,
                'jenis'       => 'kelas',
                'fasilitas'   => ['proyektor', 'whiteboard', 'ac'],
                'status'      => 'aktif',
            ],
            [
                'kode_ruang'  => 'JTE-13',
                'nama_ruang'  => 'Ruang JTE-13',
                'gedung'      => 'JTE',
                'lantai'      => 1,
                'kapasitas'   => 40,
                'jenis'       => 'kelas',
                'fasilitas'   => ['proyektor', 'whiteboard', 'ac'],
                'status'      => 'aktif',
            ],
        ];

        foreach ($ruangList as $ruang) {
            RuangKelas::firstOrCreate(
                ['kode_ruang' => $ruang['kode_ruang']],
                $ruang
            );
        }

        $this->command->info('RuangKelasSeeder: ' . count($ruangList) . ' ruang berhasil ditambahkan.');
    }
}
