<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('jenis_kelamin')->nullable()->change();
            $table->text('alamat')->nullable()->change();
            $table->string('nama_wali')->nullable()->change();
            $table->string('no_telepon_wali')->nullable()->change();
            $table->string('hubungan_wali')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('jenis_kelamin')->nullable(false)->change();
            $table->text('alamat')->nullable(false)->change();
            $table->string('nama_wali')->nullable(false)->change();
            $table->string('no_telepon_wali')->nullable(false)->change();
            $table->string('hubungan_wali')->nullable(false)->change();
        });
    }
};
