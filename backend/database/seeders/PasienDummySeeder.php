<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\MedicalAssessment;
use App\Models\Queue;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PasienDummySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🏥 Memulai generate 50 pasien dummy (REALISTIS)...');
        
        $dokter = User::where('role', 'dokter')->first();
        if (!$dokter) {
            $this->command->error('❌ Dokter tidak ditemukan!');
            return;
        }
        
        // Data per poli
        $polis = [
            'umum' => 15,
            'tumbuh_kembang' => 15,
            'psikolog' => 10,
            'terapi' => 10
        ];
        
        $totalCreated = 0;
        
        foreach ($polis as $poli => $count) {
            $this->command->info("📋 Generating Poli $poli ($count pasien)...");
            
            for ($i = 0; $i < $count; $i++) {
                // Variasi tanggal 15-18 Juni 2026
                $day = rand(15, 18);
                $createdAt = Carbon::create(2026, 6, $day, rand(8, 17), rand(0, 59));
                
                $nama = $this->generateName();
                $pasien = Patient::create([
                    'nrm' => 'RM-2026-' . str_pad(++$totalCreated, 4, '0', STR_PAD_LEFT),
                    'nik' => '3201' . str_pad(rand(1, 999999999999), 12, '0', STR_PAD_LEFT),
                    'nama_lengkap' => $nama,
                    'nama_panggilan' => explode(' ', $nama)[0],
                    'tanggal_lahir' => Carbon::now()->subYears(rand(1, 12))->subDays(rand(0, 364)),
                    'jenis_kelamin' => rand(0, 1) ? 'L' : 'P',
                    'alamat' => $this->generateAddress(),
                    'no_telepon_wali' => '08' . rand(11, 99) . rand(1000000, 9999999),
                    'nama_wali' => $this->generateName(),
                    'hubungan_wali' => rand(0, 1) ? 'Ayah' : 'Ibu',
                    'riwayat_medis' => 'Tidak ada riwayat alergi.',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $diagData = $this->getDataByPoli($poli);
                $diag = $diagData[array_rand($diagData)];

                $queue = Queue::create([
                    'id_pasien' => $pasien->id_pasien,
                    'id_pengguna' => $dokter->id,
                    'nomor_antrian' => $totalCreated,
                    'jenis_layanan' => $poli === 'terapi' ? 'terapi' : 'assessment',
                    'status' => 'selesai',
                    'prioritas' => rand(0, 2),
                    'waktu_daftar' => $createdAt,
                    'waktu_panggil' => $createdAt->copy()->addMinutes(rand(5, 15)),
                    'waktu_selesai' => $createdAt->copy()->addMinutes(rand(30, 45)),
                    'poli' => $poli,
                    'doctor_id' => $dokter->id,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                MedicalAssessment::create([
                    'id_pasien' => $pasien->id_pasien,
                    'id_pengguna' => $dokter->id,
                    'id_antrian' => $queue->id_antrian,
                    'tanggal_assessment' => $createdAt->toDateString(),
                    'keluhan_utama' => $diag['keluhan'],
                    'riwayat_penyakit' => 'Keluarga mengatakan gejala muncul sejak ' . rand(1, 3) . ' hari yang lalu.',
                    'hasil_pemeriksaan' => [
                        'tensi' => rand(90, 110) . '/' . rand(60, 75),
                        'nadi' => rand(80, 100),
                        'suhu' => rand(36, 38) . '.' . rand(0, 9),
                        'berat_badan' => rand(15, 35),
                        'tinggi_badan' => rand(100, 140)
                    ],
                    'diagnosis' => $diag['diagnosis'],
                    'rencana_terapi' => $diag['rekomendasi'],
                    'catatan_tambahan' => 'Assessment lengkap dilakukan.',
                    'status' => 'final',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                ActivityLog::create([
                    'id_pasien' => $pasien->id_pasien,
                    'id_pengguna' => $dokter->id,
                    'activity_type' => 'Assessment Medis',
                    'department' => ucfirst($poli),
                    'status' => 'Selesai',
                    'description' => 'Diagnosis: ' . $diag['diagnosis'],
                    'created_at' => $createdAt,
                ]);
            }
        }
        
        $this->command->info("✅ Selesai! Berhasil membuat $totalCreated pasien.");
    }
    
    private function getDataByPoli(string $poli): array
    {
        $data = [
            'umum' => [
                ['keluhan' => 'Demam tinggi', 'diagnosis' => 'Dengue Fever', 'rekomendasi' => 'Istirahat dan banyak minum'],
                ['keluhan' => 'Batuk pilek', 'diagnosis' => 'ISPA', 'rekomendasi' => 'Obat batuk & vitamin'],
                ['keluhan' => 'Diare', 'diagnosis' => 'GEA', 'rekomendasi' => 'Oralit & Zinc'],
            ],
            'tumbuh_kembang' => [
                ['keluhan' => 'Telat bicara', 'diagnosis' => 'Speech Delay', 'rekomendasi' => 'Terapi wicara rutin'],
                ['keluhan' => 'Hiperaktif', 'diagnosis' => 'ADHD', 'rekomendasi' => 'Terapi perilaku'],
                ['keluhan' => 'Gagal tumbuh', 'diagnosis' => 'Stunting', 'rekomendasi' => 'Perbaikan gizi'],
            ],
            'psikolog' => [
                ['keluhan' => 'Sering tantrum', 'diagnosis' => 'Emotional Disorder', 'rekomendasi' => 'Konseling anak'],
                ['keluhan' => 'Cemas berlebihan', 'diagnosis' => 'Anxiety', 'rekomendasi' => 'Play therapy'],
            ],
            'terapi' => [
                ['keluhan' => 'Kekakuan otot', 'diagnosis' => 'Cerebral Palsy', 'rekomendasi' => 'Fisioterapi'],
                ['keluhan' => 'Gangguan motorik', 'diagnosis' => 'Dyspraxia', 'rekomendasi' => 'Terapi okupasi'],
            ]
        ];
        return $data[$poli] ?? $data['umum'];
    }

    private function generateName(): string
    {
        $first = ['Ananda', 'Bagas', 'Cahya', 'Dimas', 'Erlangga', 'Fajar', 'Gibran', 'Haikal', 'Irfan', 'Jovan', 'Kevin', 'Lutfi', 'Mahesa', 'Naufal', 'Oky', 'Pandu', 'Raka', 'Saka', 'Tegar', 'Zaki'];
        $last = ['Putra', 'Ramadhan', 'Saputra', 'Wicaksono', 'Hidayat', 'Kurniawan', 'Pratama', 'Santoso', 'Zulkarnaen'];
        return $first[array_rand($first)] . ' ' . $last[array_rand($last)];
    }

    private function generateAddress(): string
    {
        $streets = ['Jl. Kaliurang', 'Jl. Solo', 'Jl. Magelang', 'Jl. Parangtritis', 'Jl. Godean'];
        return $streets[array_rand($streets)] . ' No. ' . rand(1, 200) . ', Sleman';
    }
}
