<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@kampus.ac.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Dosen',
            'email' => 'dosen@kampus.ac.id',
            'password' => bcrypt('password'),
            'role' => 'dosen',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Mahasiswa',
            'email' => 'mhs@kampus.ac.id',
            'password' => bcrypt('password'),
            'role' => 'mahasiswa',
            'is_active' => true,
        ]);
    }
}