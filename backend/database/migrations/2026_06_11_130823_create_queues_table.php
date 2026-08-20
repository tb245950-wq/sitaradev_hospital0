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
    Schema::create('queues', function (Blueprint $table) {
        $table->bigIncrements('id_antrian'); // ← UBAH dari $table->id()
        
        $table->unsignedBigInteger('id_pasien');
        $table->foreign('id_pasien')->references('id_pasien')->on('patients')->onDelete('cascade');
        
        $table->unsignedBigInteger('id_pengguna');
        $table->foreign('id_pengguna')->references('id')->on('users')->onDelete('cascade');
        
        $table->integer('nomor_antrian');
        $table->enum('jenis_layanan', ['assessment', 'terapi']);
        $table->enum('status', ['menunggu', 'dipanggil', 'selesai', 'tidak_hadir']);
        $table->integer('prioritas')->default(0);
        $table->timestamp('waktu_daftar');
        $table->timestamp('waktu_panggil')->nullable();
        $table->timestamp('waktu_selesai')->nullable();
        $table->text('catatan')->nullable();
        $table->timestamps();
    });
}
};
