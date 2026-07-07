<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Dipakai untuk memfilter jadwal kuliah milik mahasiswa yang login
            // (dicocokkan dengan kolom semester & kelas di tabel jadwal_tetap).
            // Nullable karena admin/dosen tidak memakai kolom ini.
            $table->integer('semester')->nullable()->after('program_studi');
            $table->string('kelas', 5)->nullable()->comment('Contoh: A, B, C')->after('semester');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['semester', 'kelas']);
        });
    }
};
