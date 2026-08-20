<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_assessments', function (Blueprint $table) {
            // Tambah kolom riwayat_penyakit jika belum ada
            if (!Schema::hasColumn('medical_assessments', 'riwayat_penyakit')) {
                $table->text('riwayat_penyakit')->nullable()->after('keluhan_utama');
            }
        });
    }

    public function down(): void
    {
        Schema::table('medical_assessments', function (Blueprint $table) {
            $table->dropColumn('riwayat_penyakit');
        });
    }
};