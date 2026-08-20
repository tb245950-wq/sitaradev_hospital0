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
    Schema::create('therapy_monitorings', function (Blueprint $table) {
        $table->id('id_monitoring');
        $table->foreignId('id_terapi')->constrained('therapies', 'id_terapi')->onDelete('cascade');
        $table->foreignId('id_pasien')->constrained('patients', 'id_pasien')->onDelete('cascade');
        $table->foreignId('id_terapis')->constrained('users', 'id')->onDelete('cascade');
        $table->date('tanggal_sesi');
        $table->time('waktu_mulai');
        $table->time('waktu_selesai');
        $table->enum('kehadiran', ['hadir', 'tidak_hadir', 'izin']);
        $table->text('catatan_perkembangan');
        $table->text('kondisi_pasien');
        $table->text('rekomendasi')->nullable();
        $table->integer('progress_score')->nullable(); // 0-100
        $table->timestamps();
    });
}
};
