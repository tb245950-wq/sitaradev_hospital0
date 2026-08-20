<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pasien');
            $table->foreign('id_pasien')->references('id_pasien')->on('patients')->onDelete('cascade');
            $table->unsignedBigInteger('id_pengguna');
            $table->foreign('id_pengguna')->references('id')->on('users')->onDelete('cascade');
            $table->string('activity_type'); // 'assessment', 'therapy', 'registration', 'monitoring'
            $table->string('department')->nullable(); // 'Umum', 'Terapi', etc
            $table->string('status'); // 'Selesai', 'Berlangsung', 'Baru'
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['id_pasien', 'created_at']);
            $table->index(['id_pengguna', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
