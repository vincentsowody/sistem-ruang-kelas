<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalTetap extends Model
{
    use HasFactory;

    protected $table = 'jadwal_tetap';

    protected $fillable = [
        'ruang_kelas_id', 'dosen_id', 'mata_kuliah', 'kode_mk',
        'kelas', 'program_studi', 'semester', 'tahun_akademik',
        'semester_ganjil_genap', 'hari', 'jam_mulai', 'jam_selesai',
        'sks', 'status',
    ];

    protected $casts = [
        'semester' => 'integer',
        'sks'      => 'integer',
    ];

    // ── Relasi ──────────────────────────────────────────
    public function ruangKelas()
    {
        return $this->belongsTo(RuangKelas::class);
    }

    public function dosen()
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    // ── Scope ────────────────────────────────────────────
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeTahunAkademik($query, string $tahun)
    {
        return $query->where('tahun_akademik', $tahun);
    }

    // ── Accessor ─────────────────────────────────────────
    public function getDurasiAttribute(): int
    {
        return (int) round(
            (strtotime($this->jam_selesai) - strtotime($this->jam_mulai)) / 60
        );
    }
}
