<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ruang_kelas', function (Blueprint $table) {
            $table->id();
            $table->string('kode_ruang')->unique()->comment('Contoh: R.101, LAB-A');
            $table->string('nama_ruang');
            $table->string('gedung');
            $table->integer('lantai');
            $table->integer('kapasitas')->comment('Jumlah kursi maksimal');
            $table->enum('jenis', ['kelas', 'laboratorium', 'aula', 'seminar'])->default('kelas');
            $table->json('fasilitas')->nullable()->comment('["proyektor","AC","papan_tulis","komputer"]');
            $table->enum('status', ['aktif', 'nonaktif', 'perbaikan'])->default('aktif');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ruang_kelas');
    }
};
