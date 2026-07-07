<?php

namespace Database\Factories;

use App\Models\RuangKelas;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservasiFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kode_reservasi'   => 'RSV-' . now()->format('Ymd') . '-' . $this->faker->unique()->numberBetween(1, 9999),
            'pemohon_id'       => User::factory(),
            'ruang_kelas_id'   => RuangKelas::factory(),
            'tanggal'          => now()->addDays(3)->format('Y-m-d'),
            'jam_mulai'        => '13:00',
            'jam_selesai'      => '15:00',
            'keperluan'        => $this->faker->sentence(3),
            'jenis_kegiatan'   => 'rapat',
            'jumlah_peserta'   => $this->faker->numberBetween(5, 30),
            'keterangan'       => null,
            'status'           => 'menunggu',
            'diproses_oleh'    => null,
            'diproses_pada'    => null,
            'catatan_admin'    => null,
            'ruang_saran_id'   => null,
            'gunakan_saran'    => false,
        ];
    }
}
