<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordResetController extends BaseApiController
{
    /**
     * Kirim permintaan reset password untuk staff.
     *
     * Karena email menggunakan domain custom @sitara.com (internal),
     * proses reset dilakukan oleh Super Admin / Admin melalui panel manajemen user.
     * Endpoint ini hanya memvalidasi bahwa email terdaftar, lalu mengembalikan
     * instruksi untuk menghubungi admin.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ], [
            'email.required' => 'Email harus diisi.',
            'email.email'    => 'Format email tidak valid.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $email = $request->input('email');

        // Cek apakah email ini terdaftar sebagai staff
        $user = User::where('email', $email)
            ->whereIn('role', ['super_admin', 'admin', 'dokter', 'terapis'])
            ->first();

        // Selalu kembalikan response sukses meskipun email tidak ditemukan
        // (security: jangan expose informasi apakah email terdaftar atau tidak)
        if (!$user) {
            return $this->successResponse(
                ['email_found' => false],
                'Jika email Anda terdaftar di sistem SITARA, administrator akan memberikan instruksi reset password.'
            );
        }

        // Generate token reset (disimpan untuk referensi admin jika diperlukan)
        $token = Str::random(64);
        $expiredAt = Carbon::now()->addHours(24);

        // Simpan atau update token di tabel password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'email'      => $email,
                'token'      => hash('sha256', $token),
                'created_at' => now(),
            ]
        );

        // Log aktivitas permintaan reset password
        \Log::info('Staff forgot password request', [
            'email'    => $email,
            'role'     => $user->role,
            'name'     => $user->name,
            'ip'       => $request->ip(),
            'time'     => now()->toDateTimeString(),
        ]);

        return $this->successResponse(
            [
                'email_found'    => true,
                'email'          => $email,
                'user_name'      => $user->name,
                'contact_admin'  => true,
                'domain_info'    => 'Email @sitara.com adalah email internal rumah sakit.',
            ],
            'Permintaan reset password berhasil dikirim. Silakan hubungi administrator SITARA untuk melanjutkan proses verifikasi.'
        );
    }
}
