<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    use HasFactory;

    protected $table = 'notifikasi';

    protected $fillable = [
        'user_id', 'reservasi_id', 'judul',
        'pesan', 'tipe', 'sudah_dibaca', 'dibaca_pada',
    ];

    protected $casts = [
        'sudah_dibaca' => 'boolean',
        'dibaca_pada'  => 'datetime',
    ];

    // ── Relasi ──────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reservasi()
    {
        return $this->belongsTo(Reservasi::class);
    }

    // ── Helper ───────────────────────────────────────────
    public function tandaiSudahDibaca(): void
    {
        $this->update([
            'sudah_dibaca' => true,
            'dibaca_pada'  => now(),
        ]);
    }

    public function getIkonAttribute(): string
    {
        return match($this->tipe) {
            'reservasi_baru' => '📋',
            'disetujui'      => '✅',
            'ditolak'        => '❌',
            'dibatalkan'     => '🚫',
            'pengingat'      => '⏰',
            default          => 'ℹ️',
        };
    }
}
