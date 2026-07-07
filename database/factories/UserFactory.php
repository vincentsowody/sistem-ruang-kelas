<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            // FIX: kolom `is_active` di migration default-nya true, tapi factory
            // sebelumnya tidak menyertakannya sama sekali sehingga tersimpan NULL
            // saat dibuat lewat User::factory(). Karena RoleMiddleware memperlakukan
            // NULL sebagai "akun nonaktif" dan langsung redirect ke login, ini
            // membuat test yang lupa set 'is_active' => true gagal secara
            // membingungkan (redirect 302, bukan error yang jelas).
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}