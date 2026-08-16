<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Patient;
use App\Models\Queue;
use App\Models\MedicalAssessment;
use App\Models\Therapy;
use App\Models\TherapyMonitoring;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class HospitalDummySeeder1000 extends Seeder
{
    private array $namaLaki = [
        'Ahmad Fauzi', 'Budi Santoso', 'Cahyo Pratama', 'Deni Kurniawan', 'Eko Saputra',
        'Fajar Nugroho', 'Gilang Ramadhan', 'Hendra Wijaya', 'Ivan Setiawan', 'Joko Susilo',
    ];

    private array $namaPerempuan = [
        'Aisyah Putri', 'Bunga Citra', 'Citra Dewi', 'Dina Marlina', 'Eka Sulistyowati',
        'Fitri Handayani', 'Gita Puspita', 'Hani Rahmawati', 'Indah Permatasari', 'Julia Andriani',
    ];

    private array $keluhanList = [
        'Anak sulit berbicara dan berkomunikasi', 'Keterlambatan bicara pada anak',
        'Gangguan konsentrasi dan hiperaktif', 'Anak sering tantrum berlebihan',
        'Keterlambatan perkembangan motorik',
    ];

    private array $diagnosisList = [
        'Autism Spectrum Disorder (ASD)', 'Speech Delay', 'ADHD', 'Global Development Delay',
        'Sensory Processing Disorder',
    ];

    private array $namaTermapiList = [
        'Terapi Wicara', 'Terapi Okupasi', 'Terapi Perilaku (ABA)', 'Terapi Sensori Integrasi',
        'Fisioterapi',
    ];

    private array $alamatList = [
        'Jl. Merdeka No. 12, Bandung', 'Jl. Sudirman No. 45, Jakarta', 'Jl. Diponegoro No. 8, Surabaya',
        'Jl. Pahlawan No. 23, Yogyakarta', 'Jl. Ahmad Yani No. 67, Semarang',
    ];

    private array $namaWaliList = [
        'Sumardi', 'Sumiati', 'Hendra Gunawan', 'Ratna Dewi', 'Agus Pranoto',
    ];

    private array $rencanaTermapiList = [
        'Terapi wicara 2x seminggu selama 3 bulan', 'Terapi okupasi 3x seminggu selama 2 bulan',
        'Terapi ABA intensif 5x seminggu selama 6 bulan',
    ];

    private array $catatanPerkembangan = [
        'Pasien menunjukkan kemajuan dalam komunikasi verbal', 'Kemampuan motorik halus meningkat signifikan',
        'Pasien lebih responsif terhadap instruksi', 'Konsentrasi membaik dibanding sesi sebelumnya',
    ];

    private array $kondisiPasienList = [
        'Membaik', 'Stabil', 'Perlu perhatian lebih', 'Sangat membaik', 'Cukup baik',
    ];

    private array $rekomendasiList = [
        'Lanjutkan sesi terapi dengan intensitas yang sama', 'Tingkatkan frekuensi sesi menjadi 3x seminggu',
        'Tambahkan latihan mandiri di rumah', 'Evaluasi ulang program terapi bulan depan',
    ];

    private array $poliOptions = ['Poli Umum', 'Poli Psikolog', 'Poli Terapi', 'Poli Tumbuh Kembang'];

    public function run(): void
    {
        $dokter = User::where('role', 'dokter')->first();
        $terapis = User::where('role', 'terapis')->first();

        if (!$dokter || !$terapis) {
            $this->command->error('Dokter atau terapis tidak ditemukan!');
            return;
        }

        $startDate = Carbon::create(2026, 7, 6);
        $today = Carbon::today();

        $this->command->info('=== Menambahkan 500 data dummy pasien (total: 1000) ===');

        $startId = Patient::max('id_pasien') ?? 0;
        $allPatients = $this->buatPasien(500, $startId, $startDate, $today);

        $this->buatAntrian($allPatients, $dokter, $startDate, $today);
        $this->buatAssessment($allPatients, $dokter, $startDate, $today);
        $this->buatTerapi($allPatients, $terapis, $startDate, $today);
        $this->buatMonitoring($allPatients, $terapis, $startDate, $today);
        $this->buatKombinasi($allPatients, $dokter, $terapis, $startDate, $today);

        $this->command->info('✓ 500 pasien tambahan berhasil ditambahkan!');
    }

    private function buatPasien(int $total, int $startId, Carbon $startDate, Carbon $today): array
    {
        $allPatients = [];
        $this->command->info("Membuat {$total} user & pasien...");

        for ($i = 1; $i <= $total; $i++) {
            $idx = $startId + $i;
            $isLaki = ($i % 2 === 1);
            $namaPool = $isLaki ? $this->namaLaki : $this->namaPerempuan;
            $nama = $namaPool[($i - 1) % count($namaPool)] . ' ' . $this->sufiks($idx);
            $jk = $isLaki ? 'L' : 'P';
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nama));
            $email = $slug . $idx . '@pasien.test';

            $tglDaftar = $startDate->copy()->addDays(rand(0, $startDate->diffInDays($today)));
            $nrm = 'NRM-' . $tglDaftar->format('Ymd') . '-' . str_pad($idx, 4, '0', STR_PAD_LEFT);
            $tglLahir = Carbon::create(rand(2008, 2020), rand(1, 12), rand(1, 28));

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $nama,
                    'password' => Hash::make('password123'),
                    'role' => 'pasien',
                    'status' => 'active',
                ]
            );

            $plainNik = $this->generateNik($idx);
            $patient = Patient::updateOrCreate(
                ['nrm' => $nrm],
                [
                    'user_id' => $user->id,
                    'nik' => $plainNik,
                    'nik_hash' => hash('sha256', $plainNik),
                    'nama_lengkap' => $nama,
                    'nama_panggilan' => explode(' ', $nama)[0],
                    'tanggal_lahir' => $tglLahir->format('Y-m-d'),
                    'jenis_kelamin' => $jk,
                    'alamat' => $this->alamatList[($i - 1) % count($this->alamatList)],
                    'no_telepon_wali' => '08' . rand(100000000, 999999999),
                    'nama_wali' => $this->namaWaliList[($i - 1) % count($this->namaWaliList)],
                    'hubungan_wali' => ['Ayah', 'Ibu', 'Wali'][$i % 3],
                    'riwayat_medis' => null,
                ]
            );

            $allPatients[] = [
                'user' => $user,
                'patient' => $patient,
                'keluhan' => $this->keluhanList[($i - 1) % count($this->keluhanList)],
                'diagnosis' => $this->diagnosisList[($i - 1) % count($this->diagnosisList)],
                'terapi' => $this->namaTermapiList[($i - 1) % count($this->namaTermapiList)],
                'tglDaftar' => $tglDaftar,
            ];
        }

        $this->command->info("  ✓ {$total} pasien dibuat");
        return $allPatients;
    }

    private function buatAntrian(array $allPatients, User $dokter, Carbon $startDate, Carbon $today): void
    {
        $this->command->info('Membuat antrian (100 pasien pertama)...');
        $statusList = ['menunggu', 'dipanggil', 'selesai', 'tidak_hadir'];

        for ($i = 0; $i < min(100, count($allPatients)); $i++) {
            $p = $allPatients[$i];
            $status = $statusList[$i % count($statusList)];
            $tglAntrian = $p['tglDaftar']->copy();
            $waktuDaftar = $tglAntrian->copy()->setTime(rand(7, 11), rand(0, 59));
            $waktuPanggil = in_array($status, ['dipanggil', 'selesai']) ? $waktuDaftar->copy()->addMinutes(rand(10, 45)) : null;
            $waktuSelesai = $status === 'selesai' ? $waktuDaftar->copy()->addMinutes(rand(50, 120)) : null;

            Queue::create([
                'id_pasien' => $p['patient']->id_pasien,
                'id_pengguna' => $p['user']->id,
                'nomor_antrian' => $i + 1,
                'jenis_layanan' => $i % 2 === 0 ? 'assessment' : 'terapi',
                'status' => $status,
                'prioritas' => $i % 10 === 0 ? 1 : 0,
                'waktu_daftar' => $waktuDaftar,
                'waktu_panggil' => $waktuPanggil,
                'waktu_selesai' => $waktuSelesai,
                'poli' => $this->poliOptions[$i % count($this->poliOptions)],
                'doctor_id' => $dokter->id,
                'catatan' => 'Kunjungan: ' . $p['keluhan'],
            ]);
        }

        $this->command->info('  ✓ Antrian dibuat');
    }

    private function buatAssessment(array $allPatients, User $dokter, Carbon $startDate, Carbon $today): void
    {
        $this->command->info('Membuat assessment (100 pasien berikutnya)...');

        for ($i = 100; $i < min(200, count($allPatients)); $i++) {
            $p = $allPatients[$i];
            $tglAntrian = $p['tglDaftar']->copy()->setTime(rand(7, 11), rand(0, 59));
            $waktuPanggil = $tglAntrian->copy()->addMinutes(rand(10, 30));

            $queue = Queue::create([
                'id_pasien' => $p['patient']->id_pasien,
                'id_pengguna' => $p['user']->id,
                'nomor_antrian' => $i + 1,
                'jenis_layanan' => 'assessment',
                'status' => 'dipanggil',
                'prioritas' => 0,
                'waktu_daftar' => $tglAntrian,
                'waktu_panggil' => $waktuPanggil,
                'waktu_selesai' => null,
                'poli' => $this->poliOptions[$i % count($this->poliOptions)],
                'doctor_id' => $dokter->id,
                'catatan' => 'Untuk assessment: ' . $p['keluhan'],
            ]);

            $statusAssessment = $i % 3 === 0 ? 'draft' : 'final';
            $tglAssessment = $p['tglDaftar']->copy();

            MedicalAssessment::create([
                'id_pasien' => $p['patient']->id_pasien,
                'id_pengguna' => $dokter->id,
                'id_antrian' => $queue->id_antrian,
                'keluhan_utama' => $p['keluhan'],
                'diagnosis' => $p['diagnosis'],
                'riwayat_penyakit' => 'Tidak ada riwayat penyakit kronis sebelumnya',
                'hasil_pemeriksaan' => json_encode(['berat_badan' => rand(10, 25) . ' kg', 'tinggi_badan' => rand(90, 130) . ' cm']),
                'rencana_terapi' => $this->rencanaTermapiList[$i % count($this->rencanaTermapiList)],
                'catatan_medis' => $statusAssessment === 'final' ? 'Pasien kooperatif. Diagnosis: ' . $p['diagnosis'] : null,
                'status' => $statusAssessment,
                'tanggal_assessment' => $tglAssessment->format('Y-m-d'),
            ]);
        }

        $this->command->info('  ✓ Assessment dibuat');
    }

    private function buatTerapi(array $allPatients, User $terapis, Carbon $startDate, Carbon $today): void
    {
        $this->command->info('Membuat terapi (100 pasien berikutnya)...');
        $dokter = User::where('role', 'dokter')->first();
        $statusTerapiList = ['terjadwal', 'berjalan', 'selesai', 'dihentikan'];

        for ($i = 200; $i < min(300, count($allPatients)); $i++) {
            $p = $allPatients[$i];
            $statusTerapi = $statusTerapiList[$i % count($statusTerapiList)];
            $tglMulai = $p['tglDaftar']->copy();

            $assessment = MedicalAssessment::create([
                'id_pasien' => $p['patient']->id_pasien,
                'id_pengguna' => $dokter->id,
                'id_antrian' => null,
                'keluhan_utama' => $p['keluhan'],
                'diagnosis' => $p['diagnosis'],
                'riwayat_penyakit' => 'Tidak ada riwayat penyakit kronis sebelumnya',
                'hasil_pemeriksaan' => json_encode(['kondisi_umum' => 'Baik']),
                'rencana_terapi' => $this->rencanaTermapiList[$i % count($this->rencanaTermapiList)],
                'catatan_medis' => 'Assessment sebelum terapi. Diagnosis: ' . $p['diagnosis'],
                'status' => 'final',
                'tanggal_assessment' => $tglMulai->format('Y-m-d'),
            ]);

            $tglSelesai = in_array($statusTerapi, ['selesai', 'dihentikan']) ? $tglMulai->copy()->addDays(rand(7, 30)) : null;

            Therapy::create([
                'id_assessment' => $assessment->id_assessment,
                'id_pasien' => $p['patient']->id_pasien,
                'id_terapis' => $terapis->id,
                'nama_terapi' => $p['terapi'],
                'deskripsi' => 'Program ' . $p['terapi'] . ' untuk ' . $p['diagnosis'],
                'dosis' => rand(1, 3) . 'x per sesi',
                'durasi_hari' => rand(30, 90),
                'frekuensi_per_minggu' => rand(2, 5),
                'status' => $statusTerapi,
                'tanggal_mulai' => $tglMulai->format('Y-m-d'),
                'tanggal_selesai' => $tglSelesai?->format('Y-m-d'),
            ]);
        }

        $this->command->info('  ✓ Terapi dibuat');
    }

    private function buatMonitoring(array $allPatients, User $terapis, Carbon $startDate, Carbon $today): void
    {
        $this->command->info('Membuat monitoring (100 pasien berikutnya)...');
        $dokter = User::where('role', 'dokter')->first();

        for ($i = 300; $i < min(400, count($allPatients)); $i++) {
            $p = $allPatients[$i];
            $tglMulai = $p['tglDaftar']->copy();

            $assessment = MedicalAssessment::create([
                'id_pasien' => $p['patient']->id_pasien,
                'id_pengguna' => $dokter->id,
                'id_antrian' => null,
                'keluhan_utama' => $p['keluhan'],
                'diagnosis' => $p['diagnosis'],
                'riwayat_penyakit' => 'Tidak ada riwayat penyakit kronis sebelumnya',
                'hasil_pemeriksaan' => json_encode(['kondisi_umum' => 'Baik']),
                'rencana_terapi' => $this->rencanaTermapiList[$i % count($this->rencanaTermapiList)],
                'catatan_medis' => 'Assessment sebelum monitoring. Diagnosis: ' . $p['diagnosis'],
                'status' => 'final',
                'tanggal_assessment' => $tglMulai->format('Y-m-d'),
            ]);

            $terapi = Therapy::create([
                'id_assessment' => $assessment->id_assessment,
                'id_pasien' => $p['patient']->id_pasien,
                'id_terapis' => $terapis->id,
                'nama_terapi' => $p['terapi'],
                'deskripsi' => 'Program ' . $p['terapi'] . ' untuk ' . $p['diagnosis'],
                'dosis' => rand(1, 3) . 'x per sesi',
                'durasi_hari' => rand(30, 90),
                'frekuensi_per_minggu' => rand(2, 4),
                'status' => 'berjalan',
                'tanggal_mulai' => $tglMulai->format('Y-m-d'),
                'tanggal_selesai' => null,
            ]);

            $jumlahSesi = rand(2, 5);
            for ($sesi = 0; $sesi < $jumlahSesi; $sesi++) {
                $tglSesi = $tglMulai->copy()->addDays($sesi * rand(2, 4));
                if ($tglSesi->gt($today)) break;

                $waktuMulai = $tglSesi->copy()->setTime(rand(8, 15), rand(0, 59));
                $waktuSelesai = $waktuMulai->copy()->addMinutes(rand(45, 90));

                TherapyMonitoring::create([
                    'id_terapi' => $terapi->id_terapi,
                    'id_pasien' => $p['patient']->id_pasien,
                    'id_terapis' => $terapis->id,
                    'tanggal_sesi' => $tglSesi->format('Y-m-d'),
                    'waktu_mulai' => $waktuMulai->format('H:i:s'),
                    'waktu_selesai' => $waktuSelesai->format('H:i:s'),
                    'kehadiran' => rand(1, 10) > 1 ? 'hadir' : ['tidak_hadir', 'izin'][rand(0, 1)],
                    'catatan_perkembangan' => $this->catatanPerkembangan[$sesi % count($this->catatanPerkembangan)],
                    'kondisi_pasien' => $this->kondisiPasienList[$sesi % count($this->kondisiPasienList)],
                    'rekomendasi' => $this->rekomendasiList[$sesi % count($this->rekomendasiList)],
                    'progress_score' => rand(40, 95),
                ]);
            }
        }

        $this->command->info('  ✓ Monitoring dibuat');
    }

    private function buatKombinasi(array $allPatients, User $dokter, User $terapis, Carbon $startDate, Carbon $today): void
    {
        $this->command->info('Membuat kombinasi lengkap (sisa 100 pasien)...');

        for ($i = 400; $i < count($allPatients); $i++) {
            $p = $allPatients[$i];
            $tglAwal = $p['tglDaftar']->copy();

            $waktuDaftar = $tglAwal->copy()->setTime(rand(7, 10), rand(0, 59));
            $waktuPanggil = $waktuDaftar->copy()->addMinutes(rand(10, 30));
            $waktuSelesai = $waktuDaftar->copy()->addMinutes(rand(50, 100));

            $queue = Queue::create([
                'id_pasien' => $p['patient']->id_pasien,
                'id_pengguna' => $p['user']->id,
                'nomor_antrian' => $i + 1,
                'jenis_layanan' => 'assessment',
                'status' => 'selesai',
                'prioritas' => 0,
                'waktu_daftar' => $waktuDaftar,
                'waktu_panggil' => $waktuPanggil,
                'waktu_selesai' => $waktuSelesai,
                'poli' => $this->poliOptions[$i % count($this->poliOptions)],
                'doctor_id' => $dokter->id,
                'catatan' => 'Kunjungan awal: ' . $p['keluhan'],
            ]);

            $assessment = MedicalAssessment::create([
                'id_pasien' => $p['patient']->id_pasien,
                'id_pengguna' => $dokter->id,
                'id_antrian' => $queue->id_antrian,
                'keluhan_utama' => $p['keluhan'],
                'diagnosis' => $p['diagnosis'],
                'riwayat_penyakit' => 'Tidak ada riwayat penyakit kronis sebelumnya',
                'hasil_pemeriksaan' => json_encode(['berat_badan' => rand(10, 25) . ' kg', 'tinggi_badan' => rand(90, 130) . ' cm']),
                'rencana_terapi' => $this->rencanaTermapiList[$i % count($this->rencanaTermapiList)],
                'catatan_medis' => 'Pasien kooperatif. Diagnosis: ' . $p['diagnosis'],
                'status' => 'final',
                'tanggal_assessment' => $tglAwal->format('Y-m-d'),
            ]);

            $tglMulaiTerapi = $tglAwal->copy()->addDays(rand(1, 3));
            $terapi = Therapy::create([
                'id_assessment' => $assessment->id_assessment,
                'id_pasien' => $p['patient']->id_pasien,
                'id_terapis' => $terapis->id,
                'nama_terapi' => $p['terapi'],
                'deskripsi' => 'Program ' . $p['terapi'] . ' untuk ' . $p['diagnosis'],
                'dosis' => rand(1, 3) . 'x per sesi',
                'durasi_hari' => rand(30, 90),
                'frekuensi_per_minggu' => rand(2, 4),
                'status' => 'berjalan',
                'tanggal_mulai' => $tglMulaiTerapi->format('Y-m-d'),
                'tanggal_selesai' => null,
            ]);

            $jumlahSesi = rand(1, 4);
            for ($sesi = 0; $sesi < $jumlahSesi; $sesi++) {
                $tglSesi = $tglMulaiTerapi->copy()->addDays($sesi * rand(2, 4));
                if ($tglSesi->gt($today)) break;

                $waktuMulaiSesi = $tglSesi->copy()->setTime(rand(8, 15), rand(0, 59));
                $waktuSelesaiSesi = $waktuMulaiSesi->copy()->addMinutes(rand(45, 90));

                TherapyMonitoring::create([
                    'id_terapi' => $terapi->id_terapi,
                    'id_pasien' => $p['patient']->id_pasien,
                    'id_terapis' => $terapis->id,
                    'tanggal_sesi' => $tglSesi->format('Y-m-d'),
                    'waktu_mulai' => $waktuMulaiSesi->format('H:i:s'),
                    'waktu_selesai' => $waktuSelesaiSesi->format('H:i:s'),
                    'kehadiran' => rand(1, 10) > 1 ? 'hadir' : 'izin',
                    'catatan_perkembangan' => $this->catatanPerkembangan[$sesi % count($this->catatanPerkembangan)],
                    'kondisi_pasien' => $this->kondisiPasienList[$sesi % count($this->kondisiPasienList)],
                    'rekomendasi' => $this->rekomendasiList[$sesi % count($this->rekomendasiList)],
                    'progress_score' => rand(50, 95),
                ]);
            }
        }

        $this->command->info('  ✓ Kombinasi lengkap dibuat');
    }

    private function sufiks(int $i): string
    {
        $list = ['Putra', 'Saputra', 'Pratama', 'Nugroho', 'Santoso', 'Wijaya', 'Kusuma', 'Lestari'];
        return $list[$i % count($list)];
    }

    private function generateNik(int $i): string
    {
        $kodeWilayah = ['3204', '3273', '3174', '3578', '3374'][($i - 1) % 5];
        $tahun = 2008 + (($i - 1) % 13);
        $bulan = str_pad((($i - 1) % 12) + 1, 2, '0', STR_PAD_LEFT);
        $hari = str_pad((($i - 1) % 28) + 1, 2, '0', STR_PAD_LEFT);
        $tglLahir = $hari . $bulan . $tahun;
        $seq = str_pad($i, 4, '0', STR_PAD_LEFT);
        return $kodeWilayah . $tglLahir . $seq;
    }
}
