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
    public function ruangKelas() { return $this->belongsTo(RuangKelas::class); }
    public function dosen()      { return $this->belongsTo(User::class, 'dosen_id'); }

    // ── Scope ────────────────────────────────────────────
    public function scopeAktif($q)              { return $q->where('status', 'aktif'); }
    public function scopeTahunAkademik($q, $t)  { return $q->where('tahun_akademik', $t); }

    /**
     * BUG FIX 2: getDurasiAttribute() sama seperti Reservasi —
     * strtotime("H:i") tidak reliable lintas timezone.
     * FIX: hitung manual dari jam dan menit.
     */
    public function getDurasiAttribute(): int
    {
        [$hMulai,   $mMulai]   = array_map('intval', explode(':', $this->jam_mulai));
        [$hSelesai, $mSelesai] = array_map('intval', explode(':', $this->jam_selesai));
        return ($hSelesai * 60 + $mSelesai) - ($hMulai * 60 + $mMulai);
    }
}