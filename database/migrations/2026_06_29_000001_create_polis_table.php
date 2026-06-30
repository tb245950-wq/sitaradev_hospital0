<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('polis', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('nama', 100);
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });

        // Seed data awal dari poli yang sudah dipakai di sistem
        DB::table('polis')->insert([
            ['kode' => 'umum',           'nama' => 'Poli Umum',           'deskripsi' => 'Konsultasi umum dan pemeriksaan awal',             'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['kode' => 'psikolog',       'nama' => 'Poli Psikolog',        'deskripsi' => 'Konsultasi psikologi anak dan keluarga',           'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['kode' => 'terapi',         'nama' => 'Poli Terapi',          'deskripsi' => 'Terapi wicara, okupasi, dan fisioterapi',          'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
            ['kode' => 'tumbuh_kembang', 'nama' => 'Poli Tumbuh Kembang',  'deskripsi' => 'Pemantauan tumbuh kembang anak',                  'status' => 'aktif', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('polis');
    }
};
