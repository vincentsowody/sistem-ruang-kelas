<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan index untuk meningkatkan performa query.
     */
    public function up(): void
    {
        // ==================================================
        // JADWAL TETAP
        // ==================================================
        Schema::table('jadwal_tetap', function (Blueprint $table) {

            $table->index(
                ['hari', 'tahun_akademik', 'semester_ganjil_genap', 'status'],
                'idx_jadwal_hari_semester_status'
            );

            $table->index(
                ['ruang_kelas_id', 'hari', 'status'],
                'idx_jadwal_ruang_hari'
            );

            $table->index(
                ['dosen_id', 'hari', 'status'],
                'idx_jadwal_dosen_hari'
            );
        });

        // ==================================================
        // RESERVASI
        // ==================================================
        Schema::table('reservasi', function (Blueprint $table) {

            /**
             * Index untuk pengecekan konflik reservasi ruang.
             *
             * Contoh query:
             * where ruang_kelas_id = ?
             * and tanggal = ?
             * and status = 'disetujui'
             */
            $table->index(
                ['ruang_kelas_id', 'tanggal', 'status'],
                'idx_reservasi_ruang_tanggal'
            );
        });

        // ==================================================
        // NOTIFIKASI
        // ==================================================
        Schema::table('notifikasi', function (Blueprint $table) {

            $table->index(
                ['user_id', 'sudah_dibaca', 'created_at'],
                'idx_notifikasi_user_dibaca'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ==================================================
        // JADWAL TETAP
        // ==================================================
        Schema::table('jadwal_tetap', function (Blueprint $table) {

            $table->dropIndex('idx_jadwal_hari_semester_status');
            $table->dropIndex('idx_jadwal_ruang_hari');
            $table->dropIndex('idx_jadwal_dosen_hari');
        });

        // ==================================================
        // RESERVASI
        // ==================================================
        Schema::table('reservasi', function (Blueprint $table) {

            $table->dropIndex('idx_reservasi_ruang_tanggal');
        });

        // ==================================================
        // NOTIFIKASI
        // ==================================================
        Schema::table('notifikasi', function (Blueprint $table) {

            $table->dropIndex('idx_notifikasi_user_dibaca');
        });
    }
};