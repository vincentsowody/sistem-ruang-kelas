<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Token reset password yang digenerate admin
            // Berbeda dari forgot-password bawaan Laravel (yang ada di tabel password_reset_tokens)
            $table->string('admin_reset_token')->nullable()->after('remember_token');
            $table->timestamp('admin_reset_token_expires_at')->nullable()->after('admin_reset_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['admin_reset_token', 'admin_reset_token_expires_at']);
        });
    }
};
