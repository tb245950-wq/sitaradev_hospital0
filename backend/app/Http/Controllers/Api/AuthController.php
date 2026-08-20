<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\LoginHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseApiController
{
    /**
     * Helper: parse browser & OS dari user agent string
     */
    private function parseUserAgent(string $ua): array
    {
        $browser = 'Unknown';
        $os      = 'Unknown';

        // Browser detection
        if (str_contains($ua, 'Firefox'))       $browser = 'Firefox';
        elseif (str_contains($ua, 'Edg'))       $browser = 'Edge';
        elseif (str_contains($ua, 'Chrome'))    $browser = 'Chrome';
        elseif (str_contains($ua, 'Safari'))    $browser = 'Safari';
        elseif (str_contains($ua, 'Opera'))     $browser = 'Opera';
        elseif (str_contains($ua, 'MSIE') || str_contains($ua, 'Trident')) $browser = 'Internet Explorer';

        // OS detection
        if (str_contains($ua, 'Windows NT'))    $os = 'Windows';
        elseif (str_contains($ua, 'Mac OS X'))  $os = 'macOS';
        elseif (str_contains($ua, 'Android'))   $os = 'Android';
        elseif (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) $os = 'iOS';
        elseif (str_contains($ua, 'Linux'))     $os = 'Linux';

        return ['browser' => $browser, 'os' => $os];
    }

    /**
     * Helper: tulis login history
     */
    private function logLogin(Request $request, ?User $user, bool $success, string $failureReason = ''): void
    {
        try {
            $ua     = $request->userAgent() ?? '';
            $parsed = $this->parseUserAgent($ua);

            LoginHistory::create([
                'user_id'        => $user?->id,
                'email'          => $request->input('email'),
                'ip_address'     => $request->ip(),
                'user_agent'     => mb_substr($ua, 0, 500),
                'browser'        => $parsed['browser'],
                'os'             => $parsed['os'],
                'success'        => $success,
                'failure_reason' => $success ? null : $failureReason,
                'login_at'       => now(),
            ]);
        } catch (\Throwable $e) {
            // Jangan biarkan error logging mengganggu response login
            \Log::error('LoginHistory write failed: ' . $e->getMessage());
        }
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = User::where('email', $request->email)->first();

        // Kredensial salah — return 422 agar konsisten dengan test expectations
        if (!$user || !Hash::check($request->password, $user->password)) {
            $this->logLogin($request, $user, false, 'Email atau password salah');
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
                'errors' => ['password' => ['Email atau password salah.']]
            ], 422);
        }

        // Akun tidak aktif
        if ($user->status !== 'active') {
            $this->logLogin($request, $user, false, 'Akun tidak aktif: ' . $user->status);
            return $this->forbiddenResponse('Akun Anda tidak aktif.');
        }

        // Role tidak diizinkan
        if (!in_array($user->role, ['super_admin', 'admin', 'dokter', 'terapis'])) {
            $this->logLogin($request, $user, false, 'Role tidak diizinkan: ' . $user->role);
            return $this->forbiddenResponse('Akses ditolak.');
        }

        // Login berhasil
        $token = $user->createToken('auth_token')->plainTextToken;
        $user->update(['last_login_at' => now()]);
        $this->logLogin($request, $user, true);

        return $this->successResponse([
            'user' => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'role'   => $user->role,
                'status' => $user->status,
            ],
            'token' => $token,
        ], 'Login berhasil');
    }

    /**
     * Self-registration untuk staff (dokter/terapis).
     * Akun baru otomatis berstatus 'inactive' — menunggu aktivasi oleh Admin.
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role'     => 'required|in:dokter,terapis',
            'nip'      => 'nullable|string|max:50|unique:users,nip',
            'phone'    => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password,
            'role'     => $request->role,
            'nip'      => $request->nip,
            'phone'    => $request->phone,
            'status'   => 'inactive',
        ]);

        return $this->successResponse([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ], 'Pendaftaran berhasil. Akun Anda menunggu aktivasi oleh Admin.', 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return $this->successResponse(null, 'Logout berhasil.');
    }

    public function user(Request $request): JsonResponse
    {
        return $this->successResponse(['user' => $request->user()]);
    }
}
