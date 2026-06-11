<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_tetap', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ruang_kelas_id')->constrained('ruang_kelas')->onDelete('cascade');
            $table->foreignId('dosen_id')->constrained('users')->onDelete('cascade');
            $table->string('mata_kuliah');
            $table->string('kode_mk')->nullable();
            $table->string('kelas')->comment('Contoh: A, B, C');
            $table->string('program_studi');
            $table->integer('semester');
            $table->string('tahun_akademik')->comment('Contoh: 2024/2025');
            $table->enum('semester_ganjil_genap', ['ganjil', 'genap']);
            $table->enum('hari', ['senin','selasa','rabu','kamis','jumat','sabtu']);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->integer('sks')->default(2);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();

            // Cegah bentrok: ruang yang sama, hari & jam yang sama
            $table->unique(
                ['ruang_kelas_id', 'hari', 'jam_mulai', 'jam_selesai', 'tahun_akademik', 'semester_ganjil_genap'],
                'unique_jadwal_ruang'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_tetap');
    }
};
