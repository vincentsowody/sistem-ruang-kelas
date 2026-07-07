<?php

namespace Tests\Feature;

use App\Models\JadwalTetap;
use App\Models\Reservasi;
use App\Models\RuangKelas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature Test: Alur pengajuan reservasi oleh user.
 *
 * Mencakup:
 * - Pengajuan berhasil (ruang tersedia)
 * - Pengajuan saat ruang bentrok → greedy saran alternatif
 * - Validasi input
 * - Otorisasi (tamu tidak bisa akses)
 */
class ReservasiStoreTest extends TestCase
{
    use RefreshDatabase;

    private User $dosen;
    private RuangKelas $ruang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dosen = User::factory()->create(['role' => 'dosen', 'is_active' => true]);

        $this->ruang = RuangKelas::factory()->create([
            'kode_ruang' => 'R.101',
            'kapasitas'  => 40,
            'status'     => 'aktif',
        ]);
    }

    /** @test */
    public function tamu_tidak_bisa_mengajukan_reservasi()
    {
        $response = $this->post(route('reservasi.store'), []);

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function pengajuan_berhasil_jika_ruang_tersedia()
    {
        $payload = [
            'ruang_kelas_id' => $this->ruang->id,
            'tanggal'        => now()->addDays(3)->format('Y-m-d'),
            'jam_mulai'      => '09:00',
            'jam_selesai'    => '11:00',
            'keperluan'      => 'Rapat Himpunan',
            'jenis_kegiatan' => 'kegiatan_mahasiswa',
            'jumlah_peserta' => 30,
        ];

        $response = $this->actingAs($this->dosen)
            ->post(route('reservasi.store'), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reservasi', [
            'ruang_kelas_id' => $this->ruang->id,
            'pemohon_id'     => $this->dosen->id,
            'status'         => 'menunggu',
            'keperluan'      => 'Rapat Himpunan',
        ]);
    }

    /** @test */
    public function pengajuan_dengan_ruang_bentrok_menyimpan_dengan_status_menunggu_dan_ada_saran()
    {
        $ruangAlternatif = RuangKelas::factory()->create([
            'kode_ruang' => 'R.102',
            'kapasitas'  => 50,
            'status'     => 'aktif',
        ]);

        $pemohonLain = User::factory()->create(['role' => 'mahasiswa']);

        // Kunci ruang R.101 dengan reservasi disetujui
        Reservasi::factory()->create([
            'ruang_kelas_id' => $this->ruang->id,
            'pemohon_id'     => $pemohonLain->id,
            'tanggal'        => now()->addDays(3)->format('Y-m-d'),
            'jam_mulai'      => '09:00',
            'jam_selesai'    => '11:00',
            'status'         => 'disetujui',
        ]);

        $payload = [
            'ruang_kelas_id' => $this->ruang->id,
            'tanggal'        => now()->addDays(3)->format('Y-m-d'),
            'jam_mulai'      => '09:00',
            'jam_selesai'    => '11:00',
            'keperluan'      => 'Seminar',
            'jenis_kegiatan' => 'seminar',
            'jumlah_peserta' => 30,
        ];

        $response = $this->actingAs($this->dosen)
            ->post(route('reservasi.store'), $payload);

        // Tetap disimpan, bukan ditolak
        $this->assertDatabaseHas('reservasi', [
            'ruang_kelas_id' => $this->ruang->id,
            'pemohon_id'     => $this->dosen->id,
            'status'         => 'menunggu',
        ]);

        // Ada saran ruang alternatif (ruangAlternatif)
        $reservasi = Reservasi::where('pemohon_id', $this->dosen->id)->first();
        $this->assertNotNull($reservasi->ruang_saran_id,
            'Saat ruang bentrok, sistem harus menyarankan ruang alternatif.'
        );
        $this->assertEquals($ruangAlternatif->id, $reservasi->ruang_saran_id);

        // Flash warning (bukan success) karena ada konflik
        $response->assertSessionHas('warning');
    }

    /** @test */
    public function pengajuan_gagal_jika_jam_selesai_sebelum_jam_mulai()
    {
        $payload = [
            'ruang_kelas_id' => $this->ruang->id,
            'tanggal'        => now()->addDays(1)->format('Y-m-d'),
            'jam_mulai'      => '11:00',
            'jam_selesai'    => '09:00', // salah
            'keperluan'      => 'Test',
            'jenis_kegiatan' => 'rapat',
            'jumlah_peserta' => 10,
        ];

        $this->actingAs($this->dosen)
            ->post(route('reservasi.store'), $payload)
            ->assertSessionHasErrors('jam_selesai');
    }

    /** @test */
    public function pengajuan_gagal_jika_jam_selesai_lewat_dari_17_00()
    {
        $payload = [
            'ruang_kelas_id' => $this->ruang->id,
            'tanggal'        => now()->addDays(1)->format('Y-m-d'),
            'jam_mulai'      => '16:00',
            'jam_selesai'    => '18:00', // melewati batas operasional
            'keperluan'      => 'Test',
            'jenis_kegiatan' => 'rapat',
            'jumlah_peserta' => 10,
        ];

        $this->actingAs($this->dosen)
            ->post(route('reservasi.store'), $payload)
            ->assertSessionHasErrors('jam_selesai');

        $this->assertDatabaseCount('reservasi', 0);
    }

    /** @test */
    public function pengajuan_berhasil_jika_jam_selesai_persis_17_00()
    {
        $payload = [
            'ruang_kelas_id' => $this->ruang->id,
            'tanggal'        => now()->addDays(1)->format('Y-m-d'),
            'jam_mulai'      => '15:00',
            'jam_selesai'    => '17:00', // batas maksimal, harus tetap boleh
            'keperluan'      => 'Test',
            'jenis_kegiatan' => 'rapat',
            'jumlah_peserta' => 10,
        ];

        $this->actingAs($this->dosen)
            ->post(route('reservasi.store'), $payload)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('reservasi', ['jam_selesai' => '17:00']);
    }

    /** @test */
    public function pengajuan_gagal_jika_tanggal_di_masa_lalu()
    {
        $payload = [
            'ruang_kelas_id' => $this->ruang->id,
            'tanggal'        => now()->subDay()->format('Y-m-d'),
            'jam_mulai'      => '09:00',
            'jam_selesai'    => '11:00',
            'keperluan'      => 'Test',
            'jenis_kegiatan' => 'rapat',
            'jumlah_peserta' => 10,
        ];

        $this->actingAs($this->dosen)
            ->post(route('reservasi.store'), $payload)
            ->assertSessionHasErrors('tanggal');
    }

    /** @test */
    public function pengajuan_tidak_konflik_dengan_reservasi_berstatus_menunggu()
    {
        // Ada reservasi lain berstatus 'menunggu' di slot yang sama
        $pemohonLain = User::factory()->create(['role' => 'mahasiswa']);
        Reservasi::factory()->create([
            'ruang_kelas_id' => $this->ruang->id,
            'pemohon_id'     => $pemohonLain->id,
            'tanggal'        => now()->addDays(3)->format('Y-m-d'),
            'jam_mulai'      => '09:00',
            'jam_selesai'    => '11:00',
            'status'         => 'menunggu', // belum disetujui
        ]);

        $payload = [
            'ruang_kelas_id' => $this->ruang->id,
            'tanggal'        => now()->addDays(3)->format('Y-m-d'),
            'jam_mulai'      => '09:00',
            'jam_selesai'    => '11:00',
            'keperluan'      => 'Rapat',
            'jenis_kegiatan' => 'rapat',
            'jumlah_peserta' => 20,
        ];

        $this->actingAs($this->dosen)
            ->post(route('reservasi.store'), $payload)
            ->assertSessionHas('success'); // tersedia, tidak dianggap konflik
    }
}