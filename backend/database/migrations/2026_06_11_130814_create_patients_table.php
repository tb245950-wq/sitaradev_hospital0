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
    Schema::create('patients', function (Blueprint $table) {
        $table->bigIncrements('id_pasien'); // ← Pertahankan ini (sesuai SRS)
        // $table->id(); // ← HAPUS/KOMENTARI baris ini!
        
        $table->string('nrm', 50)->unique();
        $table->string('nik', 20)->unique();
        $table->string('nama_lengkap');
        $table->string('nama_panggilan')->nullable();
        $table->date('tanggal_lahir');
        $table->enum('jenis_kelamin', ['L', 'P']);
        $table->text('alamat');
        $table->string('no_telepon_wali');
        $table->string('nama_wali');
        $table->string('hubungan_wali');
        $table->text('riwayat_medis')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
}
};
