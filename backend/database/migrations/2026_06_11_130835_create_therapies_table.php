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
    Schema::create('therapies', function (Blueprint $table) {
        $table->id('id_terapi');
        $table->foreignId('id_assessment')->constrained('medical_assessments', 'id_assessment')->onDelete('cascade');
        $table->foreignId('id_pasien')->constrained('patients', 'id_pasien')->onDelete('cascade');
        $table->foreignId('id_terapis')->constrained('users', 'id')->onDelete('cascade');
        $table->string('nama_terapi');
        $table->text('deskripsi')->nullable();
        $table->string('dosis')->nullable();
        $table->integer('durasi_hari');
        $table->integer('frekuensi_per_minggu');
        $table->enum('status', ['terjadwal', 'berjalan', 'selesai', 'dihentikan'])->default('terjadwal');
        $table->date('tanggal_mulai');
        $table->date('tanggal_selesai')->nullable();
        $table->timestamps();
    });
}
};
