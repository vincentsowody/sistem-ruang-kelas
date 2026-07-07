<?php

namespace Tests\Feature;

use App\Models\Reservasi;
use App\Models\RuangKelas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\KirimNotifikasiReservasi;
use Tests\TestCase;

/**
 * Feature Test: Admin memproses reservasi.
 *
 * Mencakup:
 * - Admin setujui reservasi
 * - Admin tolak reservasi
 * - Admin pilihkan ruang terbaik (greedy) dan setujui
 * - Guard: non-admin tidak bisa akses endpoint admin
 * - Guard: reservasi yang sudah diproses tidak bisa diproses ulang
 */
class AdminReservasiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $dosen;
    private RuangKelas $ruang;
    private Reservasi $reservasi;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake(); // Jangan dispatch job sungguhan di test

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->dosen = User::factory()->create(['role' => 'dosen', 'is_active' => true]);

        $this->ruang = RuangKelas::factory()->create([
            'kode_ruang' => 'R.101',
            'kapasitas'  => 40,
            'status'     => 'aktif',
        ]);

        $this->reservasi = Reservasi::factory()->create([
            'ruang_kelas_id' => $this->ruang->id,
            'pemohon_id'     => $this->dosen->id,
            'tanggal'        => now()->addDays(5)->format('Y-m-d'),
            'jam_mulai'      => '09:00',
            'jam_selesai'    => '11:00',
            'jumlah_peserta' => 30,
            'status'         => 'menunggu',
        ]);
    }

    // ── Setujui ────────────────────────────────────────────────

    /** @test */
    public function admin_bisa_menyetujui_reservasi()
    {
        $this->actingAs($this->admin)
            ->post(route('admin.reservasi.setujui', $this->reservasi))
            ->assertRedirect();

        $this->assertDatabaseHas('reservasi', [
            'id'     => $this->reservasi->id,
            'status' => 'disetujui',
        ]);

        Queue::assertPushed(KirimNotifikasiReservasi::class);
    }

    /** @test */
    public function dosen_tidak_bisa_menyetujui_reservasi()
    {
        $this->actingAs($this->dosen)
            ->post(route('admin.reservasi.setujui', $this->reservasi))
            ->assertForbidden();

        $this->assertDatabaseHas('reservasi', [
            'id'     => $this->reservasi->id,
            'status' => 'menunggu', // tidak berubah
        ]);
    }

    /** @test */
    public function reservasi_yang_sudah_disetujui_tidak_bisa_disetujui_lagi()
    {
        $this->reservasi->update(['status' => 'disetujui']);

        $this->actingAs($this->admin)
            ->post(route('admin.reservasi.setujui', $this->reservasi))
            ->assertSessionHas('error');
    }

    // ── Tolak ─────────────────────────────────────────────────

    /** @test */
    public function admin_bisa_menolak_reservasi_dengan_alasan()
    {
        $this->actingAs($this->admin)
            ->post(route('admin.reservasi.tolak', $this->reservasi), [
                'catatan_admin' => 'Ruang sedang direnovasi.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('reservasi', [
            'id'            => $this->reservasi->id,
            'status'        => 'ditolak',
            'catatan_admin' => 'Ruang sedang direnovasi.',
        ]);
    }

    /** @test */
    public function penolakan_gagal_jika_alasan_kosong()
    {
        $this->actingAs($this->admin)
            ->post(route('admin.reservasi.tolak', $this->reservasi), [
                'catatan_admin' => '',
            ])
            ->assertSessionHasErrors('catatan_admin');

        $this->assertDatabaseHas('reservasi', [
            'id'     => $this->reservasi->id,
            'status' => 'menunggu',
        ]);
    }

    // ── Pilihkan Ruang ─────────────────────────────────────────

    /** @test */
    public function admin_bisa_pilihkan_ruang_dan_reservasi_langsung_disetujui()
    {
        $ruangLain = RuangKelas::factory()->create([
            'kode_ruang' => 'R.202',
            'kapasitas'  => 50,
            'status'     => 'aktif',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.reservasi.pilihkan-ruang', $this->reservasi), [
                'ruang_dipilih_id' => $ruangLain->id,
                'catatan_admin'    => 'Ruang asli diganti karena kebutuhan fasilitas.',
            ])
            ->assertRedirect(route('reservasi.show', $this->reservasi));

        $this->assertDatabaseHas('reservasi', [
            'id'             => $this->reservasi->id,
            'ruang_kelas_id' => $ruangLain->id,
            'status'         => 'disetujui',
            'diproses_oleh'  => $this->admin->id,
        ]);

        Queue::assertPushed(KirimNotifikasiReservasi::class);
    }

    /** @test */
    public function pilihkan_ruang_gagal_jika_ruang_dipilih_bentrok()
    {
        $pemohonLain = User::factory()->create(['role' => 'mahasiswa']);

        // Kunci ruang dengan reservasi disetujui di slot yang sama
        Reservasi::factory()->create([
            'ruang_kelas_id' => $this->ruang->id,
            'pemohon_id'     => $pemohonLain->id,
            'tanggal'        => $this->reservasi->tanggal->format('Y-m-d'),
            'jam_mulai'      => '09:00',
            'jam_selesai'    => '11:00',
            'status'         => 'disetujui',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.reservasi.pilihkan-ruang', $this->reservasi), [
                'ruang_dipilih_id' => $this->ruang->id,
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('reservasi', [
            'id'     => $this->reservasi->id,
            'status' => 'menunggu', // tidak berubah
        ]);
    }

    /** @test */
    public function pilihkan_ruang_tidak_bisa_dipakai_pada_reservasi_yang_sudah_diproses()
    {
        $this->reservasi->update(['status' => 'ditolak']);

        $this->actingAs($this->admin)
            ->post(route('admin.reservasi.pilihkan-ruang', $this->reservasi), [
                'ruang_dipilih_id' => $this->ruang->id,
            ])
            ->assertSessionHas('error');
    }

    // ── API Saran Ruang ────────────────────────────────────────

    /** @test */
    public function api_saran_ruang_admin_mengembalikan_json_dengan_daftar_ruang()
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.reservasi.saran-ruang', $this->reservasi));

        $response->assertOk()
            ->assertJsonStructure([
                'ruang_asli_konflik',
                'ruang_asli_detail',
                'tersedia',
                'bentrok',
                'jumlah_peserta',
            ]);

        // Ruang asli tidak konflik karena belum ada reservasi disetujui
        $response->assertJson(['ruang_asli_konflik' => false]);
    }

    /** @test */
    public function api_saran_ruang_menandai_ruang_yang_bentrok_dengan_benar()
    {
        $pemohonLain = User::factory()->create(['role' => 'mahasiswa']);

        // Kunci ruang asli
        Reservasi::factory()->create([
            'ruang_kelas_id' => $this->ruang->id,
            'pemohon_id'     => $pemohonLain->id,
            'tanggal'        => $this->reservasi->tanggal->format('Y-m-d'),
            'jam_mulai'      => '09:00',
            'jam_selesai'    => '11:00',
            'status'         => 'disetujui',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.reservasi.saran-ruang', $this->reservasi));

        $response->assertOk()
            ->assertJson(['ruang_asli_konflik' => true]);

        // Ruang asli harus masuk ke list bentrok, bukan tersedia
        $data = $response->json();
        $idBentrok = collect($data['bentrok'])->pluck('id')->toArray();
        $this->assertContains($this->ruang->id, $idBentrok);
    }
}
