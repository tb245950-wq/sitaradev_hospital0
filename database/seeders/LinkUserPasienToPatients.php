<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Patient;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class LinkUserPasienToPatients extends Seeder
{
    /**
     * Menghubungkan semua User dengan role 'pasien' ke tabel patients.
     * Jika user belum punya data patient, buat record baru.
     */
    public function run(): void
    {
        $this->command->info('🔗 Menghubungkan User Pasien ke Tabel Patients...');

        // Ambil semua user dengan role pasien
        $usersPasien = User::where('role', 'pasien')->get();

        $linked = 0;
        $created = 0;

        foreach ($usersPasien as $user) {
            // Cek apakah user ini sudah punya patient record
            $patient = Patient::where('user_id', $user->id)->first();

            if ($patient) {
                $this->command->info("✓ {$user->name} sudah terhubung ke patient: {$patient->nama_lengkap}");
                $linked++;
                continue;
            }

            // Cari patient yang namanya mirip (tanpa user_id atau cocok dengan nama user)
            $patientByName = Patient::where('nama_lengkap', 'LIKE', '%' . explode(' ', $user->name)[0] . '%')
                ->whereNull('user_id')
                ->first();

            if ($patientByName) {
                // Link patient yang sudah ada ke user ini
                $patientByName->user_id = $user->id;
                $patientByName->save();
                $this->command->info("🔗 Linked: {$user->name} => {$patientByName->nama_lengkap}");
                $linked++;
            } else {
                // Buat data patient baru untuk user ini
                $nameParts = explode(' ', $user->name);
                $firstName = $nameParts[0];
                
                $age = rand(5, 45); // Random age
                $birthDate = Carbon::now()->subYears($age)->subDays(rand(0, 365));
                
                // Generate NIK dan NRM unik
                $nik = $this->generateUniqueNik();
                $nrm = $this->generateUniqueNrm();

                $newPatient = Patient::create([
                    'user_id' => $user->id,
                    'nama_lengkap' => $user->name,
                    'nama_panggilan' => $firstName,
                    'nrm' => $nrm,
                    'nik' => $nik,
                    'tanggal_lahir' => $birthDate->format('Y-m-d'),
                    'jenis_kelamin' => $this->guessGender($firstName),
                    'alamat' => 'Jl. Dummy No. ' . rand(1, 100) . ', Jakarta',
                    'no_telepon_wali' => '0813' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                    'nama_wali' => 'Orang Tua ' . $user->name,
                    'hubungan_wali' => 'Orang Tua',
                    'riwayat_medis' => null,
                ]);

                $this->command->info("✨ Created NEW patient for: {$user->name} (NRM: {$nrm})");
                $created++;
            }
        }

        $this->command->info('');
        $this->command->info("✅ Selesai!");
        $this->command->info("   - Sudah terhubung: {$linked}");
        $this->command->info("   - Dibuat baru: {$created}");
        $this->command->info("   - Total: " . ($linked + $created));
    }

    private function generateUniqueNik(): string
    {
        do {
            $nik = '3201' . str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT) 
                   . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT) 
                   . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
            
            // Check uniqueness using nik_hash instead of encrypted nik
            $nikHash = hash('sha256', $nik);
        } while (Patient::where('nik_hash', $nikHash)->exists());

        return $nik;
    }

    private function generateUniqueNrm(): string
    {
        do {
            $nrm = 'NRM-' . date('Ymd') . '-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        } while (Patient::where('nrm', $nrm)->exists());

        return $nrm;
    }

    private function guessGender(string $firstName): string
    {
        // Nama yang biasa perempuan
        $femaleNames = [
            'Aisyah', 'Bunga', 'Citra', 'Dewi', 'Eka', 'Fitri', 'Gita', 'Hani', 
            'Indah', 'Julia', 'Kartika', 'Lina', 'Maya', 'Nina', 'Okta', 
            'Putri', 'Qori', 'Rina', 'Siti', 'Tina', 'Ulfa', 'Vera', 'Winda', 
            'Xenia', 'Yuli', 'Zahra', 'Dina'
        ];

        foreach ($femaleNames as $femaleName) {
            if (stripos($firstName, $femaleName) !== false) {
                return 'P';
            }
        }

        return 'L'; // Default laki-laki
    }
}
