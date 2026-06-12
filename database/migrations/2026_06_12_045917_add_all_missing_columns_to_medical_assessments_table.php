<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_assessments', function (Blueprint $table) {
            // Tambahkan kolom riwayat_penyakit jika belum ada
            if (!Schema::hasColumn('medical_assessments', 'riwayat_penyakit')) {
                $table->text('riwayat_penyakit')->nullable()->after('keluhan_utama');
            }
            
            // Tambahkan kolom obat_diresepkan (JSON) jika belum ada
            if (!Schema::hasColumn('medical_assessments', 'obat_diresepkan')) {
                $table->json('obat_diresepkan')->nullable()->after('rencana_terapi');
            }
            
            // Tambahkan kolom catatan_tambahan jika belum ada
            if (!Schema::hasColumn('medical_assessments', 'catatan_tambahan')) {
                $table->text('catatan_tambahan')->nullable()->after('obat_diresepkan');
            }
            
            // Tambahkan kolom status jika belum ada
            if (!Schema::hasColumn('medical_assessments', 'status')) {
                $table->string('status')->default('draft')->after('catatan_tambahan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('medical_assessments', function (Blueprint $table) {
            $table->dropColumn([
                'riwayat_penyakit',
                'obat_diresepkan',
                'catatan_tambahan',
                'status'
            ]);
        });
    }
};