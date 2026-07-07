<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\RuangKelas;
use App\Models\JadwalTetap;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── 0. Ruang & Dosen sesuai data Excel jadwal PSTI ──
        // Wajib dijalankan sebelum import jadwal, karena import
        // jadwal mencocokkan nama ruang & dosen ke data ini.
        $this->call([
            RuangKelasSeeder::class,
            DosenSeeder::class,
        ]);

        // ── 1. Users ─────────────────────────────────────
        $admin = User::create([
            'name'          => 'Administrator',
            'email'         => 'admin@kampus.ac.id',
            'password'      => Hash::make('password'),
            'role'          => 'admin',
            'nip_nim'       => '199001012020011001',
            'program_studi' => null,
            'no_hp'         => '081234567890',
        ]);

        $dosen = User::create([
            'name'          => 'Dr. Budi Santoso, M.Kom',
            'email'         => 'dosen@kampus.ac.id',
            'password'      => Hash::make('password'),
            'role'          => 'dosen',
            'nip_nim'       => '198505152010011002',
            'program_studi' => 'Teknik Informatika',
            'no_hp'         => '081298765432',
        ]);

        $dosen2 = User::create([
            'name'          => 'Siti Rahayu, M.T',
            'email'         => 'siti@kampus.ac.id',
            'password'      => Hash::make('password'),
            'role'          => 'dosen',
            'nip_nim'       => '199203102015012003',
            'program_studi' => 'Sistem Informasi',
            'no_hp'         => '081311223344',
        ]);

        User::create([
            'name'          => 'Andi Mahasiswa',
            'email'         => 'mahasiswa@kampus.ac.id',
            'password'      => Hash::make('password'),
            'role'          => 'mahasiswa',
            'nip_nim'       => '21011001',
            'program_studi' => 'Teknik Informatika',
            'semester'      => 1,
            'kelas'         => 'A',
            'no_hp'         => '082211223344',
        ]);

        User::create([
            'name'          => 'Rina Mahasiswa',
            'email'         => 'rina@kampus.ac.id',
            'password'      => Hash::make('password'),
            'role'          => 'mahasiswa',
            'nip_nim'       => '22031045',
            'program_studi' => 'Teknik Informatika',
            'semester'      => 5,
            'kelas'         => 'A',
            'no_hp'         => '082255667788',
        ]);

        // ── 2. Ruang Kelas ───────────────────────────────
        $ruangData = [
            ['kode_ruang' => 'R.101', 'nama_ruang' => 'Ruang Kelas 101', 'gedung' => 'Gedung A', 'lantai' => 1, 'kapasitas' => 40, 'jenis' => 'kelas',        'fasilitas' => ['proyektor', 'ac', 'papan_tulis', 'wifi']],
            ['kode_ruang' => 'R.102', 'nama_ruang' => 'Ruang Kelas 102', 'gedung' => 'Gedung A', 'lantai' => 1, 'kapasitas' => 35, 'jenis' => 'kelas',        'fasilitas' => ['proyektor', 'ac', 'papan_tulis']],
            ['kode_ruang' => 'R.201', 'nama_ruang' => 'Ruang Kelas 201', 'gedung' => 'Gedung A', 'lantai' => 2, 'kapasitas' => 50, 'jenis' => 'kelas',        'fasilitas' => ['proyektor', 'ac', 'papan_tulis', 'wifi', 'sound_system']],
            ['kode_ruang' => 'R.202', 'nama_ruang' => 'Ruang Kelas 202', 'gedung' => 'Gedung A', 'lantai' => 2, 'kapasitas' => 40, 'jenis' => 'kelas',        'fasilitas' => ['proyektor', 'ac', 'papan_tulis']],
            ['kode_ruang' => 'LAB-A', 'nama_ruang' => 'Laboratorium Komputer A', 'gedung' => 'Gedung B', 'lantai' => 1, 'kapasitas' => 30, 'jenis' => 'laboratorium', 'fasilitas' => ['komputer', 'proyektor', 'ac', 'wifi']],
            ['kode_ruang' => 'LAB-B', 'nama_ruang' => 'Laboratorium Komputer B', 'gedung' => 'Gedung B', 'lantai' => 1, 'kapasitas' => 25, 'jenis' => 'laboratorium', 'fasilitas' => ['komputer', 'proyektor', 'ac', 'wifi']],
            ['kode_ruang' => 'AULA',  'nama_ruang' => 'Aula Utama',       'gedung' => 'Gedung C', 'lantai' => 1, 'kapasitas' => 200,'jenis' => 'aula',        'fasilitas' => ['proyektor', 'ac', 'sound_system', 'wifi', 'podium']],
            ['kode_ruang' => 'SEM-1', 'nama_ruang' => 'Ruang Seminar',    'gedung' => 'Gedung C', 'lantai' => 2, 'kapasitas' => 80, 'jenis' => 'seminar',     'fasilitas' => ['proyektor', 'ac', 'sound_system', 'wifi', 'papan_tulis']],
        ];

        $ruangIds = [];
        foreach ($ruangData as $data) {
            $r = RuangKelas::create($data);
            $ruangIds[$data['kode_ruang']] = $r->id;
        }

        // ── 3. Jadwal Tetap (contoh) ─────────────────────
        // NB: ruang_kelas_id diambil via kode_ruang (bukan angka hardcode),
        // karena RuangKelasSeeder di atas juga membuat baris ruang lain
        // sehingga ID auto-increment tidak lagi mulai dari 1.
        $jadwalData = [
            ['ruang_kelas_id' => $ruangIds['R.101'], 'dosen_id' => $dosen->id,  'mata_kuliah' => 'Pemrograman Web',         'kode_mk' => 'TI301', 'kelas' => 'A', 'program_studi' => 'Teknik Informatika', 'semester' => 5, 'tahun_akademik' => '2024/2025', 'semester_ganjil_genap' => 'ganjil', 'hari' => 'senin',  'jam_mulai' => '08:00', 'jam_selesai' => '10:30', 'sks' => 3],
            ['ruang_kelas_id' => $ruangIds['R.102'], 'dosen_id' => $dosen2->id, 'mata_kuliah' => 'Basis Data',               'kode_mk' => 'SI201', 'kelas' => 'B', 'program_studi' => 'Sistem Informasi',   'semester' => 3, 'tahun_akademik' => '2024/2025', 'semester_ganjil_genap' => 'ganjil', 'hari' => 'senin',  'jam_mulai' => '10:00', 'jam_selesai' => '12:30', 'sks' => 3],
            ['ruang_kelas_id' => $ruangIds['R.201'], 'dosen_id' => $dosen->id,  'mata_kuliah' => 'Algoritma & Pemrograman',  'kode_mk' => 'TI101', 'kelas' => 'A', 'program_studi' => 'Teknik Informatika', 'semester' => 1, 'tahun_akademik' => '2024/2025', 'semester_ganjil_genap' => 'ganjil', 'hari' => 'selasa', 'jam_mulai' => '07:30', 'jam_selesai' => '09:30', 'sks' => 2],
            ['ruang_kelas_id' => $ruangIds['LAB-A'], 'dosen_id' => $dosen->id,  'mata_kuliah' => 'Pemrograman Web',         'kode_mk' => 'TI301', 'kelas' => 'B', 'program_studi' => 'Teknik Informatika', 'semester' => 5, 'tahun_akademik' => '2024/2025', 'semester_ganjil_genap' => 'ganjil', 'hari' => 'rabu',   'jam_mulai' => '13:00', 'jam_selesai' => '15:30', 'sks' => 3],
            ['ruang_kelas_id' => $ruangIds['R.202'], 'dosen_id' => $dosen2->id, 'mata_kuliah' => 'Sistem Informasi',        'kode_mk' => 'SI301', 'kelas' => 'A', 'program_studi' => 'Sistem Informasi',   'semester' => 5, 'tahun_akademik' => '2024/2025', 'semester_ganjil_genap' => 'ganjil', 'hari' => 'kamis',  'jam_mulai' => '09:00', 'jam_selesai' => '11:30', 'sks' => 3],
        ];

        foreach ($jadwalData as $data) {
            JadwalTetap::create($data);
        }
    }
}