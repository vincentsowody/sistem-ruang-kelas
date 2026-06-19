<?php

namespace Tests\Unit;

use App\Models\JadwalTetap;
use App\Models\Reservasi;
use App\Models\RuangKelas;
use App\Models\User;
use App\Services\GreedyScheduler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit Test untuk GreedyScheduler
 *
 * Menguji dua algoritma utama:
 * 1. cekKonflik()      — deteksi bentrok jadwal/reservasi
 * 2. cariRuangTerbaik() — Greedy Best-Fit mencari ruang terkecil yang muat
 */
class GreedySchedulerTest extends TestCase
{
    use RefreshDatabase;

    private GreedyScheduler $greedy;
    private RuangKelas $ruangKecil;   // kapasitas 30
    private RuangKelas $ruangSedang;  // kapasitas 60
    private RuangKelas $ruangBesar;   // kapasitas 100
    private User $dosen;

    protected function setUp(): void
    {
        parent::setUp();

        $this->greedy = new GreedyScheduler();

        // Buat data ruang dengan kapasitas berbeda
        $this->ruangKecil = RuangKelas::factory()->create([
            'kode_ruang' => 'R.101',
            'kapasitas'  => 30,
            'status'     => 'aktif',
        ]);

        $this->ruangSedang = RuangKelas::factory()->create([
            'kode_ruang' => 'R.201',
            'kapasitas'  => 60,
            'status'     => 'aktif',
        ]);

        $this->ruangBesar = RuangKelas::factory()->create([
            'kode_ruang' => 'R.301',
            'kapasitas'  => 100,
            'status'     => 'aktif',
        ]);

        $this->dosen = User::factory()->create(['role' => 'dosen']);
    }

    // =========================================================
    // GROUP 1: cekKonflik()
    // =========================================================

    /** @test */
    public function tidak_ada_konflik_jika_ruang_kosong()
    {
        $hasil = $this->greedy->cekKonflik(
            $this->ruangKecil->id,
            '2025-09-01', // Senin
            '08:00',
            '10:00'
        );

        $this->assertFalse($hasil['konflik']);
        $this->assertEmpty($hasil['detail']);
    }

    /** @test */
    public function ruang_tidak_ditemukan_dianggap_konflik()
    {
        $hasil = $this->greedy->cekKonflik(
            99999, // ID tidak ada
            '2025-09-01',
            '08:00',
            '10:00'
        );

        $this->assertTrue($hasil['konflik']);
        $this->assertStringContainsString('tidak ditemukan', $hasil['detail']);
    }

    /** @test */
    public function konflik_dengan_jadwal_tetap_yang_overlap()
    {
        // Buat jadwal tetap Senin 08:00–10:00
        JadwalTetap::factory()->create([
            'ruang_kelas_id' => $this->ruangKecil->id,
            'dosen_id'       => $this->dosen->id,
            'hari'           => 'senin',
            'jam_mulai'      => '08:00',
            'jam_selesai'    => '10:00',
            'status'         => 'aktif',
        ]);

        // Cek reservasi Senin 09:00–11:00 → harus konflik (overlap di tengah)
        $hasil = $this->greedy->cekKonflik(
            $this->ruangKecil->id,
            '2025-09-01', // Senin
            '09:00',
            '11:00'
        );

        $this->assertTrue($hasil['konflik']);
    }

    /** @test */
    public function tidak_konflik_jika_jadwal_tepat_bersebelahan()
    {
        // Jadwal tetap Senin 08:00–10:00
        JadwalTetap::factory()->create([
            'ruang_kelas_id' => $this->ruangKecil->id,
            'dosen_id'       => $this->dosen->id,
            'hari'           => 'senin',
            'jam_mulai'      => '08:00',
            'jam_selesai'    => '10:00',
            'status'         => 'aktif',
        ]);

        // Reservasi 10:00–12:00 → TIDAK boleh konflik (bersebelahan persis)
        $hasil = $this->greedy->cekKonflik(
            $this->ruangKecil->id,
            '2025-09-01', // Senin
            '10:00',
            '12:00'
        );

        $this->assertFalse($hasil['konflik'],
            'Slot bersebelahan (selesai = mulai berikutnya) seharusnya tidak konflik.'
        );
    }

    /** @test */
    public function konflik_dengan_reservasi_yang_sudah_disetujui()
    {
        $pemohon = User::factory()->create(['role' => 'mahasiswa']);

        // Reservasi sudah disetujui pada tanggal & jam tertentu
        Reservasi::factory()->create([
            'ruang_kelas_id' => $this->ruangKecil->id,
            'pemohon_id'     => $pemohon->id,
            'tanggal'        => '2025-09-05',
            'jam_mulai'      => '13:00',
            'jam_selesai'    => '15:00',
            'status'         => 'disetujui',
        ]);

        // Reservasi baru di jam yang sama → harus konflik
        $hasil = $this->greedy->cekKonflik(
            $this->ruangKecil->id,
            '2025-09-05',
            '14:00',
            '16:00'
        );

        $this->assertTrue($hasil['konflik']);
    }

    /** @test */
    public function tidak_konflik_dengan_reservasi_yang_masih_menunggu()
    {
        $pemohon = User::factory()->create(['role' => 'mahasiswa']);

        // Reservasi MENUNGGU (belum disetujui) di jam yang sama
        Reservasi::factory()->create([
            'ruang_kelas_id' => $this->ruangKecil->id,
            'pemohon_id'     => $pemohon->id,
            'tanggal'        => '2025-09-05',
            'jam_mulai'      => '13:00',
            'jam_selesai'    => '15:00',
            'status'         => 'menunggu', // belum disetujui
        ]);

        // Tidak boleh dianggap konflik karena reservasi belum disetujui
        $hasil = $this->greedy->cekKonflik(
            $this->ruangKecil->id,
            '2025-09-05',
            '14:00',
            '16:00'
        );

        $this->assertFalse($hasil['konflik'],
            'Reservasi berstatus menunggu tidak boleh dianggap mengunci ruang.'
        );
    }

    /** @test */
    public function konflik_tidak_terhitung_jika_dikecualikan_dari_pengecekan()
    {
        $pemohon = User::factory()->create(['role' => 'mahasiswa']);

        $reservasi = Reservasi::factory()->create([
            'ruang_kelas_id' => $this->ruangKecil->id,
            'pemohon_id'     => $pemohon->id,
            'tanggal'        => '2025-09-05',
            'jam_mulai'      => '13:00',
            'jam_selesai'    => '15:00',
            'status'         => 'disetujui',
        ]);

        // Cek dengan mengecualikan reservasi itu sendiri (dipakai saat edit)
        $hasil = $this->greedy->cekKonflik(
            $this->ruangKecil->id,
            '2025-09-05',
            '13:00',
            '15:00',
            $reservasi->id // kecuali reservasi ini
        );

        $this->assertFalse($hasil['konflik'],
            'Reservasi yang dikecualikan tidak boleh menyebabkan konflik dengan dirinya sendiri.'
        );
    }

    // =========================================================
    // GROUP 2: cariRuangTerbaik() — Greedy Best-Fit
    // =========================================================

    /** @test */
    public function best_fit_mengembalikan_ruang_terkecil_yang_muat()
    {
        // Semua ruang kosong, cari untuk 25 peserta
        // Ruang kecil (30) > 25 → harus dipilih karena paling pas (best-fit)
        $ruang = $this->greedy->cariRuangTerbaik(
            '2025-09-05',
            '08:00',
            '10:00',
            25 // jumlah peserta
        );

        $this->assertNotNull($ruang);
        $this->assertEquals($this->ruangKecil->id, $ruang->id,
            'Best-fit harus memilih ruang terkecil yang kapasitasnya cukup.'
        );
    }

    /** @test */
    public function best_fit_skip_ruang_yang_kapasitasnya_kurang()
    {
        // Cari untuk 50 peserta → ruang kecil (30) tidak cukup, harus pilih sedang (60)
        $ruang = $this->greedy->cariRuangTerbaik(
            '2025-09-05',
            '08:00',
            '10:00',
            50
        );

        $this->assertNotNull($ruang);
        $this->assertEquals($this->ruangSedang->id, $ruang->id,
            'Ruang dengan kapasitas kurang dari jumlah peserta tidak boleh dipilih.'
        );
    }

    /** @test */
    public function best_fit_skip_ruang_yang_sedang_terpakai()
    {
        $pemohon = User::factory()->create(['role' => 'mahasiswa']);

        // Ruang kecil sudah ada reservasi disetujui jam 08:00–10:00
        Reservasi::factory()->create([
            'ruang_kelas_id' => $this->ruangKecil->id,
            'pemohon_id'     => $pemohon->id,
            'tanggal'        => '2025-09-05',
            'jam_mulai'      => '08:00',
            'jam_selesai'    => '10:00',
            'status'         => 'disetujui',
        ]);

        // Cari untuk 20 peserta di jam yang sama
        // Ruang kecil bentrok → harus lompat ke ruang sedang
        $ruang = $this->greedy->cariRuangTerbaik(
            '2025-09-05',
            '08:00',
            '10:00',
            20
        );

        $this->assertNotNull($ruang);
        $this->assertEquals($this->ruangSedang->id, $ruang->id,
            'Ruang yang sudah terpakai harus dilewati meskipun kapasitasnya sesuai.'
        );
    }

    /** @test */
    public function best_fit_mengembalikan_null_jika_semua_ruang_penuh()
    {
        $pemohon = User::factory()->create(['role' => 'mahasiswa']);

        // Semua ruang sudah terisi jam 08:00–10:00
        foreach ([$this->ruangKecil, $this->ruangSedang, $this->ruangBesar] as $ruang) {
            Reservasi::factory()->create([
                'ruang_kelas_id' => $ruang->id,
                'pemohon_id'     => $pemohon->id,
                'tanggal'        => '2025-09-05',
                'jam_mulai'      => '08:00',
                'jam_selesai'    => '10:00',
                'status'         => 'disetujui',
            ]);
        }

        $ruang = $this->greedy->cariRuangTerbaik(
            '2025-09-05',
            '08:00',
            '10:00',
            20
        );

        $this->assertNull($ruang,
            'Harus mengembalikan null jika tidak ada ruang yang tersedia.'
        );
    }

    /** @test */
    public function best_fit_mengecualikan_ruang_yang_diminta()
    {
        // Semua ruang kosong, tapi kita minta kecualikan ruangKecil
        $ruang = $this->greedy->cariRuangTerbaik(
            '2025-09-05',
            '08:00',
            '10:00',
            20,
            [],
            $this->ruangKecil->id // dikecualikan
        );

        $this->assertNotNull($ruang);
        $this->assertNotEquals($this->ruangKecil->id, $ruang->id,
            'Ruang yang dikecualikan tidak boleh dikembalikan sebagai hasil.'
        );
        $this->assertEquals($this->ruangSedang->id, $ruang->id);
    }

    /** @test */
    public function best_fit_tidak_memilih_ruang_nonaktif()
    {
        // Nonaktifkan semua ruang kecuali ruangBesar
        $this->ruangKecil->update(['status'  => 'nonaktif']);
        $this->ruangSedang->update(['status' => 'nonaktif']);

        $ruang = $this->greedy->cariRuangTerbaik(
            '2025-09-05',
            '08:00',
            '10:00',
            20
        );

        $this->assertNotNull($ruang);
        $this->assertEquals($this->ruangBesar->id, $ruang->id,
            'Ruang nonaktif tidak boleh masuk kandidat.'
        );
    }

    // =========================================================
    // GROUP 3: jadwalkanBatch() — Greedy First-Fit batch
    // =========================================================

    /** @test */
    public function jadwalkan_batch_berhasil_mengalokasikan_jadwal_ke_ruang_kosong()
    {
        $input = [
            [
                'mata_kuliah'      => 'Algoritma & Pemrograman',
                'hari'             => 'senin',
                'jam_mulai'        => '08:00',
                'jam_selesai'      => '10:00',
                'jumlah_mahasiswa' => 25,
            ],
        ];

        $hasil = $this->greedy->jadwalkanBatch($input);

        $this->assertCount(1, $hasil['berhasil'],
            'Jadwal harus berhasil dialokasikan ke ruang yang kosong.'
        );
        $this->assertCount(0, $hasil['gagal']);
        $this->assertNotNull($hasil['berhasil'][0]['ruang_dialokasikan']);
    }

    /** @test */
    public function jadwalkan_batch_memilih_ruang_terkecil_yang_tersedia()
    {
        $input = [
            [
                'mata_kuliah'      => 'Basis Data',
                'hari'             => 'selasa',
                'jam_mulai'        => '10:00',
                'jam_selesai'      => '12:00',
                'jumlah_mahasiswa' => 20,
            ],
        ];

        $hasil = $this->greedy->jadwalkanBatch($input);

        $this->assertCount(1, $hasil['berhasil']);
        // Karena first-fit dari kapasitas terkecil, harusnya dapat ruangKecil (30)
        $this->assertEquals(
            $this->ruangKecil->id,
            $hasil['berhasil'][0]['ruang_dialokasikan']->id
        );
    }

    /** @test */
    public function jadwalkan_batch_memasukkan_ke_gagal_jika_semua_ruang_bentrok()
    {
        // Buat jadwal tetap di semua ruang untuk hari Rabu 08:00-10:00
        foreach ([$this->ruangKecil, $this->ruangSedang, $this->ruangBesar] as $ruang) {
            JadwalTetap::factory()->create([
                'ruang_kelas_id' => $ruang->id,
                'dosen_id'       => $this->dosen->id,
                'hari'           => 'rabu',
                'jam_mulai'      => '08:00',
                'jam_selesai'    => '10:00',
                'status'         => 'aktif',
            ]);
        }

        $input = [
            [
                'mata_kuliah'      => 'Pemrograman Web',
                'hari'             => 'rabu',
                'jam_mulai'        => '08:00',
                'jam_selesai'      => '10:00',
                'jumlah_mahasiswa' => 20,
            ],
        ];

        $hasil = $this->greedy->jadwalkanBatch($input);

        $this->assertCount(0, $hasil['berhasil']);
        $this->assertCount(1, $hasil['gagal'],
            'Jadwal harus masuk ke daftar gagal jika semua ruang bentrok.'
        );
    }

    /** @test */
    public function jadwalkan_batch_dengan_input_kosong_mengembalikan_array_kosong()
    {
        $hasil = $this->greedy->jadwalkanBatch([]);

        $this->assertEmpty($hasil['berhasil']);
        $this->assertEmpty($hasil['gagal']);
    }
}
