<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Services\KtpVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PatientUploadController extends Controller
{
    public function __construct(
        private readonly KtpVerificationService $ktpVerifier
    ) {}

    // ──────────────────────────────────────────────────────────────────────
    // Upload Foto KTP
    // POST /api/pasien/upload/ktp
    // ──────────────────────────────────────────────────────────────────────

    public function uploadKtp(Request $request): JsonResponse
    {
        $patient = $this->getPatient($request);
        if ($patient instanceof JsonResponse) return $patient;

        // Ambil aturan dari config
        $cfg = config('upload.ktp');

        $request->validate([
            'ktp_photo' => [
                'required',
                'file',
                'mimes:' . $cfg['extensions'],
                'max:' . $cfg['max_size'],
            ],
        ], [
            'ktp_photo.required' => 'File foto KTP wajib diupload.',
            'ktp_photo.mimes'    => 'Format file harus JPG, PNG, atau WebP.',
            'ktp_photo.max'      => 'Ukuran file maksimal ' . ($cfg['max_size'] / 1024) . ' MB.',
        ]);

        try {
            // Hapus foto KTP lama jika ada
            if ($patient->ktp_photo) {
                Storage::disk($cfg['disk'])->delete($patient->ktp_photo);
            }

            // Simpan file baru dengan nama unik
            $file     = $request->file('ktp_photo');
            $filename = 'ktp_' . $patient->id_pasien . '_' . Str::random(12) . '.' . $file->getClientOriginalExtension();
            $path     = $file->storeAs($cfg['path'], $filename, $cfg['disk']);

            // Update DB: foto tersimpan, status jadi pending
            $patient->update([
                'ktp_photo'  => $path,
                'ktp_status' => 'pending',
                'ktp_rejected_reason' => null,
            ]);

            // Jalankan internal checker jika diaktifkan
            $verificationResult = null;
            if (config('upload.ktp_checker.enabled', true)) {
                $patient->refresh(); // pastikan data terbaru
                $verificationResult = $this->ktpVerifier->verify($patient);

                if ($verificationResult['passed'] && config('upload.ktp_checker.auto_approve', true)) {
                    $patient->update([
                        'ktp_status'      => 'verified',
                        'ktp_verified_at' => now(),
                        'ktp_rejected_reason' => null,
                    ]);
                } elseif (!$verificationResult['passed'] && config('upload.ktp_checker.auto_reject', false)) {
                    $patient->update([
                        'ktp_status'          => 'rejected',
                        'ktp_rejected_reason' => $verificationResult['reason'],
                    ]);
                }
            }

            $patient->refresh();

            return response()->json([
                'success' => true,
                'message' => $this->statusMessage($patient->ktp_status),
                'data'    => [
                    'ktp_status'       => $patient->ktp_status,
                    'ktp_verified_at'  => $patient->ktp_verified_at,
                    'verification'     => $verificationResult ? [
                        'passed'     => $verificationResult['passed'],
                        'confidence' => $verificationResult['confidence'],
                        'checks'     => $verificationResult['checks'],
                    ] : null,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('KTP upload error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengupload foto KTP.'], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    // Upload Foto Profil
    // POST /api/pasien/upload/avatar
    // ──────────────────────────────────────────────────────────────────────

    public function uploadAvatar(Request $request): JsonResponse
    {
        $patient = $this->getPatient($request);
        if ($patient instanceof JsonResponse) return $patient;

        $cfg = config('upload.avatar');

        $request->validate([
            'avatar' => [
                'required',
                'file',
                'mimes:' . $cfg['extensions'],
                'max:' . $cfg['max_size'],
            ],
        ], [
            'avatar.required' => 'File foto profil wajib diupload.',
            'avatar.mimes'    => 'Format file harus JPG, PNG, WebP, atau GIF.',
            'avatar.max'      => 'Ukuran file maksimal ' . ($cfg['max_size'] / 1024) . ' MB.',
        ]);

        try {
            // Hapus foto lama jika ada
            if ($patient->profile_photo) {
                Storage::disk($cfg['disk'])->delete($patient->profile_photo);
            }

            $file     = $request->file('avatar');
            $filename = 'avatar_' . $patient->id_pasien . '_' . Str::random(12) . '.' . $file->getClientOriginalExtension();
            $path     = $file->storeAs($cfg['path'], $filename, $cfg['disk']);

            $patient->update(['profile_photo' => $path]);

            // URL publik foto profil
            $url = Storage::disk($cfg['disk'])->url($path);

            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil diupload.',
                'data'    => [
                    'profile_photo_url' => $url,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Avatar upload error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengupload foto profil.'], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    // Cek Status Verifikasi KTP
    // GET /api/pasien/ktp-status
    // ──────────────────────────────────────────────────────────────────────

    public function ktpStatus(Request $request): JsonResponse
    {
        $patient = $this->getPatient($request);
        if ($patient instanceof JsonResponse) return $patient;

        return response()->json([
            'success' => true,
            'data'    => [
                'ktp_status'          => $patient->ktp_status ?? 'none',
                'ktp_verified_at'     => $patient->ktp_verified_at,
                'ktp_rejected_reason' => $patient->ktp_rejected_reason,
                'has_ktp_photo'       => !empty($patient->ktp_photo),
                'has_profile_photo'   => !empty($patient->profile_photo),
                'profile_photo_url'   => $patient->profile_photo
                    ? Storage::disk('public')->url($patient->profile_photo)
                    : null,
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────

    private function getPatient(Request $request): Patient|JsonResponse
    {
        $patient = Patient::where('user_id', $request->user()->id)->first();

        if (!$patient) {
            return response()->json(['success' => false, 'message' => 'Data pasien tidak ditemukan.'], 404);
        }

        return $patient;
    }

    private function statusMessage(string $status): string
    {
        return match ($status) {
            'verified' => 'KTP berhasil diverifikasi! NIK Anda sudah terverifikasi.',
            'rejected' => 'KTP ditolak. Silakan periksa data Anda dan upload ulang.',
            'pending'  => 'Foto KTP berhasil diupload dan sedang dalam proses verifikasi.',
            default    => 'Foto KTP berhasil diupload.',
        };
    }
}
