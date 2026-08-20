<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tambah kolom NIP (Nomor Induk Pegawai)
            if (!Schema::hasColumn('users', 'nip')) {
                $table->string('nip', 100)->unique()->nullable()->after('role');
            }

            // Tambah kolom status (active/inactive/suspended)
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status', 20)->default('active')->after('nip');
            }

            // Tambah kolom last_login_at
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('status');
            }

            // Tambah kolom deleted_at untuk soft delete
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }

            // Index untuk performa
            $table->index(['role', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'status']);
            $table->dropColumn(['nip', 'status', 'last_login_at', 'deleted_at']);
        });
    }
};