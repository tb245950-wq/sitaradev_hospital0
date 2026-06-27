<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PatientAuthController extends Controller
{
    /**
     * Login pasien
     * POST /api/pasien/login
     */
    public function login(Request $request): JsonResponse
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|min:6'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Cari user berdasarkan email
            $user = User::where('email', $request->email)->first();

            // Cek apakah user ada dan password cocok
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau password salah'
                ], 401);
            }

            // Cek apakah role = pasien
            if ($user->role !== 'pasien') {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun ini bukan akun pasien'
                ], 403);
            }

            // Cek status akun
            if ($user->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun tidak aktif'
                ], 403);
            }

            // Buat token
            $token = $user->createToken('patient_token')->plainTextToken;

            // Update last login
            $user->update(['last_login_at' => now()]);

            // Cari data patient berdasarkan user_id
            $patient = Patient::where('user_id', $user->id)->first();

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role
                    ],
                    'patient' => $patient ? [
                        'id_pasien' => $patient->id_pasien,
                        'nrm' => $patient->nrm,
                        'nama_lengkap' => $patient->nama_lengkap,
                        'nik' => $patient->nik,
                        'tanggal_lahir' => $patient->tanggal_lahir?->format('Y-m-d'),
                        'jenis_kelamin' => $patient->jenis_kelamin,
                        'alamat' => $patient->alamat,
                        'nama_wali' => $patient->nama_wali,
                        'no_telepon_wali' => $patient->no_telepon_wali,
                    ] : null,
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Patient login error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register pasien baru
     * POST /api/pasien/register
     */
    public function register(Request $request): JsonResponse
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6|confirmed',
                'nik' => 'required|string|unique:patients,nik',
                'phone' => 'required|string',
                'date_of_birth' => 'nullable|date',
                'gender' => 'nullable|in:male,female',
                'address' => 'nullable|string',
                'parent_name' => 'nullable|string',
                'parent_phone' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 1. Create User
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'pasien',
                'status' => 'active'
            ]);

            // 2. Map gender ke bahasa Indonesia
            $genderMap = [
                'male' => 'Laki-laki',
                'female' => 'Perempuan',
                'laki-laki' => 'Laki-laki',
                'perempuan' => 'Perempuan',
                'Laki-laki' => 'Laki-laki',
                'Perempuan' => 'Perempuan',
            ];
            $genderValue = $genderMap[$request->gender] ?? null;

            if (!$genderValue) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis kelamin tidak valid. Gunakan: male, female, laki-laki, atau perempuan'
                    ], 422);
                }

            // 3. Auto-generate NRM (Nomor Rekam Medis)
            $nrm = 'NRM-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // 4. Create Patient dengan NAMA KOLOM YANG BENAR (sesuai database)
            $patient = Patient::create([
                'user_id' => $user->id,
                'nrm' => 'NRM-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'nik' => $request->nik,
                'nama_lengkap' => $request->name,
                'tanggal_lahir' => $request->date_of_birth,
                'jenis_kelamin' => $genderValue,  // ← Pastikan ini 'Laki-laki' atau 'Perempuan'
                'alamat' => $request->address,
                'nama_wali' => $request->parent_name,
                'no_telepon_wali' => $request->parent_phone ?? $request->phone,
                'hubungan_wali' => 'Orang Tua',
                'riwayat_medis' => null,
            ]);

            // 5. Create Token
            $token = $user->createToken('patient_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role
                    ],
                    'patient' => [
                        'id_pasien' => $patient->id_pasien,
                        'nrm' => $patient->nrm,
                        'nama_lengkap' => $patient->nama_lengkap,
                        'nik' => $patient->nik,
                        'tanggal_lahir' => $patient->tanggal_lahir?->format('Y-m-d'),
                        'jenis_kelamin' => $patient->jenis_kelamin,
                        'alamat' => $patient->alamat,
                        'nama_wali' => $patient->nama_wali,
                        'no_telepon_wali' => $patient->no_telepon_wali,
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Patient registration error: ' . $e->getMessage());
            
            // Rollback user jika patient gagal dibuat
            if (isset($user)) {
                $user->delete();
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout pasien
     * POST /api/pasien/logout
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil'
            ]);
        } catch (\Exception $e) {
            Log::error('Patient logout error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Logout gagal'
            ], 500);
        }
    }

    /**
     * Dashboard pasien
     * GET /api/pasien/dashboard
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $patient = Patient::where('user_id', $user->id)->first();

            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pasien tidak ditemukan'
                ], 404);
            }

            // Hitung statistik
            $assessments = $patient->assessments()->count();
            $therapies = $patient->therapies()->where('status', 'active')->count();
            $queuesToday = $patient->queues()
                ->whereDate('created_at', today())
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'patient' => [
                        'id_pasien' => $patient->id_pasien,
                        'nrm' => $patient->nrm,
                        'nama_lengkap' => $patient->nama_lengkap,
                        'nik' => $patient->nik,
                        'tanggal_lahir' => $patient->tanggal_lahir?->format('Y-m-d'),
                        'jenis_kelamin' => $patient->jenis_kelamin,
                        'alamat' => $patient->alamat,
                        'nama_wali' => $patient->nama_wali,
                        'no_telepon_wali' => $patient->no_telepon_wali,
                        'riwayat_medis' => $patient->riwayat_medis,
                    ],
                    'stats' => [
                        'total_assessments' => $assessments,
                        'active_therapies' => $therapies,
                        'queues_today' => $queuesToday
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Patient dashboard error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat dashboard'
            ], 500);
        }
    }

    /**
     * Profile pasien
     * GET /api/pasien/profile
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $patient = Patient::where('user_id', $user->id)->first();

            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pasien tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'status' => $user->status,
                        'created_at' => $user->created_at,
                    ],
                    'patient' => [
                        'id_pasien' => $patient->id_pasien,
                        'nrm' => $patient->nrm,
                        'nama_lengkap' => $patient->nama_lengkap,
                        'nik' => $patient->nik,
                        'tanggal_lahir' => $patient->tanggal_lahir?->format('Y-m-d'),
                        'jenis_kelamin' => $patient->jenis_kelamin,
                        'alamat' => $patient->alamat,
                        'nama_wali' => $patient->nama_wali,
                        'no_telepon_wali' => $patient->no_telepon_wali,
                        'hubungan_wali' => $patient->hubungan_wali,
                        'riwayat_medis' => $patient->riwayat_medis,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Patient profile error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat profile'
            ], 500);
        }
    }

    /**
     * Booking antrian dari pasien
     * POST /api/pasien/booking
     */
    public function booking(Request $request): JsonResponse
    {
        try {
            $user    = $request->user();
            $patient = Patient::where('user_id', $user->id)->first();

            if (!$patient) {
                return response()->json(['success' => false, 'message' => 'Data pasien tidak ditemukan'], 404);
            }

            $validator = Validator::make($request->all(), [
                'poli'      => 'required|string',
                'doctor_id' => 'nullable|exists:users,id',
                'priority'  => 'nullable|in:normal,urgent',
                'notes'     => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
            }

            // Cek antrian aktif hari ini
            $existing = \App\Models\Queue::where('id_pasien', $patient->id_pasien)
                ->whereDate('created_at', today())
                ->whereIn('status', ['waiting', 'menunggu'])
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah memiliki antrian aktif hari ini. Nomor: ' . $existing->nomor_antrian,
                ], 409);
            }

            // Generate nomor antrian
            $lastQueue = \App\Models\Queue::whereDate('created_at', today())->max('nomor_antrian') ?? 0;
            $nomorAntrian = $lastQueue + 1;

            $queue = \App\Models\Queue::create([
                'id_pasien'     => $patient->id_pasien,
                'nomor_antrian' => $nomorAntrian,
                'queue_number'  => 'A' . str_pad($nomorAntrian, 3, '0', STR_PAD_LEFT),
                'jenis_layanan' => $request->poli,
                'poli'          => $request->poli,
                'doctor_id'     => $request->doctor_id,
                'status'        => 'waiting',
                'priority'      => $request->priority ?? 'normal',
                'prioritas'     => $request->priority === 'urgent' ? 1 : 0,
                'catatan'       => $request->notes,
                'booked_by'     => $user->id,
                'waktu_daftar'  => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Antrian berhasil dibuat',
                'data'    => [
                    'queue_number' => $queue->queue_number ?? 'A' . str_pad($nomorAntrian, 3, '0', STR_PAD_LEFT),
                    'nomor_antrian'=> $nomorAntrian,
                    'poli'         => $queue->poli,
                    'status'       => $queue->status,
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Patient booking error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal membuat antrian: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Daftar dokter untuk pasien
     * GET /api/pasien/doctors
     */
    public function getDoctors(Request $request): JsonResponse
    {
        $doctors = User::where('role', 'dokter')
            ->where('status', 'active')
            ->select('id', 'name', 'nip')
            ->get()
            ->map(fn($d) => ['id' => $d->id, 'name' => $d->name, 'nip' => $d->nip]);

        return response()->json(['success' => true, 'data' => $doctors]);
    }

    /**
     * Update profile pasien
     * PUT /api/pasien/profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $patient = Patient::where('user_id', $user->id)->first();

            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pasien tidak ditemukan'
                ], 404);
            }

            // Validasi
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'phone' => 'sometimes|string',
                'address' => 'sometimes|string',
                'date_of_birth' => 'sometimes|date',
                'parent_name' => 'sometimes|string',
                'parent_phone' => 'sometimes|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update user
            if ($request->has('name')) {
                $user->update(['name' => $request->name]);
                $patient->update(['nama_lengkap' => $request->name]);
            }

            // Update patient
            $updateData = [];
            if ($request->has('address')) $updateData['alamat'] = $request->address;
            if ($request->has('date_of_birth')) $updateData['tanggal_lahir'] = $request->date_of_birth;
            if ($request->has('parent_name')) $updateData['nama_wali'] = $request->parent_name;
            if ($request->has('parent_phone')) $updateData['no_telepon_wali'] = $request->parent_phone;
            
            if (!empty($updateData)) {
                $patient->update($updateData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile berhasil diupdate',
                'data' => [
                    'user' => $user->fresh(),
                    'patient' => $patient->fresh()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Patient update profile error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal update profile'
            ], 500);
        }
    }
}