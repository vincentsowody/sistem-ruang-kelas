<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuangKelas extends Model
{
    use HasFactory;

    protected $table = 'ruang_kelas';

    protected $fillable = [
        'kode_ruang', 'nama_ruang', 'gedung', 'lantai',
        'kapasitas', 'jenis', 'fasilitas', 'status', 'keterangan',
    ];

    protected $casts = [
        'fasilitas' => 'array',
        'lantai'    => 'integer',
        'kapasitas' => 'integer',
    ];

    // ── Relasi ──────────────────────────────────────────
    public function jadwalTetap()
    {
        return $this->hasMany(JadwalTetap::class);
    }

    public function reservasi()
    {
        return $this->hasMany(Reservasi::class);
    }

    // ── Scope ────────────────────────────────────────────
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeKapasitasMin($query, int $min)
    {
        return $query->where('kapasitas', '>=', $min);
    }

    /**
     * Cek apakah ruang tersedia pada tanggal & jam tertentu.
     * Digunakan oleh algoritma greedy.
     */
    public function tersediaPada(string $tanggal, string $jamMulai, string $jamSelesai, ?int $kecualiReservasiId = null): bool
    {
        // Konversi tanggal ke nama hari (Indonesia) dengan fallback aman
        $carbonDate = Carbon::parse($tanggal);
        $hariMap    = [1=>'senin',2=>'selasa',3=>'rabu',4=>'kamis',5=>'jumat',6=>'sabtu',7=>'minggu'];
        $hari       = $hariMap[$carbonDate->dayOfWeekIso] ?? strtolower($carbonDate->locale('id')->dayName);

        // Cek bentrok dengan jadwal tetap
        // Dua slot overlap jika: jam_mulai < $jamSelesai DAN jam_selesai > $jamMulai
        // (slot bersebelahan persis, mis. 08:00-10:00 dan 10:00-12:00, TIDAK dianggap bentrok)
        $bentrokJadwal = $this->jadwalTetap()
            ->where('hari', $hari)
            ->where('status', 'aktif')
            ->where(function ($q) use ($jamMulai, $jamSelesai) {
                $q->where('jam_mulai', '<', $jamSelesai)
                  ->where('jam_selesai', '>', $jamMulai);
            })
            ->exists();

        if ($bentrokJadwal) return false;

        // Cek bentrok dengan reservasi yang sudah disetujui
        $query = $this->reservasi()
            ->where('tanggal', $tanggal)
            ->where('status', 'disetujui')
            ->where(function ($q) use ($jamMulai, $jamSelesai) {
                $q->where('jam_mulai', '<', $jamSelesai)
                  ->where('jam_selesai', '>', $jamMulai);
            });

        if ($kecualiReservasiId) {
            $query->where('id', '!=', $kecualiReservasiId);
        }

        return !$query->exists();
    }

    // ── Accessor ─────────────────────────────────────────
    public function getFasilitasListAttribute(): string
    {
        if (empty($this->fasilitas)) return '-';
        return implode(', ', array_map(fn($f) => ucwords(str_replace('_', ' ', $f)), $this->fasilitas));
    }
}
