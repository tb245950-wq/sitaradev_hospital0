<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PatientAuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah'],
            ]);
        }

        // VALIDASI ROLE PASIEN
        if ($user->role !== 'pasien') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Akun ini bukan untuk pasien.'
            ], 403);
        }

        if ($user->status !== 'aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak aktif.'
            ], 403);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('patient_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'user' => [
                    'id' => $user->id,  // ← INI YANG DIPAKAI DI BOOKING
                    'name' => $user->name,
                    'email' => $user->email,
                    'nik' => $user->nik,
                    'phone' => $user->phone,
                    'role' => $user->role,
                ],
                'token' => $token,
            ]
        ], 200);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'nik' => 'required|string|unique:users',
            'phone' => 'required|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'pasien',
            'nik' => $request->nik,
            'phone' => $request->phone,
            'status' => 'aktif',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil.',
            'data' => $user
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.'
        ], 200);
    }
}
