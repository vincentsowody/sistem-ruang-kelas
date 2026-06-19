<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * BUG FIX C: unique_jadwal_ruang constraint salah.
     *
     * Constraint lama: UNIQUE(ruang_kelas_id, hari, jam_mulai, jam_selesai, tahun_akademik, semester_ganjil_genap)
     * Masalah:
     *   1. Hanya tangkap duplikat PERSIS SAMA (jam sama persis) — overlap tidak terdeteksi
     *   2. Dua jadwal 08:00-10:00 dan 08:00-10:00 ditolak (benar)
     *      Tapi 08:00-10:00 dan 09:00-11:00 LOLOS padahal bentrok (salah)
     *   3. Logic konflik yang benar harus di level aplikasi (WHERE overlap),
     *      bukan di unique constraint DB
     *
     * FIX: hapus unique constraint yang keliru — konflik sudah dihandle di
     * JadwalTetapController::cekKonflikJadwal() dengan WHERE overlap query.
     * Ganti dengan index biasa untuk performa query.
     */
    public function up(): void
    {
        Schema::table('jadwal_tetap', function (Blueprint $table) {
            // Hapus unique constraint yang tidak efektif
            $table->dropUnique('unique_jadwal_ruang');

            // Tambahkan index biasa untuk performa query cek konflik
            $table->index(
                ['ruang_kelas_id', 'hari', 'tahun_akademik', 'semester_ganjil_genap'],
                'idx_jadwal_cek_konflik'
            );
            $table->index(
                ['dosen_id', 'hari', 'tahun_akademik', 'semester_ganjil_genap'],
                'idx_jadwal_dosen_konflik'
            );
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_tetap', function (Blueprint $table) {
            $table->dropIndex('idx_jadwal_cek_konflik');
            $table->dropIndex('idx_jadwal_dosen_konflik');

            // Kembalikan unique constraint lama
            $table->unique(
                ['ruang_kelas_id', 'hari', 'jam_mulai', 'jam_selesai', 'tahun_akademik', 'semester_ganjil_genap'],
                'unique_jadwal_ruang'
            );
        });
    }
};