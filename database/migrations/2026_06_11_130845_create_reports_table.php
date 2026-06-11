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
    Schema::create('reports', function (Blueprint $table) {
        $table->id('id_laporan');
        $table->foreignId('id_pengguna')->constrained('users', 'id')->onDelete('cascade');
        $table->enum('tipe_laporan', ['harian', 'mingguan', 'bulanan', 'evaluasi_pasien']);
        $table->string('judul');
        $table->date('periode_mulai');
        $table->date('periode_selesai');
        $table->text('ringkasan_isi');
        $table->string('file_path')->nullable(); // Path file PDF
        $table->enum('status', ['draft', 'final'])->default('draft');
        $table->timestamps();
    });
}
};
