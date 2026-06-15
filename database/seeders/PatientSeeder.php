<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        
        // Male names Indonesia
        $maleNames = [
            'Ahmad', 'Budi', 'Candra', 'Dedi', 'Eko', 'Fajar', 'Gunawan', 'Hadi',
            'Indra', 'Joko', 'Kurniawan', 'Lukman', 'Muhammad', 'Nanda', 'Obi',
            'Putra', 'Rizki', 'Surya', 'Teguh', 'Usman', 'Vikri', 'Wahyu',
            'Yoga', 'Zainal', 'Abdul', 'Bagas', 'Dimas', 'Farhan', 'Galih',
            'Hendra', 'Ilham', 'Jefri', 'Kevin', 'Lutfi', 'Maulana', 'Naufal',
            'Omar', 'Pradita', 'Qori', 'Raffi', 'Satria', 'Taufik', 'Ulul',
            'Vino', 'Widi', 'Xavier', 'Yusuf', 'Zulfikar'
        ];
        
        // Female names Indonesia
        $femaleNames = [
            'Siti', 'Ayu', 'Dewi', 'Fitri', 'Gita', 'Hana', 'Indah', 'Jihan',
            'Kartika', 'Lestari', 'Maya', 'Nadia', 'Olivia', 'Putri', 'Qori',
            'Rina', 'Sari', 'Tari', 'Utami', 'Vina', 'Wulan', 'Xena', 'Yuni',
            'Zahra', 'Aisyah', 'Bunga', 'Citra', 'Diana', 'Eka', 'Farah',
            'Grace', 'Hesti', 'Ika', 'Julia', 'Kiki', 'Lina', 'Maria',
            'Nia', 'Olga', 'Putri', 'Rani', 'Siska', 'Tika', 'Uli', 'Vera',
            'Widya', 'Yanti', 'Zoe'
        ];
        
        // Last names
        $lastNames = [
            'Pratama', 'Santoso', 'Wijaya', 'Kusuma', 'Purnama', 'Nugroho',
            'Setiawan', 'Wibowo', 'Hakim', 'Pamungkas', 'Prasetyo', 'Utomo',
            'Saputra', 'Hidayat', 'Rahman', 'Firmansyah', 'Perdana', 'Nugraha',
            'Laksana', 'Maulana', 'Ramadhan', 'Siregar', 'Nasution', 'Harahap'
        ];
        
        // Cities
        $cities = [
            'Jakarta', 'Bandung', 'Surabaya', 'Medan', 'Semarang', 'Makassar',
            'Palembang', 'Tangerang', 'Depok', 'Bekasi', 'Bogor', 'Batam',
            'Pekanbaru', 'Bandar Lampung', 'Padang', 'Malang', 'Denpasar',
            'Samarinda', 'Balikpapan', 'Manado', 'Pontianak', 'Banjarmasin'
        ];
        
        // Streets
        $streets = [
            'Jl. Merdeka', 'Jl. Sudirman', 'Jl. Ahmad Yani', 'Jl. Gatot Subroto',
            'Jl. Thamrin', 'Jl. Diponegoro', 'Jl. Kartini', 'Jl. Pemuda',
            'Jl. Pahlawan', 'Jl. Raya Bogor', 'Jl. HR Rasuna Said',
            'Jl. TB Simatupang', 'Jl. Fatmawati', 'Jl. Panglima Polim'
        ];
        
        // Medical history
        $medicalHistories = [
            'Riwayat alergi susu sapi',
            'Sering demam tinggi',
            'Riwayat asma bronkial',
            'Alergi makanan laut',
            'Riwayat epilepsi',
            'Diabetes tipe 1',
            'Gangguan pendengaran',
            'Gangguan penglihatan',
            'ADHD (Attention Deficit Hyperactivity Disorder)',
            'Autisme spectrum disorder',
            'Gangguan bicara',
            'Cerebral palsy',
            'Down syndrome',
            'Thalassemia',
            'Penyakit jantung bawaan',
            'Gangguan tumbuh kembang',
            'Malnutrisi',
            'Stunting',
            'Tidak ada riwayat penyakit kronis',
            'Riwayat operasi appendicitis'
        ];

        $hubunganWali = ['Ayah', 'Ibu', 'Kakak', 'Kakek', 'Nenek', 'Paman', 'Bibi'];
        
        // Generate 100 patients
        for ($i = 0; $i < 100; $i++) {
            // Determine gender
            $gender = ($i % 2 == 0) ? 'L' : 'P';
            
            // Generate name based on gender
            if ($gender === 'L') {
                $firstName = $faker->randomElement($maleNames);
            } else {
                $firstName = $faker->randomElement($femaleNames);
            }
            
            $lastName = $faker->randomElement($lastNames);
            $middleName = $faker->word();
            $fullName = $firstName . ' ' . $middleName . ' ' . $lastName;
            
            // Generate date of birth (0-18 years old)
            $dateOfBirth = $faker->dateTimeBetween('-18 years', 'now')->format('Y-m-d');
            
            // Generate NIK (16 digits)
            $cityCode = str_pad($faker->numberBetween(11, 76), 2, '0', STR_PAD_LEFT);
            $districtCode = $faker->numberBetween(1, 99);
            $birthDate = date('dmy', strtotime($dateOfBirth));
            $uniqueCode = str_pad($faker->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT);
            $nik = $cityCode . $districtCode . $birthDate . $uniqueCode;
            
            // Generate NRM
            $nrm = 'RM-' . date('Ymd') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);

            // Generate address
            $street = $faker->randomElement($streets);
            $number = $faker->numberBetween(1, 999);
            $city = $faker->randomElement($cities);
            $rt = $faker->numberBetween(1, 10);
            $rw = $faker->numberBetween(1, 10);
            $postalCode = $faker->postcode();
            
            $address = "$street No. $number, RT.$rt/RW.$rw, $city, $postalCode";
            
            // Generate parent name
            $parentPrefix = $faker->randomElement(['Drs.', 'Dr.', 'H.', 'Hj.', 'Ir.', '']);
            $parentName = $parentPrefix . ' ' . $faker->name();
            $parentName = preg_replace('/\s+/', ' ', trim($parentName));
            
            // Generate parent phone
            $parentPhone = '08' . $faker->numberBetween(10, 99) . $faker->unique()->numerify('########');
            
            // Generate medical history (40% have history)
            $medicalHistory = null;
            if ($faker->boolean(40)) {
                $medicalHistory = $faker->randomElement($medicalHistories);
            }
            
            // Create patient
            Patient::create([
                'nrm' => $nrm,
                'nik' => $nik,
                'nama_lengkap' => $fullName,
                'nama_panggilan' => $firstName,
                'tanggal_lahir' => $dateOfBirth,
                'jenis_kelamin' => $gender,
                'alamat' => $address,
                'no_telepon_wali' => $parentPhone,
                'nama_wali' => $parentName,
                'hubungan_wali' => $faker->randomElement($hubunganWali),
                'riwayat_medis' => $medicalHistory,
            ]);
        }
        
        $this->command->info('✅ 100 data pasien berhasil dibuat!');
    }
}
