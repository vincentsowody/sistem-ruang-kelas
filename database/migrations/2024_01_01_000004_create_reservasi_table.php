<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservasi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_reservasi')->unique()->comment('Kode unik otomatis, contoh: RSV-20240101-001');
            $table->foreignId('pemohon_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('ruang_kelas_id')->constrained('ruang_kelas')->onDelete('cascade');

            // Waktu reservasi
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');

            // Informasi kegiatan
            $table->string('keperluan')->comment('Nama/judul kegiatan');
            $table->enum('jenis_kegiatan', ['kuliah_pengganti','ujian','rapat','seminar','kegiatan_mahasiswa','lainnya']);
            $table->integer('jumlah_peserta');
            $table->text('keterangan')->nullable();

            // Alur approval
            $table->enum('status', ['menunggu','disetujui','ditolak','dibatalkan'])->default('menunggu');
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diproses_pada')->nullable();
            $table->text('catatan_admin')->nullable()->comment('Alasan penolakan atau catatan persetujuan');

            // Algoritma greedy — ruang yang disarankan sistem
            $table->foreignId('ruang_saran_id')->nullable()->constrained('ruang_kelas')->nullOnDelete()
                  ->comment('Ruang yang disarankan oleh algoritma greedy jika ruang pilihan tidak tersedia');
            $table->boolean('gunakan_saran')->default(false);

            $table->timestamps();

            // Index untuk mempercepat query cek konflik
            $table->index(['ruang_kelas_id', 'tanggal', 'jam_mulai', 'jam_selesai']);
            $table->index(['status', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservasi');
    }
};
