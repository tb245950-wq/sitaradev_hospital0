<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_assessments', function (Blueprint $table) {
            // Ubah kolom catatan_medis menjadi nullable (boleh kosong)
            $table->text('catatan_medis')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('medical_assessments', function (Blueprint $table) {
            $table->text('catatan_medis')->nullable(false)->change();
        });
    }
};