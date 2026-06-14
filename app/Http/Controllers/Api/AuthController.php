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
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang Anda masukkan salah.'],
            ]);
        }

        $user = Auth::user();
        /** @var \App\Models\User $user */
        $token = $user->createToken('sitara-auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data'    => [
                'user'  => $user,
                'token' => $token,
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
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ], 200);
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