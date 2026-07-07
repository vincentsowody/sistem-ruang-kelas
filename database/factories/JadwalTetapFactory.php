<?php

namespace Database\Factories;

use App\Models\RuangKelas;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class JadwalTetapFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ruang_kelas_id'          => RuangKelas::factory(),
            'dosen_id'                => User::factory()->state(['role' => 'dosen']),
            'mata_kuliah'             => $this->faker->words(3, true),
            'kode_mk'                 => strtoupper($this->faker->bothify('IF-###')),
            'kelas'                   => $this->faker->randomElement(['A', 'B', 'C']),
            'program_studi'           => 'Teknik Informatika',
            'semester'                => $this->faker->numberBetween(1, 8),
            'tahun_akademik'          => '2025/2026',
            'semester_ganjil_genap'   => 'ganjil',
            'hari'                    => $this->faker->randomElement(['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu']),
            'jam_mulai'               => '08:00',
            'jam_selesai'             => '10:00',
            'sks'                     => 2,
            'status'                  => 'aktif',
        ];
    }
}
