<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RuangKelasFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kode_ruang'  => 'R.' . $this->faker->unique()->numberBetween(100, 999),
            'nama_ruang'  => 'Ruang ' . $this->faker->word(),
            'gedung'      => $this->faker->randomElement(['Gedung A', 'Gedung B', 'Gedung C']),
            'lantai'      => $this->faker->numberBetween(1, 4),
            'kapasitas'   => $this->faker->numberBetween(20, 100),
            'jenis'       => $this->faker->randomElement(['kelas', 'laboratorium', 'aula', 'seminar']),
            'fasilitas'   => ['proyektor', 'AC', 'papan_tulis'],
            'status'      => 'aktif',
            'keterangan'  => null,
        ];
    }
}
