<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\Queue;
use App\Models\MedicalAssessment;
use App\Models\Therapy;
use App\Models\TherapyMonitoring;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SitaraDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Memulai generate data dummy SITARA...');
        
        // ===== PRIORITY 1: USERS (Dokter & Terapis) =====
        $this->command->info('📝 Creating users...');
        $users = $this->createUsers();
        
        // ===== PRIORITY 1: 50 PASIEN =====
        $this->command->info('👥 Creating 50 patients...');
        $patients = $this->createPatients(50);
        
        // ===== PRIORITY 1: ANTRIAN HARI INI =====
        $this->command->info('🎫 Creating today queues...');
        $queues = $this->createTodayQueues($patients, $users['dokter'], 15);
        
        // ===== PRIORITY 1: ASSESSMENT HARI INI =====
        $this->command->info('📋 Creating assessments...');
        $assessments = $this->createAssessments($patients, $users['dokter'], $queues, 8);
        
        // ===== PRIORITY 1: ACTIVITY LOGS =====
        $this->command->info('📊 Creating activity logs...');
        $this->createActivityLogs($patients, $users, $queues, $assessments);
        
        // ===== PRIORITY 2: PROGRAM TERAPI =====
        $this->command->info('🧠 Creating therapy programs...');
        $therapies = $this->createTherapies($patients, $assessments, $users, 10);
        
        // ===== PRIORITY 2: MONITORING/SESI TERAPI =====
        $this->command->info('📈 Creating monitoring sessions...');
        $this->createMonitoringSessions($therapies, $users['terapis'], 20);
        
        $this->command->info('✅ Data dummy SITARA berhasil dibuat!');
    }
    
    private function createUsers(): array
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@sitara.test'],
            [
                'name' => 'Super Admin',
                'role' => 'admin',
                'status' => 'active',
                'password' => 'admin123',
            ]
        );

        $dokter = User::firstOrCreate(
            ['email' => 'dokter.demo@sitara.test'],
            [
                'name' => 'Dr. Sarah Wijaya',
                'role' => 'dokter',
                'nip' => '20240001',
                'status' => 'active',
                'password' => 'password123',
            ]
        );
        
        $terapis = User::firstOrCreate(
            ['email' => 'terapis.demo@sitara.test'],
            [
                'name' => 'Rina Wina, S.Tr.Kes',
                'role' => 'terapis',
                'nip' => '20240002',
                'status' => 'active',
                'password' => 'password123',
            ]
        );
        
        return ['dokter' => $dokter, 'terapis' => $terapis, 'admin' => $admin];
    }
    
    private function createPatients(int $count): array
    {
        $maleNames = ['Ahmad', 'Budi', 'Candra', 'Dedi', 'Eko', 'Fajar', 'Galih', 'Hendra', 'Ilham', 'Joko', 'Kevin', 'Lukman', 'Muhammad', 'Naufal', 'Omar', 'Putra', 'Rizki', 'Surya', 'Teguh', 'Umar', 'Vino', 'Wahyu', 'Yoga', 'Zainal', 'Arka'];
        $femaleNames = ['Aisyah', 'Bunga', 'Citra', 'Dewi', 'Eka', 'Fitri', 'Gita', 'Hana', 'Indah', 'Julia', 'Kartika', 'Lestari', 'Maya', 'Nadia', 'Olivia', 'Putri', 'Qori', 'Rina', 'Sari', 'Tari', 'Utami', 'Vina', 'Wulan', 'Yuni', 'Zahra'];
        $lastNames = ['Pratama', 'Santoso', 'Wijaya', 'Kusuma', 'Purnama', 'Nugroho', 'Setiawan', 'Wibowo', 'Hakim', 'Pamungkas', 'Prasetyo', 'Utomo', 'Saputra', 'Hidayat', 'Rahman'];
        
        $diagnoses = ['Gangguan Bicara', 'ADHD', 'Autisme', 'Tuna Grahita', 'Gangguan Sensori', 'Down Syndrome', 'Cerebral Palsy', 'Gangguan Belajar', 'Gangguan Perilaku', 'Normal Development'];
        
        $patients = [];
        
        for ($i = 0; $i < $count; $i++) {
            $gender = ($i % 2 == 0) ? 'L' : 'P';
            $firstName = $gender === 'L' 
                ? $maleNames[array_rand($maleNames)] 
                : $femaleNames[array_rand($femaleNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $fullName = $firstName . ' ' . $lastName;
            
            $age = rand(2, 12);
            $birthDate = Carbon::now()->subYears($age)->subDays(rand(0, 365));
            
            $nik = '3201' . str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT) . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT) . str_pad($i + 1, 6, '0', STR_PAD_LEFT);
            $nrm = 'RM-' . date('Ymd') . '-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT) . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT);
            
            $patients[] = Patient::create([
                'nama_lengkap' => $fullName,
                'nama_panggilan' => $firstName,
                'nrm' => $nrm,
                'nik' => $nik,
                'tanggal_lahir' => $birthDate->format('Y-m-d'),
                'jenis_kelamin' => $gender,
                'alamat' => 'Jl. Contoh No. ' . ($i + 1) . ', Jakarta',
                'no_telepon_wali' => '0813' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                'nama_wali' => 'Bapak/Ibu ' . $lastName,
                'hubungan_wali' => 'Orang Tua',
                'riwayat_medis' => rand(0, 100) < 40 ? 'Riwayat ' . $diagnoses[array_rand($diagnoses)] : null,
            ]);
        }
        
        return $patients;
    }
    
    private function createTodayQueues(array $patients, User $dokter, int $count): array
    {
        $queues = [];
        $priorities = [0, 0, 0, 3, 7];
        $types = ['assessment', 'terapi'];
        $statuses = ['menunggu', 'menunggu', 'dipanggil', 'selesai', 'selesai'];
        
        for ($i = 0; $i < $count; $i++) {
            $patient = $patients[array_rand($patients)];
            $status = $statuses[$i % count($statuses)];
            $priority = $priorities[array_rand($priorities)];
            
            $queue = Queue::create([
                'id_pasien' => $patient->id_pasien,
                'id_pengguna' => $dokter->id,
                'nomor_antrian' => $i + 1,
                'jenis_layanan' => $types[array_rand($types)],
                'status' => $status,
                'prioritas' => $priority,
                'waktu_daftar' => Carbon::today()->setHour(rand(8, 14)),
                'waktu_panggil' => in_array($status, ['dipanggil', 'selesai']) ? Carbon::today()->subMinutes(rand(5, 60)) : null,
                'waktu_selesai' => $status === 'selesai' ? Carbon::today()->subMinutes(rand(1, 30)) : null,
            ]);
            
            $queues[] = $queue;
        }
        
        return $queues;
    }
    
    private function createAssessments(array $patients, User $dokter, array $queues, int $count): array
    {
        $assessments = [];
        $anamnesis = [
            'Orang tua melaporkan anak sulit berbicara dan hanya mengucapkan beberapa kata',
            'Anak menunjukkan perilaku hiperaktif dan sulit berkonsentrasi di kelas',
            'Anak tidak merespon saat dipanggil nama dan menghindari kontak mata',
            'Orang tua khawatir dengan perkembangan motorik anak yang terlambat',
            'Anak mengalami kesulitan belajar membaca dan menulis dibanding teman sebaya',
        ];
        $diagnoses = [
            'Gangguan Bahasa Ekspresif',
            'ADHD Tipe Kombinasi',
            'Gangguan Spektrum Autisme Level 1',
            'Gangguan Perkembangan Motorik',
            'Disleksia',
        ];
        
        for ($i = 0; $i < $count; $i++) {
            $patient = $patients[$i % count($patients)];
            $queue = $queues[$i % count($queues)];
            
            $assessment = MedicalAssessment::create([
                'id_pasien' => $patient->id_pasien,
                'id_pengguna' => $dokter->id,
                'id_antrian' => $queue->id_antrian,
                'tanggal_assessment' => Carbon::today()->subDays(rand(0, 3)),
                'keluhan_utama' => $anamnesis[$i % count($anamnesis)],
                'riwayat_penyakit' => 'Tidak ada',
                'hasil_pemeriksaan' => ['tensi' => '90/60', 'nadi' => '80', 'suhu' => '36.5'],
                'diagnosis' => $diagnoses[$i % count($diagnoses)],
                'rencana_terapi' => 'Program terapi rutin 2x seminggu',
                'status' => 'final',
            ]);
            
            $assessments[] = $assessment;
        }
        
        return $assessments;
    }
    
    private function createActivityLogs(array $patients, array $users, array $queues, array $assessments): void
    {
        foreach ($queues as $queue) {
            ActivityLog::create([
                'id_pasien' => $queue->id_pasien,
                'id_pengguna' => $users['admin']->id,
                'activity_type' => 'Registrasi Antrian',
                'department' => $queue->jenis_layanan === 'terapi' ? 'Terapi' : 'Umum',
                'status' => 'Baru',
                'description' => 'Nomor Antrian: Q' . str_pad($queue->nomor_antrian, 3, '0', STR_PAD_LEFT),
                'created_at' => $queue->waktu_daftar,
            ]);
        }
        
        foreach ($assessments as $assessment) {
            ActivityLog::create([
                'id_pasien' => $assessment->id_pasien,
                'id_pengguna' => $assessment->id_pengguna,
                'activity_type' => 'Assessment Medis',
                'department' => 'Umum',
                'status' => 'Selesai',
                'description' => 'Diagnosis: ' . $assessment->diagnosis,
                'created_at' => $assessment->created_at,
            ]);
        }
    }
    
    private function createTherapies(array $patients, array $assessments, array $users, int $count): array
    {
        $therapyNames = ['Terapi Wicara', 'Terapi Okupasi', 'Terapi Fisio', 'Terapi Bermain', 'Terapi Sensori Integrasi'];
        $goals = [
            'Meningkatkan kemampuan komunikasi verbal anak',
            'Mengembangkan keterampilan motorik halus',
            'Meningkatkan kemampuan sosial dan interaksi',
        ];
        
        $therapies = [];
        
        for ($i = 0; $i < $count; $i++) {
            $patient = $patients[$i % count($patients)];
            $assessment = $assessments[$i % count($assessments)] ?? null;
            
            $therapy = Therapy::create([
                'id_pasien' => $patient->id_pasien,
                'id_assessment' => $assessment?->id_assessment,
                'id_terapis' => $users['terapis']->id,
                'nama_terapi' => $therapyNames[$i % count($therapyNames)],
                'deskripsi' => $goals[$i % count($goals)],
                'frekuensi_per_minggu' => rand(1, 3),
                'durasi_hari' => 30,
                'status' => 'berjalan',
                'tanggal_mulai' => Carbon::now()->subDays(rand(7, 30)),
            ]);
            
            $therapies[] = $therapy;
        }
        
        return $therapies;
    }
    
    private function createMonitoringSessions(array $therapies, User $terapis, int $count): void
    {
        $totalScheduled = 0;
        $totalCompleted = 0;
        
        for ($i = 0; $i < $count; $i++) {
            $therapy = $therapies[$i % count($therapies)];
            $sessionDate = Carbon::now()->subDays(rand(1, 14));
            
            $isAttended = rand(1, 100) <= 85;
            $kehadiran = $isAttended ? 'hadir' : 'tidak_hadir';
            
            if ($isAttended) {
                $totalCompleted++;
            }
            $totalScheduled++;
            
            TherapyMonitoring::create([
                'id_terapi' => $therapy->id_terapi,
                'id_pasien' => $therapy->id_pasien,
                'id_terapis' => $terapis->id,
                'tanggal_sesi' => $sessionDate,
                'waktu_mulai' => '09:00',
                'waktu_selesai' => '10:00',
                'kehadiran' => $kehadiran,
                'catatan_perkembangan' => 'Anak kooperatif dan menunjukkan kemajuan.',
                'kondisi_pasien' => 'Stabil',
                'progress_score' => rand(60, 95),
            ]);
        }
        
        $attendanceRate = $totalScheduled > 0 ? round(($totalCompleted / $totalScheduled) * 100) : 0;
        $this->command->info("📊 Attendance Rate Generated: {$attendanceRate}% ({$totalCompleted}/{$totalScheduled} sesi)");
    }
}
