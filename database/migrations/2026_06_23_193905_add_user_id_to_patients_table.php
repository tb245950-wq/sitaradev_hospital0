<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cek apakah kolom user_id sudah ada
        if (!Schema::hasColumn('patients', 'user_id')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->foreignId('user_id')
                      ->nullable()
                      ->after('id_pasien')
                      ->constrained('users')
                      ->nullOnDelete();
            });
        }

        // Tambah index untuk kolom yang ada
        if (Schema::hasColumn('patients', 'nik')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->index('nik');
            });
        }

        if (Schema::hasColumn('patients', 'nrm')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->index('nrm');
            });
        }

        if (Schema::hasColumn('patients', 'user_id')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->index('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            if (Schema::hasColumn('patients', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropIndex(['user_id']);
                $table->dropColumn('user_id');
            }
            $table->dropIndex(['nik']);
            $table->dropIndex(['nrm']);
        });
    }
};