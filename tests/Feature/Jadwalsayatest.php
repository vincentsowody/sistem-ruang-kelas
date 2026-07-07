<?php

namespace Tests\Feature;

use App\Models\JadwalTetap;
use App\Models\RuangKelas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test fitur "Jadwal Saya" — memastikan mahasiswa hanya melihat jadwal
 * yang cocok dengan program_studi + semester + kelas miliknya sendiri,
 * sehingga mahasiswa semester berbeda melihat jadwal yang berbeda pula.
 */
class JadwalSayaTest extends TestCase
{
    use RefreshDatabase;

    protected RuangKelas $ruang;
    protected User $dosen;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ruang = RuangKelas::factory()->create(['status' => 'aktif']);
        $this->dosen = User::factory()->create(['role' => 'dosen']);

        JadwalTetap::create([
            'ruang_kelas_id'        => $this->ruang->id,
            'dosen_id'              => $this->dosen->id,
            'mata_kuliah'           => 'Algoritma & Pemrograman',
            'kode_mk'               => 'TI101',
            'kelas'                 => 'A',
            'program_studi'         => 'Teknik Informatika',
            'semester'              => 1,
            'tahun_akademik'        => '2025/2026',
            'semester_ganjil_genap' => 'ganjil',
            'hari'                  => 'senin',
            'jam_mulai'             => '08:00',
            'jam_selesai'           => '10:00',
            'sks'                   => 2,
            'status'                => 'aktif',
        ]);

        JadwalTetap::create([
            'ruang_kelas_id'        => $this->ruang->id,
            'dosen_id'              => $this->dosen->id,
            'mata_kuliah'           => 'Struktur Data',
            'kode_mk'               => 'TI301',
            'kelas'                 => 'A',
            'program_studi'         => 'Teknik Informatika',
            'semester'              => 3,
            'tahun_akademik'        => '2025/2026',
            'semester_ganjil_genap' => 'ganjil',
            'hari'                  => 'selasa',
            'jam_mulai'             => '10:00',
            'jam_selesai'           => '12:00',
            'sks'                   => 2,
            'status'                => 'aktif',
        ]);
    }

    /** @test */
    public function mahasiswa_semester_1_hanya_melihat_jadwal_semester_1()
    {
        $mhs = User::factory()->create([
            'role'          => 'mahasiswa',
            'program_studi' => 'Teknik Informatika',
            'semester'      => 1,
            'kelas'         => 'A',
        ]);

        $response = $this->actingAs($mhs)->get(route('mahasiswa.jadwal-saya'));

        $response->assertOk();
        $response->assertSee('Algoritma & Pemrograman');
        $response->assertDontSee('Struktur Data');
    }

    /** @test */
    public function mahasiswa_semester_3_hanya_melihat_jadwal_semester_3()
    {
        $mhs = User::factory()->create([
            'role'          => 'mahasiswa',
            'program_studi' => 'Teknik Informatika',
            'semester'      => 3,
            'kelas'         => 'A',
        ]);

        $response = $this->actingAs($mhs)->get(route('mahasiswa.jadwal-saya'));

        $response->assertOk();
        $response->assertSee('Struktur Data');
        $response->assertDontSee('Algoritma & Pemrograman');
    }

    /** @test */
    public function mahasiswa_kelas_berbeda_tidak_ikut_melihat_jadwal_kelas_lain()
    {
        $mhs = User::factory()->create([
            'role'          => 'mahasiswa',
            'program_studi' => 'Teknik Informatika',
            'semester'      => 1,
            'kelas'         => 'B', // beda kelas dengan jadwal yang di-seed (kelas A)
        ]);

        $response = $this->actingAs($mhs)->get(route('mahasiswa.jadwal-saya'));

        $response->assertOk();
        $response->assertDontSee('Algoritma & Pemrograman');
    }

    /** @test */
    public function mahasiswa_tanpa_data_semester_kelas_mendapat_pesan_lengkapi_data()
    {
        $mhs = User::factory()->create([
            'role'          => 'mahasiswa',
            'program_studi' => null,
            'semester'      => null,
            'kelas'         => null,
        ]);

        $response = $this->actingAs($mhs)->get(route('mahasiswa.jadwal-saya'));

        $response->assertOk();
        $response->assertSee('belum diisi');
    }

    /** @test */
    public function dosen_tidak_bisa_akses_halaman_jadwal_saya_mahasiswa()
    {
        $response = $this->actingAs($this->dosen)->get(route('mahasiswa.jadwal-saya'));

        $response->assertForbidden();
    }
}