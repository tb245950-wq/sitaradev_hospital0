<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseApiController
{
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);
        
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }
        
        $user = User::where('email', $request->email)->first();
        
        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Email atau password salah', 401);
        }
        
        if ($user->status !== 'active') {
            return $this->forbiddenResponse('Akun Anda tidak aktif.');
        }
        
        if (!in_array($user->role, ['admin', 'dokter', 'terapis'])) {
            return $this->forbiddenResponse('Akses ditolak.');
        }
        
        $token = $user->createToken('auth_token')->plainTextToken;
        $user->update(['last_login_at' => now()]);
        
        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status
            ],
            'token' => $token
        ], 'Login berhasil');
    }
    
    /**
     * Self-registration untuk staff (dokter/terapis).
     * Akun baru otomatis berstatus 'inactive' — menunggu aktivasi Admin.
     * POST /register (public)
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
            'status'   => 'inactive', // Menunggu aktivasi oleh Admin
        ]);

        return $this->successResponse([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ]
        ], 'Pendaftaran berhasil. Akun Anda menunggu aktivasi oleh Admin.', 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return $this->successResponse(null, 'Logout berhasil');
    }
    
    public function user(Request $request): JsonResponse
    {
        return $this->successResponse(['user' => $request->user()]);
    }
}
