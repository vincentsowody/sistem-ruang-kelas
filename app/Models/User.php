<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nip_nim',
        'program_studi',
        'semester',
        'kelas',
        'no_hp',
        'is_active',
        'admin_reset_token',
        'admin_reset_token_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // =====================================================
    // RELASI
    // =====================================================

    public function jadwalTetap()
    {
        return $this->hasMany(JadwalTetap::class, 'dosen_id');
    }

    public function reservasi()
    {
        return $this->hasMany(Reservasi::class, 'pemohon_id');
    }

    public function notifikasi()
    {
        return $this->hasMany(Notifikasi::class);
    }

    /**
     * Jadwal kuliah milik mahasiswa ini, dicocokkan berdasarkan
     * program_studi + semester + kelas miliknya sendiri.
     * Ini BUKAN relasi FK biasa (mahasiswa tidak "memiliki" baris jadwal_tetap),
     * melainkan query berdasarkan kecocokan atribut — dipakai oleh
     * halaman "Jadwal Saya" agar mahasiswa semester 1 dan semester 3
     * (dst) melihat jadwal yang berbeda sesuai kelasnya masing-masing.
     */
    public function jadwalKelasSaya()
    {
        return JadwalTetap::aktif()
            ->where('program_studi', $this->program_studi)
            ->where('semester', $this->semester)
            ->where('kelas', $this->kelas);
    }

    // =====================================================
    // HELPER ROLE
    // =====================================================

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isDosen(): bool
    {
        return $this->role === 'dosen';
    }

    public function isMahasiswa(): bool
    {
        return $this->role === 'mahasiswa';
    }

    // =====================================================
    // NOTIFIKASI
    // =====================================================

    public function notifikasiBelumDibaca()
    {
        return $this->notifikasi()
                    ->where('sudah_dibaca', false);

        // Jika tabel notifikasi belum ada,
        // sementara bisa gunakan:
        // return collect();
    }

    // =====================================================
    // SCOPE
    // =====================================================

    public function scopeAdmin($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeDosen($query)
    {
        return $query->where('role', 'dosen');
    }

    public function scopeMahasiswa($query)
    {
        return $query->where('role', 'mahasiswa');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}