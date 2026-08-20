<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Patient;
use App\Models\Queue;
use App\Models\MedicalAssessment;
use App\Models\Therapy;
use App\Models\TherapyMonitoring;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class HospitalDummySeeder500 extends Seeder
{
    // Distribusi 500 pasien:
    // - 100 pasien: hanya antrian (berbagai status)
    // - 100 pasien: sedang assessment
    // - 100 pasien: sedang terapi
    // - 100 pasien: sedang monitoring
    // - 100 pasien: kombinasi (antrian + assessment + terapi + monitoring)

    private array $namaLaki = [
        'Ahmad Fauzi', 'Budi Santoso', 'Cahyo Pratama', 'Deni Kurniawan', 'Eko Saputra',
        'Fajar Nugroho', 'Gilang Ramadhan', 'Hendra Wijaya', 'Ivan Setiawan', 'Joko Susilo',
        'Kevin Maulana', 'Lutfi Hakim', 'Muhamad Rizal', 'Nanda Putra', 'Oscar Dermawan',
        'Putra Ardiansyah', 'Qomaruddin Sidiq', 'Rafi Adhitya', 'Sandi Pratama', 'Taufik Hidayat',
        'Umar Faruq', 'Vino Bastian', 'Wahyu Setiawan', 'Yusuf Maulana', 'Zaki Ramadhan',
        'Aldi Firmansyah', 'Bagus Nugroho', 'Candra Kusuma', 'Daffa Akbar', 'Erlangga Putra',
        'Fathur Rahman', 'Galih Prakoso', 'Haikal Aziz', 'Ilham Saputra', 'Joni Pranata',
        'Khairul Anwar', 'Lukman Hakim', 'Mahendra Jaya', 'Naufal Ihsan', 'Omar Salahudin',
        'Prasetyo Budi', 'Rangga Saputra', 'Satria Wibawa', 'Teguh Santoso', 'Udin Sobari',
        'Valdi Setiawan', 'Wildan Maulana', 'Yoga Pratama', 'Zainal Abidin', 'Arya Wiguna',
        'Bagas Prabowo', 'Ciko Pramudya', 'Dimas Aditya', 'Erdian Putra', 'Farel Prayoga',
        'Gifari Azhar', 'Hanif Mustofa', 'Ikhsan Taufik', 'Jefri Andika', 'Kresna Bayu',
        'Luthfi Mubarok', 'Mirza Fathoni', 'Nizar Habibi', 'Okky Setiawan', 'Pandu Wiratama',
        'Qadri Santoso', 'Reza Pahlevi', 'Surya Dharma', 'Tirta Kusuma', 'Ulul Azmi',
        'Vicky Prasetyo', 'Wahid Nugroho', 'Xander Irawan', 'Yogi Hermawan', 'Zahra Putra',
    ];

    private array $namaPerempuan = [
        'Aisyah Putri', 'Bunga Citra', 'Citra Dewi', 'Dina Marlina', 'Eka Sulistyowati',
        'Fitri Handayani', 'Gita Puspita', 'Hani Rahmawati', 'Indah Permatasari', 'Julia Andriani',
        'Kartika Sari', 'Lina Mardiana', 'Maya Sofianti', 'Nina Amalia', 'Okta Vianti',
        'Putri Rahayu', 'Qori Auliana', 'Rina Wulandari', 'Siti Nurhaliza', 'Tina Sumarni',
        'Ulfa Khairunnisa', 'Vera Handayani', 'Winda Lestari', 'Yuli Astuti', 'Zahra Nabila',
        'Aini Rahmah', 'Bella Safitri', 'Cici Amelia', 'Desi Ratnasari', 'Elsa Pertiwi',
        'Fani Oktavia', 'Grace Adinda', 'Hesti Pratiwi', 'Irma Suryani', 'Jeni Kusuma',
        'Kiki Andriani', 'Lala Maharani', 'Mimi Sartika', 'Neni Susanti', 'Opi Rahayu',
        'Puri Wulandari', 'Rere Anggraini', 'Sari Dewi', 'Tari Lestari', 'Uni Handayani',
        'Vivi Septiani', 'Wulan Sari', 'Yaya Anggraeni', 'Zaza Fitriani', 'Ayu Kartika',
        'Bella Novitasari', 'Cantika Permata', 'Dara Puspita', 'Elva Silviana', 'Fira Maulida',
        'Galuh Permatasari', 'Hana Pratiwi', 'Ika Ratnawati', 'Jasmine Putri', 'Kezia Amanda',
        'Laila Nurhidayah', 'Mira Santika', 'Nadia Khairani', 'Olivia Rahayu', 'Pita Handayani',
        'Qonita Azzahra', 'Risa Amelia', 'Silva Agustina', 'Tiara Maharani', 'Umi Kulsum',
        'Vanessa Putri', 'Widya Astuti', 'Xenia Prabawati', 'Yunita Sari', 'Zuhriyah Amini',
    ];

    private array $keluhanList = [
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
        'Anak sulit fokus saat belajar',
        'Gangguan komunikasi non-verbal',
        'Stimming berlebihan pada anak',
        'Masalah interaksi sosial',
        'Sulit mengikuti instruksi',
    ];

    private array $diagnosisList = [
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
        'Developmental Coordination Disorder',
        'Selective Mutism',
        'Anxiety Disorder',
        'Oppositional Defiant Disorder',
        'Social Communication Disorder',
    ];

    private array $namaTermapiList = [
        'Terapi Wicara',
        'Terapi Okupasi',
        'Terapi Perilaku (ABA)',
        'Terapi Sensori Integrasi',
        'Fisioterapi',
        'Terapi Bermain',
        'Terapi Kognitif',
        'Terapi Musik',
        'Terapi Seni',
        'Hidroterapi',
    ];

    private array $alamatList = [
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
        'Jl. Dago No. 72, Bandung',
        'Jl. Setiabudhi No. 101, Bandung',
        'Jl. Buah Batu No. 44, Bandung',
        'Jl. Pasteur No. 37, Bandung',
        'Jl. Soekarno Hatta No. 28, Bandung',
        'Jl. Riau No. 62, Bandung',
        'Jl. Kebon Jati No. 19, Bandung',
        'Jl. Otista No. 83, Bandung',
        'Jl. Sukajadi No. 51, Bandung',
        'Jl. Terusan Buahbatu No. 76, Bandung',
    ];

    private array $namaWaliList = [
        'Sumardi', 'Sumiati', 'Hendra Gunawan', 'Ratna Dewi', 'Agus Pranoto',
        'Dewi Susilowati', 'Bambang Irawan', 'Sari Indah', 'Rudi Hartono', 'Yanti Kusuma',
        'Wahyudi Santoso', 'Nurul Hidayah', 'Darmawan Putra', 'Sri Wahyuni', 'Andi Susanto',
        'Erna Lestari', 'Gunawan Hartawan', 'Yuliana Safitri', 'Bachtiar Rifai', 'Tuti Rahayu',
    ];

    private array $rencanaTermapiList = [
        'Terapi wicara 2x seminggu selama 3 bulan',
        'Terapi okupasi 3x seminggu selama 2 bulan',
        'Terapi ABA intensif 5x seminggu selama 6 bulan',
        'Terapi sensori integrasi 2x seminggu selama 4 bulan',
        'Fisioterapi 3x seminggu selama 1 bulan',
        'Kombinasi terapi wicara dan bermain 3x seminggu',
        'Terapi kognitif 2x seminggu selama 3 bulan',
        'Terapi musik 1x seminggu sebagai pendamping',
        'Hidroterapi 2x seminggu selama 2 bulan',
        'Program home exercise 5x seminggu',
    ];

    private array $catatanPerkembangan = [
        'Pasien menunjukkan kemajuan dalam komunikasi verbal',
        'Kemampuan motorik halus meningkat signifikan',
        'Pasien lebih responsif terhadap instruksi',
        'Konsentrasi membaik dibanding sesi sebelumnya',
        'Masih perlu latihan lebih intensif untuk koordinasi',
        'Pasien aktif dan kooperatif selama sesi',
        'Terjadi peningkatan pada kemampuan sosial',
        'Pasien mampu menyelesaikan tugas dengan bantuan minimal',
        'Kemampuan berbicara mulai berkembang pesat',
        'Pasien menunjukkan antusiasme tinggi dalam sesi',
    ];

    private array $kondisiPasienList = [
        'Membaik', 'Stabil', 'Perlu perhatian lebih', 'Sangat membaik', 'Belum ada perubahan signifikan',
        'Cukup baik', 'Meningkat pesat', 'Butuh evaluasi ulang',
    ];

    private array $rekomendasiList = [
        'Lanjutkan sesi terapi dengan intensitas yang sama',
        'Tingkatkan frekuensi sesi menjadi 3x seminggu',
        'Kurangi intensitas, pasien terlihat kelelahan',
        'Tambahkan latihan mandiri di rumah',
        'Evaluasi ulang program terapi bulan depan',
        'Orang tua perlu dilibatkan lebih aktif dalam sesi',
        'Konsultasi ulang dengan dokter spesialis',
        'Pertahankan program yang sudah berjalan baik',
    ];

    private array $poliOptions = ['Poli Umum', 'Poli Psikolog', 'Poli Terapi', 'Poli Tumbuh Kembang'];

    public function run(): void
    {
        $dokter  = User::where('role', 'dokter')->first();
        $terapis = User::where('role', 'terapis')->first();

        if (!$dokter || !$terapis) {
            $this->command->error('Dokter atau terapis tidak ditemukan! Jalankan: php artisan db:seed dulu.');
            return;
        }

        $startDate = Carbon::create(2026, 6, 25);
        $today     = Carbon::today();

        $this->command->info('=== Membuat 500 data dummy pasien (' . $startDate->format('d M Y') . ' - ' . $today->format('d M Y') . ') ===');
        $this->command->newLine();

        // Kumpulkan semua pasien yang dibuat
        $allPatients = $this->buatPasien(500, $startDate, $today);

        $this->command->info('✓ 500 pasien & user berhasil dibuat.');

        // Distribusi index:
        // [0-99]   = hanya antrian
        // [100-199] = assessment
        // [200-299] = terapi
        // [300-399] = monitoring
        // [400-499] = kombinasi lengkap

        $this->buatAntrian($allPatients, $dokter, $startDate, $today);
        $this->buatAssessment($allPatients, $dokter, $startDate, $today);
        $this->buatTerapi($allPatients, $terapis, $startDate, $today);
        $this->buatMonitoring($allPatients, $terapis, $startDate, $today);
        $this->buatKombinasi($allPatients, $dokter, $terapis, $startDate, $today);

        $this->printRingkasan();
    }

    // =========================================================
    // BUAT 500 PASIEN + USER LOGIN
    // =========================================================
    private function buatPasien(int $total, Carbon $startDate, Carbon $today): array
    {
        $allPatients = [];
        $this->command->info("Membuat {$total} user & pasien...");

        for ($i = 1; $i <= $total; $i++) {
            $isLaki   = ($i % 2 === 1);
            $namaPool = $isLaki ? $this->namaLaki : $this->namaPerempuan;
            $nama     = $namaPool[($i - 1) % count($namaPool)] . ' ' . $this->sufiks($i);
            $jk       = $isLaki ? 'L' : 'P';
            $slug     = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nama));
            $email    = $slug . $i . '@pasien.test';

            // NRM format: NRM-YYYYMMDD-XXXX (tanggal daftar acak antara 25 Juni - hari ini)
            $tglDaftar = $startDate->copy()->addDays(rand(0, $startDate->diffInDays($today)));
            $nrm       = 'NRM-' . $tglDaftar->format('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT);
            $tglLahir  = Carbon::create(rand(2008, 2020), rand(1, 12), rand(1, 28));

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name'     => $nama,
                    'password' => Hash::make('password123'),
                    'role'     => 'pasien',
                    'status'   => 'active',
                ]
            );

            $plainNik = $this->generateNik($i);
            $patient = Patient::updateOrCreate(
                ['nrm' => $nrm],
                [
                    'user_id'         => $user->id,
                    'nik'             => $plainNik,
                    'nik_hash'        => hash('sha256', $plainNik),
                    'nama_lengkap'    => $nama,
                    'nama_panggilan'  => explode(' ', $nama)[0],
                    'tanggal_lahir'   => $tglLahir->format('Y-m-d'),
                    'jenis_kelamin'   => $jk,
                    'alamat'          => $this->alamatList[($i - 1) % count($this->alamatList)],
                    'no_telepon_wali' => '08' . rand(100000000, 999999999),
                    'nama_wali'       => $this->namaWaliList[($i - 1) % count($this->namaWaliList)],
                    'hubungan_wali'   => ['Ayah', 'Ibu', 'Wali'][$i % 3],
                    'riwayat_medis'   => null,
                ]
            );

            $allPatients[] = [
                'user'      => $user,
                'patient'   => $patient,
                'keluhan'   => $this->keluhanList[($i - 1) % count($this->keluhanList)],
                'diagnosis' => $this->diagnosisList[($i - 1) % count($this->diagnosisList)],
                'terapi'    => $this->namaTermapiList[($i - 1) % count($this->namaTermapiList)],
                'tglDaftar' => $tglDaftar,
            ];
        }

        return $allPatients;
    }

    // =========================================================
    // KELOMPOK 1: [0-99] HANYA ANTRIAN - semua status antrian
    // =========================================================
    private function buatAntrian(array $allPatients, User $dokter, Carbon $startDate, Carbon $today): void
    {
        $this->command->info('Membuat antrian (pasien 1-100)...');

        // Status antrian: menunggu, dipanggil, selesai, tidak_hadir
        $statusList = ['menunggu', 'dipanggil', 'selesai', 'tidak_hadir'];

        for ($i = 0; $i < 100; $i++) {
            $p      = $allPatients[$i];
            $status = $statusList[$i % count($statusList)]; // 25 tiap status

            // Tanggal antrian antara 25 Juni - hari ini
            $tglAntrian  = $startDate->copy()->addDays(rand(0, $startDate->diffInDays($today)));
            $waktuDaftar = $tglAntrian->copy()->setTime(rand(7, 11), rand(0, 59));

            $waktuPanggil = in_array($status, ['dipanggil', 'selesai'])
                ? $waktuDaftar->copy()->addMinutes(rand(10, 45))
                : null;

            $waktuSelesai = $status === 'selesai'
                ? $waktuDaftar->copy()->addMinutes(rand(50, 120))
                : null;

            Queue::create([
                'id_pasien'     => $p['patient']->id_pasien,
                'id_pengguna'   => $p['user']->id,
                'nomor_antrian' => $i + 1,
                'jenis_layanan' => $i % 2 === 0 ? 'assessment' : 'terapi',
                'status'        => $status,
                'prioritas'     => $i % 10 === 0 ? 1 : 0,
                'waktu_daftar'  => $waktuDaftar,
                'waktu_panggil' => $waktuPanggil,
                'waktu_selesai' => $waktuSelesai,
                'poli'          => $this->poliOptions[$i % count($this->poliOptions)],
                'doctor_id'     => $dokter->id,
                'catatan'       => 'Kunjungan: ' . $p['keluhan'],
            ]);
        }

        $this->command->info('  ✓ 100 antrian dibuat (25 menunggu, 25 dipanggil, 25 selesai, 25 tidak_hadir)');
    }

    // =========================================================
    // KELOMPOK 2: [100-199] SEDANG ASSESSMENT
    // =========================================================
    private function buatAssessment(array $allPatients, User $dokter, Carbon $startDate, Carbon $today): void
    {
        $this->command->info('Membuat assessment (pasien 101-200)...');

        for ($i = 100; $i < 200; $i++) {
            $p = $allPatients[$i];

            // Buat antrian dulu (jenis_layanan = assessment, status = selesai)
            $tglAntrian  = $p['tglDaftar']->copy()->setTime(rand(7, 11), rand(0, 59));
            $waktuPanggil = $tglAntrian->copy()->addMinutes(rand(10, 30));

            $queue = Queue::create([
                'id_pasien'     => $p['patient']->id_pasien,
                'id_pengguna'   => $p['user']->id,
                'nomor_antrian' => $i + 1,
                'jenis_layanan' => 'assessment',
                'status'        => 'dipanggil',
                'prioritas'     => 0,
                'waktu_daftar'  => $tglAntrian,
                'waktu_panggil' => $waktuPanggil,
                'waktu_selesai' => null,
                'poli'          => $this->poliOptions[$i % count($this->poliOptions)],
                'doctor_id'     => $dokter->id,
                'catatan'       => 'Untuk assessment: ' . $p['keluhan'],
            ]);

            // Status assessment: draft (sedang berjalan) atau final (selesai)
            $statusAssessment = $i % 3 === 0 ? 'draft' : 'final';
            $tglAssessment    = $p['tglDaftar']->copy();

            MedicalAssessment::create([
                'id_pasien'          => $p['patient']->id_pasien,
                'id_pengguna'        => $dokter->id,
                'id_antrian'         => $queue->id_antrian,
                'keluhan_utama'      => $p['keluhan'],
                'diagnosis'          => $p['diagnosis'],
                'riwayat_penyakit'   => 'Tidak ada riwayat penyakit kronis sebelumnya',
                'hasil_pemeriksaan'  => json_encode([
                    'berat_badan'      => rand(10, 25) . ' kg',
                    'tinggi_badan'     => rand(90, 130) . ' cm',
                    'kondisi_umum'     => 'Baik',
                    'catatan_khusus'   => $p['keluhan'],
                ]),
                'rencana_terapi'     => $this->rencanaTermapiList[$i % count($this->rencanaTermapiList)],
                'catatan_medis'      => $statusAssessment === 'final'
                    ? 'Pasien kooperatif. Diagnosis: ' . $p['diagnosis']
                    : null,
                'status'             => $statusAssessment,
                'tanggal_assessment' => $tglAssessment->format('Y-m-d'),
            ]);
        }

        $this->command->info('  ✓ 100 assessment dibuat (33 draft, 67 final)');
    }

    // =========================================================
    // KELOMPOK 3: [200-299] SEDANG TERAPI
    // =========================================================
    private function buatTerapi(array $allPatients, User $terapis, Carbon $startDate, Carbon $today): void
    {
        $this->command->info('Membuat terapi (pasien 201-300)...');

        $dokter = User::where('role', 'dokter')->first();
        $statusTerapiList = ['terjadwal', 'berjalan', 'selesai', 'dihentikan'];

        for ($i = 200; $i < 300; $i++) {
            $p            = $allPatients[$i];
            $statusTerapi = $statusTerapiList[$i % count($statusTerapiList)];
            $tglMulai     = $p['tglDaftar']->copy();

            // Buat assessment dulu (wajib karena id_assessment NOT NULL)
            $assessment = MedicalAssessment::create([
                'id_pasien'          => $p['patient']->id_pasien,
                'id_pengguna'        => $dokter->id,
                'id_antrian'         => null,
                'keluhan_utama'      => $p['keluhan'],
                'diagnosis'          => $p['diagnosis'],
                'riwayat_penyakit'   => 'Tidak ada riwayat penyakit kronis sebelumnya',
                'hasil_pemeriksaan'  => json_encode(['kondisi_umum' => 'Baik']),
                'rencana_terapi'     => $this->rencanaTermapiList[$i % count($this->rencanaTermapiList)],
                'catatan_medis'      => 'Assessment sebelum terapi. Diagnosis: ' . $p['diagnosis'],
                'status'             => 'final',
                'tanggal_assessment' => $tglMulai->format('Y-m-d'),
            ]);

            $tglSelesai = in_array($statusTerapi, ['selesai', 'dihentikan'])
                ? $tglMulai->copy()->addDays(rand(7, 30))
                : null;

            Therapy::create([
                'id_assessment'        => $assessment->id_assessment,
                'id_pasien'            => $p['patient']->id_pasien,
                'id_terapis'           => $terapis->id,
                'nama_terapi'          => $p['terapi'],
                'deskripsi'            => 'Program ' . $p['terapi'] . ' untuk ' . $p['diagnosis'],
                'dosis'                => rand(1, 3) . 'x per sesi',
                'durasi_hari'          => rand(30, 90),
                'frekuensi_per_minggu' => rand(2, 5),
                'status'               => $statusTerapi,
                'tanggal_mulai'        => $tglMulai->format('Y-m-d'),
                'tanggal_selesai'      => $tglSelesai?->format('Y-m-d'),
            ]);
        }

        $this->command->info('  ✓ 100 terapi dibuat (25 terjadwal, 25 berjalan, 25 selesai, 25 dihentikan)');
    }

    // =========================================================
    // KELOMPOK 4: [300-399] SEDANG MONITORING
    // =========================================================
    private function buatMonitoring(array $allPatients, User $terapis, Carbon $startDate, Carbon $today): void
    {
        $this->command->info('Membuat monitoring (pasien 301-400)...');

        for ($i = 300; $i < 400; $i++) {
            $p        = $allPatients[$i];
            $tglMulai = $p['tglDaftar']->copy();

            // Buat assessment dulu (id_assessment NOT NULL)
            $dokter = User::where('role', 'dokter')->first();
            $assessment = MedicalAssessment::create([
                'id_pasien'          => $p['patient']->id_pasien,
                'id_pengguna'        => $dokter->id,
                'id_antrian'         => null,
                'keluhan_utama'      => $p['keluhan'],
                'diagnosis'          => $p['diagnosis'],
                'riwayat_penyakit'   => 'Tidak ada riwayat penyakit kronis sebelumnya',
                'hasil_pemeriksaan'  => json_encode(['kondisi_umum' => 'Baik']),
                'rencana_terapi'     => $this->rencanaTermapiList[$i % count($this->rencanaTermapiList)],
                'catatan_medis'      => 'Assessment sebelum monitoring. Diagnosis: ' . $p['diagnosis'],
                'status'             => 'final',
                'tanggal_assessment' => $tglMulai->format('Y-m-d'),
            ]);

            // Buat terapi
            $terapi = Therapy::create([
                'id_assessment'        => $assessment->id_assessment,
                'id_pasien'            => $p['patient']->id_pasien,
                'id_terapis'           => $terapis->id,
                'nama_terapi'          => $p['terapi'],
                'deskripsi'            => 'Program ' . $p['terapi'] . ' untuk ' . $p['diagnosis'],
                'dosis'                => rand(1, 3) . 'x per sesi',
                'durasi_hari'          => rand(30, 90),
                'frekuensi_per_minggu' => rand(2, 4),
                'status'               => 'berjalan',
                'tanggal_mulai'        => $tglMulai->format('Y-m-d'),
                'tanggal_selesai'      => null,
            ]);

            // Buat 2-5 sesi monitoring
            $jumlahSesi = rand(2, 5);
            for ($sesi = 0; $sesi < $jumlahSesi; $sesi++) {
                $tglSesi    = $tglMulai->copy()->addDays($sesi * rand(2, 4));
                if ($tglSesi->gt($today)) break;

                $waktuMulai   = $tglSesi->copy()->setTime(rand(8, 15), rand(0, 59));
                $waktuSelesai = $waktuMulai->copy()->addMinutes(rand(45, 90));

                TherapyMonitoring::create([
                    'id_terapi'            => $terapi->id_terapi,
                    'id_pasien'            => $p['patient']->id_pasien,
                    'id_terapis'           => $terapis->id,
                    'tanggal_sesi'         => $tglSesi->format('Y-m-d'),
                    'waktu_mulai'          => $waktuMulai->format('H:i:s'),
                    'waktu_selesai'        => $waktuSelesai->format('H:i:s'),
                    'kehadiran'            => rand(1, 10) > 1 ? 'hadir' : ['tidak_hadir', 'izin'][rand(0, 1)],
                    'catatan_perkembangan' => $this->catatanPerkembangan[$sesi % count($this->catatanPerkembangan)],
                    'kondisi_pasien'       => $this->kondisiPasienList[$sesi % count($this->kondisiPasienList)],
                    'rekomendasi'          => $this->rekomendasiList[$sesi % count($this->rekomendasiList)],
                    'progress_score'       => rand(40, 95),
                ]);
            }
        }

        $this->command->info('  ✓ 100 pasien monitoring dibuat (masing-masing 2-5 sesi)');
    }

    // =========================================================
    // KELOMPOK 5: [400-499] KOMBINASI LENGKAP
    // antrian + assessment + terapi + monitoring
    // =========================================================
    private function buatKombinasi(array $allPatients, User $dokter, User $terapis, Carbon $startDate, Carbon $today): void
    {
        $this->command->info('Membuat data kombinasi lengkap (pasien 401-500)...');

        for ($i = 400; $i < 500; $i++) {
            $p        = $allPatients[$i];
            $tglAwal  = $p['tglDaftar']->copy();

            // 1. Antrian (selesai - sudah dipanggil)
            $waktuDaftar  = $tglAwal->copy()->setTime(rand(7, 10), rand(0, 59));
            $waktuPanggil = $waktuDaftar->copy()->addMinutes(rand(10, 30));
            $waktuSelesai = $waktuDaftar->copy()->addMinutes(rand(50, 100));

            $queue = Queue::create([
                'id_pasien'     => $p['patient']->id_pasien,
                'id_pengguna'   => $p['user']->id,
                'nomor_antrian' => $i + 1,
                'jenis_layanan' => 'assessment',
                'status'        => 'selesai',
                'prioritas'     => 0,
                'waktu_daftar'  => $waktuDaftar,
                'waktu_panggil' => $waktuPanggil,
                'waktu_selesai' => $waktuSelesai,
                'poli'          => $this->poliOptions[$i % count($this->poliOptions)],
                'doctor_id'     => $dokter->id,
                'catatan'       => 'Kunjungan awal: ' . $p['keluhan'],
            ]);

            // 2. Assessment (final)
            $assessment = MedicalAssessment::create([
                'id_pasien'          => $p['patient']->id_pasien,
                'id_pengguna'        => $dokter->id,
                'id_antrian'         => $queue->id_antrian,
                'keluhan_utama'      => $p['keluhan'],
                'diagnosis'          => $p['diagnosis'],
                'riwayat_penyakit'   => 'Tidak ada riwayat penyakit kronis sebelumnya',
                'hasil_pemeriksaan'  => json_encode([
                    'berat_badan'    => rand(10, 25) . ' kg',
                    'tinggi_badan'   => rand(90, 130) . ' cm',
                    'kondisi_umum'   => 'Baik',
                ]),
                'rencana_terapi'     => $this->rencanaTermapiList[$i % count($this->rencanaTermapiList)],
                'catatan_medis'      => 'Pasien kooperatif. Diagnosis: ' . $p['diagnosis'],
                'status'             => 'final',
                'tanggal_assessment' => $tglAwal->format('Y-m-d'),
            ]);

            // 3. Terapi (berjalan)
            $tglMulaiTerapi = $tglAwal->copy()->addDays(rand(1, 3));
            $terapi = Therapy::create([
                'id_assessment'        => $assessment->id_assessment,
                'id_pasien'            => $p['patient']->id_pasien,
                'id_terapis'           => $terapis->id,
                'nama_terapi'          => $p['terapi'],
                'deskripsi'            => 'Program ' . $p['terapi'] . ' untuk ' . $p['diagnosis'],
                'dosis'                => rand(1, 3) . 'x per sesi',
                'durasi_hari'          => rand(30, 90),
                'frekuensi_per_minggu' => rand(2, 4),
                'status'               => 'berjalan',
                'tanggal_mulai'        => $tglMulaiTerapi->format('Y-m-d'),
                'tanggal_selesai'      => null,
            ]);

            // 4. Monitoring (beberapa sesi)
            $jumlahSesi = rand(1, 4);
            for ($sesi = 0; $sesi < $jumlahSesi; $sesi++) {
                $tglSesi = $tglMulaiTerapi->copy()->addDays($sesi * rand(2, 4));
                if ($tglSesi->gt($today)) break;

                $waktuMulaiSesi   = $tglSesi->copy()->setTime(rand(8, 15), rand(0, 59));
                $waktuSelesaiSesi = $waktuMulaiSesi->copy()->addMinutes(rand(45, 90));

                TherapyMonitoring::create([
                    'id_terapi'            => $terapi->id_terapi,
                    'id_pasien'            => $p['patient']->id_pasien,
                    'id_terapis'           => $terapis->id,
                    'tanggal_sesi'         => $tglSesi->format('Y-m-d'),
                    'waktu_mulai'          => $waktuMulaiSesi->format('H:i:s'),
                    'waktu_selesai'        => $waktuSelesaiSesi->format('H:i:s'),
                    'kehadiran'            => rand(1, 10) > 1 ? 'hadir' : 'izin',
                    'catatan_perkembangan' => $this->catatanPerkembangan[$sesi % count($this->catatanPerkembangan)],
                    'kondisi_pasien'       => $this->kondisiPasienList[$sesi % count($this->kondisiPasienList)],
                    'rekomendasi'          => $this->rekomendasiList[$sesi % count($this->rekomendasiList)],
                    'progress_score'       => rand(50, 95),
                ]);
            }
        }

        $this->command->info('  ✓ 100 pasien kombinasi lengkap dibuat');
    }

    // =========================================================
    // HELPER METHODS
    // =========================================================
    private function sufiks(int $i): string
    {
        $list = ['Putra', 'Saputra', 'Pratama', 'Nugroho', 'Santoso', 'Wijaya', 'Kusuma', 'Lestari',
                 'Dewi', 'Sari', 'Rahayu', 'Wati', 'Ningrum', 'Permata', 'Indah'];
        return $list[$i % count($list)];
    }

    private function generateNik(int $i): string
    {
        // NIK format: 16 digit — kode wilayah (4) + tgl lahir (8) + seq (4)
        // Gunakan $i sebagai seed agar NIK unik & deterministik
        $kodeWilayah = ['3204', '3273', '3174', '3578', '3374'][($i - 1) % 5];
        $tahun       = 2008 + (($i - 1) % 13); // 2008–2020
        $bulan       = str_pad((($i - 1) % 12) + 1, 2, '0', STR_PAD_LEFT);
        $hari        = str_pad((($i - 1) % 28) + 1, 2, '0', STR_PAD_LEFT);
        $tglLahir    = $hari . $bulan . $tahun;
        $seq         = str_pad($i, 4, '0', STR_PAD_LEFT);
        return $kodeWilayah . $tglLahir . $seq;
    }

    private function printRingkasan(): void
    {
        $this->command->newLine();
        $this->command->info('=== RINGKASAN DATA DUMMY ===');
        $this->command->table(
            ['Data', 'Total'],
            [
                ['Users (pasien)',     User::where('role', 'pasien')->count()],
                ['Patients',           Patient::count()],
                ['Antrian',            Queue::count()],
                ['  - menunggu',       Queue::where('status', 'menunggu')->count()],
                ['  - dipanggil',      Queue::where('status', 'dipanggil')->count()],
                ['  - selesai',        Queue::where('status', 'selesai')->count()],
                ['  - tidak_hadir',    Queue::where('status', 'tidak_hadir')->count()],
                ['Assessment',         MedicalAssessment::count()],
                ['  - draft',          MedicalAssessment::where('status', 'draft')->count()],
                ['  - final',          MedicalAssessment::where('status', 'final')->count()],
                ['Terapi',             Therapy::count()],
                ['  - terjadwal',      Therapy::where('status', 'terjadwal')->count()],
                ['  - berjalan',       Therapy::where('status', 'berjalan')->count()],
                ['  - selesai',        Therapy::where('status', 'selesai')->count()],
                ['  - dihentikan',     Therapy::where('status', 'dihentikan')->count()],
                ['Sesi Monitoring',    TherapyMonitoring::count()],
            ]
        );
        $this->command->newLine();
        $this->command->info('Kredensial login: email = [namapasien][no]@pasien.test | password = password123');
        $this->command->info('Contoh: ahmadfauzipuatera1@pasien.test / password123');
        $this->command->newLine();
        $this->command->info('Staff login:');
        $this->command->info('  Dokter  : mughni@gmail.test / password123');
        $this->command->info('  Terapis : terapis@sitara.test / password123');
        $this->command->info('  Admin   : admin@sitara.test / admin123');
    }
}
