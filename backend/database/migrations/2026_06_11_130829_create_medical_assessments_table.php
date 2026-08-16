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
    Schema::create('medical_assessments', function (Blueprint $table) {
        $table->bigIncrements('id_assessment');
        
        $table->unsignedBigInteger('id_pasien');
        $table->foreign('id_pasien')->references('id_pasien')->on('patients')->onDelete('cascade');
        
        $table->unsignedBigInteger('id_pengguna');
        $table->foreign('id_pengguna')->references('id')->on('users')->onDelete('cascade');
        
        $table->unsignedBigInteger('id_antrian')->nullable();
        $table->foreign('id_antrian')->references('id_antrian')->on('queues')->onDelete('set null');
        
        $table->text('keluhan_utama');
        $table->text('diagnosis');
        $table->text('catatan_medis');
        $table->json('hasil_pemeriksaan')->nullable();
        $table->text('rencana_terapi');
        $table->enum('status', ['draft', 'final'])->default('draft');
        $table->date('tanggal_assessment');
        $table->timestamps();
    });
}
};
