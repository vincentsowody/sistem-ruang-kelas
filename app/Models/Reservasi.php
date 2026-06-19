<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Reservasi extends Model
{
    use HasFactory;

    protected $table = 'reservasi';

    protected $fillable = [
        'kode_reservasi', 'pemohon_id', 'ruang_kelas_id',
        'tanggal', 'jam_mulai', 'jam_selesai',
        'keperluan', 'jenis_kegiatan', 'jumlah_peserta',
        'keterangan', 'status', 'diproses_oleh', 'diproses_pada',
        'catatan_admin', 'ruang_saran_id', 'gunakan_saran',
    ];

    protected $casts = [
        'tanggal'        => 'date',
        'diproses_pada'  => 'datetime',
        'jumlah_peserta' => 'integer',
        'gunakan_saran'  => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->kode_reservasi)) {
                do {
                    $kode = 'RSV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
                } while (static::where('kode_reservasi', $kode)->exists());
                $model->kode_reservasi = $kode;
            }
            if (empty($model->status)) {
                $model->status = 'menunggu';
            }
        });
    }

    // ── Relasi ──────────────────────────────────────────
    public function pemohon()      { return $this->belongsTo(User::class, 'pemohon_id'); }
    public function ruangKelas()   { return $this->belongsTo(RuangKelas::class, 'ruang_kelas_id'); }
    public function ruangSaran()   { return $this->belongsTo(RuangKelas::class, 'ruang_saran_id'); }
    public function diprosesDari() { return $this->belongsTo(User::class, 'diproses_oleh'); }
    public function notifikasi()   { return $this->hasMany(Notifikasi::class, 'reservasi_id'); }

    // ── Scope ────────────────────────────────────────────
    public function scopeMenunggu($q)   { return $q->where('status', 'menunggu'); }
    public function scopeDisetujui($q)  { return $q->where('status', 'disetujui'); }
    public function scopeDitolak($q)    { return $q->where('status', 'ditolak'); }
    public function scopeDibatalkan($q) { return $q->where('status', 'dibatalkan'); }
    public function scopeHariIni($q)    { return $q->whereDate('tanggal', today()); }

    // ── Status helper ────────────────────────────────────
    public function isMenunggu(): bool   { return $this->status === 'menunggu'; }
    public function isDisetujui(): bool  { return $this->status === 'disetujui'; }
    public function isDitolak(): bool    { return $this->status === 'ditolak'; }
    public function isDibatalkan(): bool { return $this->status === 'dibatalkan'; }

    // ── Accessor ─────────────────────────────────────────

    /**
     * BUG FIX 1: getDurasiMenitAttribute() menggunakan strtotime("H:i")
     * yang tidak reliable karena strtotime("08:00") bisa menghasilkan timestamp
     * yang berbeda tergantung timezone server.
     * FIX: Gunakan Carbon untuk kalkulasi yang presisi.
     */
    public function getDurasiMenitAttribute(): int
    {
        [$hMulai, $mMulai]     = array_map('intval', explode(':', $this->jam_mulai));
        [$hSelesai, $mSelesai] = array_map('intval', explode(':', $this->jam_selesai));
        return ($hSelesai * 60 + $mSelesai) - ($hMulai * 60 + $mMulai);
    }

    public function getDurasiJamAttribute(): float
    {
        return round($this->durasi_menit / 60, 2);
    }

    public function getBadgeWarnaAttribute(): string
    {
        return match ($this->status) {
            'menunggu'   => 'warning',
            'disetujui'  => 'success',
            'ditolak'    => 'danger',
            'dibatalkan' => 'secondary',
            default      => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'menunggu'   => 'Menunggu',
            'disetujui'  => 'Disetujui',
            'ditolak'    => 'Ditolak',
            'dibatalkan' => 'Dibatalkan',
            default      => ucfirst($this->status),
        };
    }
}