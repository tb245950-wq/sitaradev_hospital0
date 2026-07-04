<?php

namespace App\Services;

use App\Models\Patient;
use Illuminate\Support\Facades\Log;

/**
 * KtpVerificationService
 *
 * Internal checker untuk verifikasi KTP pasien.
 *
 * Saat ini melakukan pengecekan dasar (data consistency check).
 * Arsitektur dibuat extensible — nanti bisa ditambah:
 *   - OCR untuk baca teks dari foto KTP
 *   - Face matching antara foto KTP vs foto profil
 *   - Validasi format NIK (kode wilayah, tanggal lahir, dll)
 *   - Integrasi Dukcapil API
 */
class KtpVerificationService
{
    /**
     * Jalankan semua pengecekan terhadap KTP pasien.
     *
     * @return array{
     *   passed: bool,
     *   confidence: float,
     *   checks: array,
     *   reason: string|null
     * }
     */
    public function verify(Patient $patient): array
    {
        $checks     = [];
        $totalScore = 0;
        $maxScore   = 0;

        // ── Check 1: NIK format valid ──────────────────────────────────
        $maxScore += 30;
        $nikCheck   = $this->checkNikFormat($patient->nik);
        $checks[]   = $nikCheck;
        if ($nikCheck['passed']) $totalScore += 30;

        // ── Check 2: NIK tanggal lahir cocok dengan data pasien ────────
        $maxScore += 30;
        $dobCheck   = $this->checkNikMatchesDob($patient->nik, $patient->tanggal_lahir);
        $checks[]   = $dobCheck;
        if ($dobCheck['passed']) $totalScore += 30;

        // ── Check 3: NIK gender cocok dengan data pasien ───────────────
        $maxScore += 20;
        $genderCheck = $this->checkNikMatchesGender($patient->nik, $patient->jenis_kelamin);
        $checks[]    = $genderCheck;
        if ($genderCheck['passed']) $totalScore += 20;

        // ── Check 4: Foto KTP sudah diupload ──────────────────────────
        $maxScore += 20;
        $photoCheck = $this->checkPhotoExists($patient->ktp_photo);
        $checks[]   = $photoCheck;
        if ($photoCheck['passed']) $totalScore += 20;

        // ── Hitung confidence score ────────────────────────────────────
        $confidence = $maxScore > 0 ? round($totalScore / $maxScore, 2) : 0;
        $minConf    = config('upload.ktp_checker.min_confidence', 0.8);
        $passed     = $confidence >= $minConf;

        // ── Tentukan reason jika gagal ────────────────────────────────
        $failedChecks = array_filter($checks, fn($c) => !$c['passed']);
        $reason       = null;
        if (!$passed && !empty($failedChecks)) {
            $reason = implode('; ', array_column(array_values($failedChecks), 'message'));
        }

        Log::info('KTP Verification', [
            'patient_id' => $patient->id_pasien,
            'confidence' => $confidence,
            'passed'     => $passed,
            'checks'     => $checks,
        ]);

        return [
            'passed'     => $passed,
            'confidence' => $confidence,
            'checks'     => $checks,
            'reason'     => $reason,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // Individual Checks
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Validasi format NIK Indonesia (16 digit, kode wilayah 01-99)
     */
    private function checkNikFormat(?string $nik): array
    {
        $name = 'Format NIK';

        if (empty($nik)) {
            return ['name' => $name, 'passed' => false, 'message' => 'NIK belum diisi'];
        }

        if (!preg_match('/^\d{16}$/', $nik)) {
            return ['name' => $name, 'passed' => false, 'message' => 'NIK harus 16 digit angka'];
        }

        // Kode provinsi: 2 digit pertama (11–96 valid Indonesia)
        $prov = (int) substr($nik, 0, 2);
        if ($prov < 11 || $prov > 96) {
            return ['name' => $name, 'passed' => false, 'message' => 'Kode provinsi NIK tidak valid'];
        }

        return ['name' => $name, 'passed' => true, 'message' => 'Format NIK valid'];
    }

    /**
     * Cocokkan tanggal lahir dari NIK dengan tanggal_lahir di data pasien.
     *
     * Format NIK digit 7-12: ddmmyyyy
     * Perempuan: hari + 40
     */
    private function checkNikMatchesDob(?string $nik, mixed $tanggalLahir): array
    {
        $name = 'NIK cocok dengan tanggal lahir';

        if (empty($nik) || empty($tanggalLahir)) {
            return ['name' => $name, 'passed' => false, 'message' => 'NIK atau tanggal lahir belum diisi'];
        }

        if (strlen($nik) !== 16) {
            return ['name' => $name, 'passed' => false, 'message' => 'Format NIK tidak valid'];
        }

        try {
            $dayRaw  = (int) substr($nik, 6, 2);
            $month   = (int) substr($nik, 8, 2);
            $year2   = (int) substr($nik, 10, 2);

            // Perempuan: hari = hari_asli + 40
            $day = $dayRaw > 40 ? $dayRaw - 40 : $dayRaw;

            // Tahun 2 digit → 4 digit
            // Asumsi: 00-29 = 2000-2029, 30-99 = 1930-1999
            $year = $year2 <= 29 ? 2000 + $year2 : 1900 + $year2;

            $nikDate    = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $patientDob = is_string($tanggalLahir)
                ? $tanggalLahir
                : $tanggalLahir->format('Y-m-d');

            if ($nikDate === $patientDob) {
                return ['name' => $name, 'passed' => true, 'message' => 'Tanggal lahir cocok'];
            }

            return [
                'name'    => $name,
                'passed'  => false,
                'message' => "Tanggal lahir di NIK ({$nikDate}) tidak cocok dengan data pasien ({$patientDob})",
            ];

        } catch (\Exception $e) {
            return ['name' => $name, 'passed' => false, 'message' => 'Gagal parse tanggal dari NIK'];
        }
    }

    /**
     * Cocokkan gender dari NIK dengan jenis_kelamin pasien.
     * NIK hari > 40 → perempuan
     */
    private function checkNikMatchesGender(?string $nik, ?string $gender): array
    {
        $name = 'NIK cocok dengan jenis kelamin';

        if (empty($nik) || empty($gender)) {
            return ['name' => $name, 'passed' => false, 'message' => 'NIK atau jenis kelamin belum diisi'];
        }

        if (strlen($nik) !== 16) {
            return ['name' => $name, 'passed' => false, 'message' => 'Format NIK tidak valid'];
        }

        $dayRaw       = (int) substr($nik, 6, 2);
        $nikIsFemale  = $dayRaw > 40;
        $dataIsFemale = strtoupper($gender) === 'P';

        if ($nikIsFemale === $dataIsFemale) {
            return ['name' => $name, 'passed' => true, 'message' => 'Jenis kelamin cocok'];
        }

        return [
            'name'    => $name,
            'passed'  => false,
            'message' => 'Jenis kelamin di NIK tidak cocok dengan data pasien',
        ];
    }

    /**
     * Pastikan file foto KTP sudah diupload
     */
    private function checkPhotoExists(?string $ktpPhoto): array
    {
        $name = 'Foto KTP sudah diupload';

        if (empty($ktpPhoto)) {
            return ['name' => $name, 'passed' => false, 'message' => 'Foto KTP belum diupload'];
        }

        // Cek file benar-benar ada di storage
        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($ktpPhoto)) {
            return ['name' => $name, 'passed' => false, 'message' => 'File foto KTP tidak ditemukan di storage'];
        }

        return ['name' => $name, 'passed' => true, 'message' => 'Foto KTP tersedia'];
    }
}
