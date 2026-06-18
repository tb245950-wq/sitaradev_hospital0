<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Enkripsi data NIK dan Alamat yang sudah ada di tabel patients.
     */
    public function up(): void
    {
        // 1. Ubah tipe data kolom menjadi TEXT untuk menampung string terenkripsi yang panjang
        Schema::table('patients', function (Blueprint $table) {
            $table->text('nik')->change();
            $table->text('alamat')->change();
        });

        // 2. Ambil semua data dan enkripsi
        $patients = DB::table('patients')->get();

        foreach ($patients as $patient) {
            $encryptedNik = !empty($patient->nik) ? Crypt::encryptString($patient->nik) : $patient->nik;
            $encryptedAlamat = !empty($patient->alamat) ? Crypt::encryptString($patient->alamat) : $patient->alamat;

            DB::table('patients')
                ->where('id_pasien', $patient->id_pasien)
                ->update([
                    'nik' => $encryptedNik,
                    'alamat' => $encryptedAlamat
                ]);
        }
    }

    /**
     * Reverse the migrations.
     * Dekripsi data kembali jika migrasi di-rollback.
     */
    public function down(): void
    {
        $patients = DB::table('patients')->get();

        foreach ($patients as $patient) {
            try {
                $decryptedNik = !empty($patient->nik) ? Crypt::decryptString($patient->nik) : $patient->nik;
                $decryptedAlamat = !empty($patient->alamat) ? Crypt::decryptString($patient->alamat) : $patient->alamat;

                DB::table('patients')
                    ->where('id_pasien', $patient->id_pasien)
                    ->update([
                        'nik' => $decryptedNik,
                        'alamat' => $decryptedAlamat
                    ]);
            } catch (\Exception $e) {
                // Skip jika tidak bisa di-decrypt
            }
        }

        // Kembalikan ke tipe data semula (Sesuaikan dengan skema asli, asumsikan string(20) dan text)
        Schema::table('patients', function (Blueprint $table) {
            $table->string('nik', 20)->change();
            $table->text('alamat')->change();
        });
    }
};
