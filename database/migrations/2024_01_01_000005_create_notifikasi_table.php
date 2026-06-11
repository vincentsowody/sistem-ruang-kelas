<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reservasi_id')->nullable()->constrained('reservasi')->nullOnDelete();
            $table->string('judul');
            $table->text('pesan');
            $table->enum('tipe', ['reservasi_baru','disetujui','ditolak','dibatalkan','pengingat','info'])->default('info');
            $table->boolean('sudah_dibaca')->default(false);
            $table->timestamp('dibaca_pada')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'sudah_dibaca']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};
