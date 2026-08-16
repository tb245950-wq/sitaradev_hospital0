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

class DummyPatientSeeder extends Seeder
{
    /**
     * Buat 50 data dummy pasien lengkap dengan:
     * - User login (role: pasien)
     * - Data rekam medis
     * - Antrian (booking)
     * - Assessment medis
     * - Terapi & monitoring
     */
    public function run(): void
    {
        $dokter   = User::where('role', 'dokter')->first();
        $terapis  = User::where('role', 'terapis')->first();

        if (!$dokter || !$terapis) {
            $this->command->error('Dokter atau terapis tidak ditemukan! Jalankan DatabaseSeeder dulu.');
            return;
        }

        $namaLaki = [
            'Ahmad Fauzi', 'Budi Santoso', 'Cahyo Pratama', 'Deni Kurniawan', 'Eko Saputra',
            'Fajar Nugroho', 'Gilang Ramadhan', 'Hendra Wijaya', 'Ivan Setiawan', 'Joko Susilo',
            'Kevin Maulana', 'Lutfi Hakim', 'Muhamad Rizal', 'Nanda Putra', 'Oscar Dermawan',
            'Putra Ardiansyah', 'Qomaruddin', 'Rafi Adhitya', 'Sandi Pratama', 'Taufik Hidayat',
            'Umar Sidiq', 'Vino Bastian', 'Wahyu Setiawan', 'Xaverius Aldo', 'Yusuf Maulana',
        ];

        $namaPerempuan = [
            'Aisyah Putri', 'Bunga Citra', 'Citra Dewi', 'Dina Marlina', 'Eka Sulistyowati',
            'Fitri Handayani', 'Gita Puspita', 'Hani Rahmawati', 'Indah Permatasari', 'Julia Andriani',
            'Kartika Sari', 'Lina Mardiana', 'Maya Sofianti', 'Nina Amalia', 'Okta Vianti',
            'Putri Rahayu', 'Qori Auliana', 'Rina Wulandari', 'Siti Nurhaliza', 'Tina Sumarni',
            'Ulfa Khairunnisa', 'Vera Handayani', 'Winda Lestari', 'Xenia Prabawati', 'Yuli Astuti',
        ];

        $namaPanggilan = ['Adi', 'Budi', 'Caca', 'Deni', 'Eko', 'Fajar', 'Gita', 'Hani', 'Ina', 'Joko',
                          'Kevin', 'Lina', 'Maya', 'Nina', 'Okta', 'Putri', 'Rafi', 'Rina', 'Siti', 'Tina',
                          'Umar', 'Vera', 'Wahyu', 'Xena', 'Yuli', 'Ayu', 'Bayu', 'Cici', 'Dani', 'Elsa',
                          'Fani', 'Gani', 'Hesti', 'Irma', 'Jani', 'Kiki', 'Lala', 'Mimi', 'Neni', 'Opi',
                          'Puri', 'Qiqi', 'Rere', 'Sari', 'Tari', 'Uni', 'Vivi', 'Wulan', 'Xena', 'Yaya'];

        $keluhanList = [
            'Anak sulit berbicara dan berkomunikasi',
            'Keterlambatan bicara pada anak',
            'Gangguan konsentrasi dan hiperaktif',
            'Anak sering tantrum berlebihan',
            'Keterlambatan perkembangan motorik',
            'Gangguan sensori pada anak',
            'Kesulitan belajar di sekolah',
            'Anak tidak mau bersosialisasi',
            'Keterlambatan perkembangan kognitif',
            'Gangguan perilaku pada anak',
            'Anak sulit tidur dan gelisah',
            'Kesulitan menulis dan membaca',
            'Gangguan koordinasi motorik halus',
            'Anak mudah frustrasi dan marah',
            'Keterlambatan bicara ekspresif',
        ];

        $diagnosisList = [
            'Autism Spectrum Disorder (ASD)',
            'Speech Delay',
            'ADHD (Attention Deficit Hyperactivity Disorder)',
            'Global Development Delay',
            'Sensory Processing Disorder',
            'Intellectual Disability',
            'Dyslexia',
            'Cerebral Palsy Ringan',
            'Down Syndrome',
            'Language Disorder',
        ];

        $namaTermapi = [
            'Terapi Wicara',
            'Terapi Okupasi',
            'Terapi Perilaku (ABA)',
            'Terapi Sensori Integrasi',
            'Fisioterapi',
            'Terapi Bermain',
            'Terapi Kognitif',
        ];

        $alamatList = [
            'Jl. Merdeka No. 12, Bandung',
            'Jl. Sudirman No. 45, Jakarta',
            'Jl. Diponegoro No. 8, Surabaya',
            'Jl. Pahlawan No. 23, Yogyakarta',
            'Jl. Ahmad Yani No. 67, Semarang',
            'Jl. Gatot Subroto No. 15, Bandung',
            'Jl. Pemuda No. 33, Depok',
            'Jl. Raya Bogor No. 99, Bogor',
            'Jl. Cihampelas No. 55, Bandung',
            'Jl. Braga No. 18, Bandung',
        ];

        $namaWaliList = [
            'Bapak Sumardi', 'Ibu Sumiati', 'Bapak Hendra', 'Ibu Ratna', 'Bapak Agus',
            'Ibu Dewi', 'Bapak Bambang', 'Ibu Sari', 'Bapak Rudi', 'Ibu Yanti',
        ];

        $now   = Carbon::now();
        $today = Carbon::today();

        $this->command->info('Membuat 50 pasien dummy...');

        $allPatients = [];

        for ($i = 1; $i <= 50; $i++) {
            $isLaki     = ($i % 2 === 1); // ganjil = laki-laki
            $namaList   = $isLaki ? $namaLaki : $namaPerempuan;
            $namaIdx    = ($i - 1) % count($namaList);
            $nama       = $namaList[$namaIdx];
            $jk         = $isLaki ? 'L' : 'P';
            $emailSlug  = strtolower(str_replace([' ', '.', "'"], ['', '', ''], $nama));
            $email      = $emailSlug . $i . '@pasien.test';
            $tglLahir   = Carbon::create(rand(2005, 2018), rand(1, 12), rand(1, 28));
            $nrm        = 'NRM-' . $today->format('Ymd') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);

            // Buat User
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name'     => $nama,
                    'password' => Hash::make('password123'),
                    'role'     => 'pasien',
                    'status'   => 'active',
                ]
            );

            // Buat Patient
            $patient = Patient::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nrm'              => $nrm,
                    'nik'              => '32' . str_pad($i, 14, rand(0, 9), STR_PAD_LEFT),
                    'nama_lengkap'     => $nama,
                    'nama_panggilan'   => $namaPanggilan[($i - 1) % count($namaPanggilan)],
                    'tanggal_lahir'    => $tglLahir->format('Y-m-d'),
                    'jenis_kelamin'    => $jk,
                    'alamat'           => $alamatList[($i - 1) % count($alamatList)],
                    'no_telepon_wali'  => '08' . rand(1000000000, 9999999999),
                    'nama_wali'        => $namaWaliList[($i - 1) % count($namaWaliList)],
                    'hubungan_wali'    => ($i % 3 === 0) ? 'Ibu' : (($i % 3 === 1) ? 'Ayah' : 'Wali'),
                    'riwayat_medis'    => null,
                ]
            );

            $allPatients[] = [
                'user'    => $user,
                'patient' => $patient,
                'keluhan' => $keluhanList[($i - 1) % count($keluhanList)],
                'diagnosis' => $diagnosisList[($i - 1) % count($diagnosisList)],
                'terapi'  => $namaTermapi[($i - 1) % count($namaTermapi)],
            ];
        }

        $this->command->info('50 pasien berhasil dibuat.');
        $this->command->info('Membuat antrian...');

        // ============================================================
        // ANTRIAN
        // Distribusi:
        // - 15 pasien: antrian HARI INI (status: menunggu / dipanggil)
        // - 10 pasien: antrian kemarin (selesai)
        // - 10 pasien: antrian minggu lalu (selesai)
        // - 15 pasien sisanya: belum booking
        // ============================================================

        $poliOptions = ['Poli Umum', 'Poli Psikolog', 'Poli Terapi', 'Poli Tumbuh Kembang'];
        $layananOptions = ['konsultasi', 'terapi', 'assessment', 'kontrol'];

        // 15 antrian hari ini
        for ($i = 0; $i < 15; $i++) {
            $p       = $allPatients[$i];
            $status  = ($i < 5) ? 'dipanggil' : (($i < 10) ? 'menunggu' : 'selesai');
            $waktuDaftar = $today->copy()->addHours(rand(7, 11))->addMinutes(rand(0, 59));

            Queue::updateOrCreate(
                ['id_pasien' => $p['patient']->id_pasien, 'waktu_daftar' => $waktuDaftar],
                [
                    'id_pengguna'    => $p['user']->id,
                    'nomor_antrian'  => $i + 1,
                    'jenis_layanan'  => $layananOptions[$i % count($layananOptions)],
                    'status'         => $status,
                    'prioritas'      => ($i % 5 === 0) ? 1 : 0,
                    'waktu_daftar'   => $waktuDaftar,
                    'waktu_panggil'  => ($status !== 'menunggu') ? $waktuDaftar->copy()->addMinutes(rand(10, 30)) : null,
                    'waktu_selesai'  => ($status === 'selesai') ? $waktuDaftar->copy()->addMinutes(rand(40, 90)) : null,
                    'poli'           => $poliOptions[$i % count($poliOptions)],
                    'doctor_id'      => $dokter->id,
                    'catatan'        => 'Kunjungan rutin - ' . $p['keluhan'],
                    'priority'       => ($i % 5 === 0) ? 'tinggi' : 'normal',
                    'queue_number'   => 'A' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                ]
            );
        }

        // 10 antrian kemarin (selesai)
        for ($i = 15; $i < 25; $i++) {
            $p           = $allPatients[$i];
            $waktuDaftar = $today->copy()->subDay()->addHours(rand(8, 15))->addMinutes(rand(0, 59));

            Queue::updateOrCreate(
                ['id_pasien' => $p['patient']->id_pasien, 'waktu_daftar' => $waktuDaftar],
                [
                    'id_pengguna'    => $p['user']->id,
                    'nomor_antrian'  => $i + 1,
                    'jenis_layanan'  => $layananOptions[$i % count($layananOptions)],
                    'status'         => 'selesai',
                    'prioritas'      => 0,
                    'waktu_daftar'   => $waktuDaftar,
                    'waktu_panggil'  => $waktuDaftar->copy()->addMinutes(rand(10, 25)),
                    'waktu_selesai'  => $waktuDaftar->copy()->addMinutes(rand(40, 80)),
                    'poli'           => $poliOptions[$i % count($poliOptions)],
                    'doctor_id'      => $dokter->id,
                    'catatan'        => 'Kunjungan kemarin',
                    'priority'       => 'normal',
                    'queue_number'   => 'B' . str_pad($i - 14, 3, '0', STR_PAD_LEFT),
                ]
            );
        }

        // 10 antrian minggu lalu (selesai)
        for ($i = 25; $i < 35; $i++) {
            $p           = $allPatients[$i];
            $waktuDaftar = $today->copy()->subDays(rand(3, 7))->addHours(rand(8, 15))->addMinutes(rand(0, 59));

            Queue::updateOrCreate(
                ['id_pasien' => $p['patient']->id_pasien, 'waktu_daftar' => $waktuDaftar],
                [
                    'id_pengguna'    => $p['user']->id,
                    'nomor_antrian'  => $i + 1,
                    'jenis_layanan'  => $layananOptions[$i % count($layananOptions)],
                    'status'         => 'selesai',
                    'prioritas'      => 0,
                    'waktu_daftar'   => $waktuDaftar,
                    'waktu_panggil'  => $waktuDaftar->copy()->addMinutes(rand(10, 25)),
                    'waktu_selesai'  => $waktuDaftar->copy()->addMinutes(rand(40, 80)),
                    'poli'           => $poliOptions[$i % count($poliOptions)],
                    'doctor_id'      => $dokter->id,
                    'catatan'        => 'Kunjungan minggu lalu',
                    'priority'       => 'normal',
                    'queue_number'   => 'C' . str_pad($i - 24, 3, '0', STR_PAD_LEFT),
                ]
            );
        }

        $this->command->info('Antrian berhasil dibuat.');
        $this->command->info('Membuat assessment...');

        // ============================================================
        // ASSESSMENT
        // - 20 pasien pertama punya assessment
        // - 10 status: draft | 5 status: submitted | 5 status: completed
        // ============================================================

        $statusAssessment = ['draft', 'draft', 'draft', 'draft', 'draft', 'final', 'final', 'final', 'final', 'final'];
        $rencanaTermapi = [
            'Terapi wicara 2x seminggu selama 3 bulan',
            'Terapi okupasi 3x seminggu selama 2 bulan',
            'Terapi ABA intensif 5x seminggu',
            'Terapi sensori integrasi 2x seminggu',
            'Fisioterapi 3x seminggu selama 1 bulan',
            'Kombinasi terapi wicara dan bermain',
            'Terapi kognitif 2x seminggu',
        ];

        $assessmentMap = []; // simpan id_assessment per pasien untuk terapi

        for ($i = 0; $i < 30; $i++) {
            $p      = $allPatients[$i];
            $status = $statusAssessment[$i % count($statusAssessment)];
            $tglAssessment = $today->copy()->subDays(rand(0, 14));

            // Ambil antrian jika ada
            $antrian = Queue::where('id_pasien', $p['patient']->id_pasien)->first();

            $assessment = MedicalAssessment::create([
                'id_pasien'           => $p['patient']->id_pasien,
                'id_pengguna'         => $dokter->id,
                'id_antrian'          => $antrian?->id_antrian,
                'keluhan_utama'       => $p['keluhan'],
                'diagnosis'           => $p['diagnosis'],
                'riwayat_penyakit'    => 'Tidak ada riwayat penyakit kronis',
                'hasil_pemeriksaan'   => 'Pemeriksaan fisik normal. ' . $p['keluhan'] . '. Perlu evaluasi lebih lanjut.',
                'rencana_terapi'      => $rencanaTermapi[$i % count($rencanaTermapi)],
                'catatan_medis'       => ($status !== 'draft') ? 'Pasien kooperatif selama pemeriksaan.' : null,
                'obat_diresepkan'     => ($status === 'final') ? 'Vitamin B Complex 1x1, Omega-3 1x1' : null,
                'catatan_tambahan'    => ($status === 'final') ? 'Orang tua diberikan edukasi mengenai kondisi anak.' : null,
                'status'              => $status,
                'tanggal_assessment'  => $tglAssessment->format('Y-m-d'),
            ]);

            $assessmentMap[$p['patient']->id_pasien] = $assessment->id_assessment;
        }

        $this->command->info('Assessment berhasil dibuat.');
        $this->command->info('Membuat terapi & monitoring...');

        // ============================================================
        // TERAPI & MONITORING
        // - 20 pasien (dari yang punya assessment completed/submitted)
        // - Status terapi: aktif, selesai, dijadwalkan
        // ============================================================

        $statusTerapi  = ['berjalan', 'berjalan', 'berjalan', 'berjalan', 'selesai', 'selesai', 'terjadwal', 'terjadwal'];
        $kondisiPasien = ['Membaik', 'Stabil', 'Perlu perhatian lebih', 'Sangat membaik', 'Belum ada perubahan signifikan'];
        $rekomendasiList = [
            'Lanjutkan sesi terapi dengan intensitas yang sama',
            'Tingkatkan frekuensi sesi menjadi 3x seminggu',
            'Kurangi intensitas, pasien terlihat kelelahan',
            'Tambahkan latihan mandiri di rumah',
            'Evaluasi ulang program terapi bulan depan',
            'Orang tua perlu dilibatkan lebih aktif dalam sesi',
        ];

        $catatanPerkembangan = [
            'Pasien menunjukkan kemajuan dalam komunikasi verbal',
            'Kemampuan motorik halus meningkat signifikan',
            'Pasien lebih responsif terhadap instruksi',
            'Konsentrasi membaik dibanding sesi sebelumnya',
            'Masih perlu latihan lebih intensif untuk koordinasi',
            'Pasien aktif dan kooperatif selama sesi',
            'Terjadi peningkatan pada kemampuan sosial',
            'Pasien mampu menyelesaikan tugas dengan bantuan minimal',
        ];

        for ($i = 0; $i < 25; $i++) {
            $p      = $allPatients[$i];
            $status = $statusTerapi[$i % count($statusTerapi)];
            $idAssessment = $assessmentMap[$p['patient']->id_pasien] ?? null;

            $tglMulai   = $today->copy()->subDays(rand(7, 30));
            $tglSelesai = ($status === 'selesai') ? $tglMulai->copy()->addDays(rand(14, 30)) : null;

            $terapi = Therapy::create([
                'id_assessment'       => $idAssessment,
                'id_pasien'           => $p['patient']->id_pasien,
                'id_terapis'          => $terapis->id,
                'nama_terapi'         => $p['terapi'],
                'deskripsi'           => 'Program ' . $p['terapi'] . ' untuk menangani ' . $p['diagnosis'],
                'dosis'               => rand(1, 3) . 'x per sesi',
                'durasi_hari'         => rand(30, 90),
                'frekuensi_per_minggu' => rand(2, 5),
                'status'              => $status,
                'tanggal_mulai'       => $tglMulai->format('Y-m-d'),
                'tanggal_selesai'     => $tglSelesai?->format('Y-m-d'),
            ]);

            // Buat sesi monitoring untuk terapi yang berjalan/selesai
            if (in_array($status, ['berjalan', 'selesai'])) {
                $jumlahSesi = ($status === 'selesai') ? rand(6, 12) : rand(2, 5);

                for ($sesi = 0; $sesi < $jumlahSesi; $sesi++) {
                    $tglSesi    = $tglMulai->copy()->addDays($sesi * 3 + rand(0, 2));
                    $waktuMulai = $tglSesi->copy()->setTime(rand(8, 15), rand(0, 59));
                    $waktuSelesai = $waktuMulai->copy()->addMinutes(rand(45, 90));

                    // Jangan buat sesi di masa depan untuk terapi berjalan
                    if ($status === 'berjalan' && $tglSesi->gt($today)) {
                        break;
                    }

                    TherapyMonitoring::create([
                        'id_terapi'            => $terapi->id_terapi,
                        'id_pasien'            => $p['patient']->id_pasien,
                        'id_terapis'           => $terapis->id,
                        'tanggal_sesi'         => $tglSesi->format('Y-m-d'),
                        'waktu_mulai'          => $waktuMulai->format('H:i:s'),
                        'waktu_selesai'        => $waktuSelesai->format('H:i:s'),
                        'kehadiran'            => (rand(1, 10) > 1) ? 'hadir' : 'tidak_hadir',
                        'catatan_perkembangan' => $catatanPerkembangan[$sesi % count($catatanPerkembangan)],
                        'kondisi_pasien'       => $kondisiPasien[$sesi % count($kondisiPasien)],
                        'rekomendasi'          => $rekomendasiList[$sesi % count($rekomendasiList)],
                        'progress_score'       => rand(40, 95),
                    ]);
                }
            }
        }

        $this->command->info('Terapi & monitoring berhasil dibuat.');
        $this->command->newLine();
        $this->command->info('=== RINGKASAN DATA DUMMY ===');
        $this->command->table(
            ['Model', 'Total'],
            [
                ['Users (pasien)', User::where('role', 'pasien')->count()],
                ['Patients', Patient::count()],
                ['Antrian', Queue::count()],
                ['Assessment', MedicalAssessment::count()],
                ['Terapi', Therapy::count()],
                ['Monitoring Sesi', TherapyMonitoring::count()],
            ]
        );
        $this->command->newLine();
        $this->command->info('Kredensial login pasien: email = [nama][no]@pasien.test | password = password123');
        $this->command->info('Contoh: ahmadafauzi1@pasien.test / password123');
    }
}
