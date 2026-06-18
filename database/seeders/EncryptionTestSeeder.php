<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class EncryptionTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🧪 Menjalankan test enkripsi PII...');

        // Ambil satu pasien secara random
        $patient = DB::table('patients')->first();

        if (!$patient) {
            $this->command->error('❌ Tidak ada pasien untuk ditest.');
            return;
        }

        $this->command->line("Pasien: {$patient->nama_lengkap}");
        $this->command->line("Data di Database (Encrypted):");
        $this->command->line("  - NIK: " . substr($patient->nik, 0, 30) . "...");
        $this->command->line("  - Alamat: " . substr($patient->alamat, 0, 30) . "...");

        try {
            $decryptedNik = Crypt::decryptString($patient->nik);
            $decryptedAlamat = Crypt::decryptString($patient->alamat);

            $this->command->info("✅ Berhasil Dekripsi:");
            $this->command->line("  - NIK Asli: {$decryptedNik}");
            $this->command->line("  - Alamat Asli: {$decryptedAlamat}");
        } catch (\Exception $e) {
            $this->command->error("❌ Gagal Dekripsi: " . $e->getMessage());
        }
    }
}
