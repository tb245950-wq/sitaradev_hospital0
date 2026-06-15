<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * FR-01: Login Pengguna
     */
    public function login(Request $request)
{
    $validated = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    // Cari user berdasarkan email
    $user = User::where('email', $validated['email'])->first();

    // Cek apakah user ada
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Email atau password salah'
        ], 401);
    }

    // Cek password
    if (!Hash::check($validated['password'], $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'Email atau password salah'
        ], 401);
    }

    // CEK STATUS AKUN - RBAC
    if ($user->status === 'inactive') {
        return response()->json([
            'success' => false,
            'message' => 'Akun Anda tidak aktif. Hubungi administrator.'
        ], 403);
    }

    if ($user->status === 'suspended') {
        return response()->json([
            'success' => false,
            'message' => 'Akun Anda ditangguhkan. Hubungi administrator.'
        ], 403);
    }

    // Update last login timestamp
    $user->update([
        'last_login_at' => now()
    ]);

    // Create token untuk API authentication
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Login berhasil',
        'data' => [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                'nip' => $user->nip,
            ],
            'token' => $token
        ]
    ], 200);
}

    /**
     * FR-13: Registrasi User
     */
    public function register(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6|confirmed',
        'role' => 'required|in:dokter,terapis', // ← WAJIB ADA!
    ]);

    // Cegah self-registration sebagai admin
    if ($validated['role'] === 'admin') {
        return response()->json([
            'success' => false,
            'message' => 'Admin tidak dapat mendaftar sendiri. Hubungi administrator.'
        ], 403);
    }

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => bcrypt($validated['password']),
        'role' => $validated['role'], // ← Simpan role dari request
    ]);

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Registrasi berhasil',
        'data' => [
            'user' => $user,
            'token' => $token
        ]
    ], 201);
}

    /**
     * FR-02: Logout Pengguna
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            
            if ($user) {
                // Hapus token yang sedang digunakan
                if ($user->currentAccessToken()) {
                    $user->currentAccessToken()->delete();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true, // Tetap return success agar frontend bisa lanjut clear state
                'message' => 'Logout berhasil (local).',
            ], 200);
        }
    }

    /**
     * Get profil user yang sedang login
     */
    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'data'    => $request->user(),
        ], 200);
    }
}