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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

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
                'nik' => [
                    'required',
                    'string',
                    'digits_between:16,20',
                    function ($attribute, $value, $fail) {
                        // Custom validation: Check nik_hash uniqueness
                        $nikHash = hash('sha256', $value);
                        $exists = Patient::where('nik_hash', $nikHash)->exists();
                        if ($exists) {
                            $fail('NIK sudah terdaftar dalam sistem.');
                        }
                    }
                ],
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

            // 2. Map gender ke L/P (sesuai database enum)
            $genderMap = [
                'male' => 'L',
                'female' => 'P',
                'laki-laki' => 'L',
                'l' => 'L',
                'perempuan' => 'P',
                'p' => 'P',
                'Laki-laki' => 'L',
                'Perempuan' => 'P',
            ];
            $genderValue = isset($request->gender) ? ($genderMap[strtolower($request->gender)] ?? $genderMap[$request->gender] ?? null) : null;

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
     * Lupa password pasien — kirim link reset ke email Gmail
     * POST /api/pasien/forgot-password
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ], [
                'email.required' => 'Email harus diisi.',
                'email.email'    => 'Format email tidak valid.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors'  => $validator->errors()
                ], 422);
            }

            $email = $request->input('email');

            // Selalu return sukses agar tidak expose apakah email terdaftar
            $user = User::where('email', $email)->where('role', 'pasien')->first();

            if (!$user) {
                return response()->json([
                    'success' => true,
                    'message' => 'Jika email Anda terdaftar, link reset password telah dikirim.'
                ]);
            }

            // Generate token reset
            $token = Str::random(64);

            // Simpan ke password_reset_tokens
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                [
                    'email'      => $email,
                    'token'      => hash('sha256', $token),
                    'created_at' => now(),
                ]
            );

            // Kirim email (gunakan Mail::raw agar tidak butuh Mailable class)
            $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173'));
            $resetLink   = $frontendUrl . '/pasien/reset-password?token=' . $token . '&email=' . urlencode($email);
            $expireHours = 24;

            Mail::raw(
                "Halo {$user->name},\n\n" .
                "Anda menerima email ini karena ada permintaan reset password untuk akun SITARA Anda.\n\n" .
                "Klik link berikut untuk mereset password Anda:\n{$resetLink}\n\n" .
                "Link ini akan kadaluarsa dalam {$expireHours} jam.\n\n" .
                "Jika Anda tidak meminta reset password, abaikan email ini.\n\n" .
                "Salam,\nTim SITARA",
                function ($message) use ($email, $user) {
                    $message->to($email, $user->name)
                            ->subject('Reset Password Akun SITARA Anda');
                }
            );

            Log::info('Patient forgot password email sent', [
                'email' => $email,
                'name'  => $user->name,
                'ip'    => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Link reset password telah dikirim ke email Anda. Silakan cek inbox atau folder spam.'
            ]);

        } catch (\Exception $e) {
            Log::error('Patient forgot password error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email. Silakan coba lagi atau hubungi administrator.'
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
                ->whereDate('waktu_daftar', today())
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
     * Cek kelengkapan profil pasien
     * GET /api/pasien/profile-status
     */
    public function profileStatus(Request $request): JsonResponse
    {
        try {
            $user    = $request->user();
            $patient = Patient::where('user_id', $user->id)->first();

            if (!$patient) {
                return response()->json([
                    'success'    => true,
                    'is_complete' => false,
                    'missing'    => ['Data pasien tidak ditemukan'],
                    'message'    => 'Profil belum lengkap',
                ]);
            }

            $missing = [];

            if (empty($patient->nik))             $missing[] = 'NIK';
            if (empty($patient->tanggal_lahir))   $missing[] = 'Tanggal Lahir';
            if (empty($patient->jenis_kelamin))   $missing[] = 'Jenis Kelamin';
            if (empty($patient->alamat))          $missing[] = 'Alamat';
            if (empty($patient->nama_wali))       $missing[] = 'Nama Wali / Orang Tua';
            if (empty($patient->no_telepon_wali)) $missing[] = 'No. Telepon Wali';
            if (empty($patient->hubungan_wali))   $missing[] = 'Hubungan Wali';

            $isComplete = empty($missing);

            return response()->json([
                'success'     => true,
                'is_complete' => $isComplete,
                'missing'     => $missing,
                'message'     => $isComplete
                    ? 'Profil sudah lengkap'
                    : 'Harap lengkapi profil terlebih dahulu sebelum melakukan booking.',
            ]);

        } catch (\Exception $e) {
            Log::error('Profile status error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengecek profil'], 500);
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

            // ── Cek kelengkapan profil ──────────────────────────────────────
            $missing = [];
            if (empty($patient->nik))             $missing[] = 'NIK';
            if (empty($patient->tanggal_lahir))   $missing[] = 'Tanggal Lahir';
            if (empty($patient->jenis_kelamin))   $missing[] = 'Jenis Kelamin';
            if (empty($patient->alamat))          $missing[] = 'Alamat';
            if (empty($patient->nama_wali))       $missing[] = 'Nama Wali / Orang Tua';
            if (empty($patient->no_telepon_wali)) $missing[] = 'No. Telepon Wali';
            if (empty($patient->hubungan_wali))   $missing[] = 'Hubungan Wali';

            if (!empty($missing)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil belum lengkap. Harap isi: ' . implode(', ', $missing),
                    'redirect' => '/pasien/profil',
                    'missing'  => $missing,
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'poli'      => 'required|string',
                'doctor_id' => 'nullable|exists:users,id',
                'type'      => 'nullable|in:consultation,assessment,therapy,control',
                'priority'  => 'nullable|in:normal,urgent',
                'notes'     => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
            }

            // Map type frontend → jenis_layanan backend
            $typeMap = [
                'consultation' => 'konsultasi',
                'assessment'   => 'assessment',
                'therapy'      => 'terapi',
                'control'      => 'kontrol',
            ];
            $jenisLayanan = $typeMap[$request->type] ?? 'konsultasi';

            // Cek antrian aktif hari ini
            $existing = \App\Models\Queue::where('id_pasien', $patient->id_pasien)
                ->whereDate('waktu_daftar', today())
                ->whereIn('status', ['menunggu', 'dipanggil'])
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah memiliki antrian aktif hari ini. Nomor: ' . $existing->nomor_antrian,
                ], 409);
            }

            // Generate nomor antrian
            $lastQueue = \App\Models\Queue::whereDate('waktu_daftar', today())->max('nomor_antrian') ?? 0;
            $nomorAntrian = $lastQueue + 1;

            $queue = \App\Models\Queue::create([
                'id_pasien'     => $patient->id_pasien,
                'nomor_antrian' => $nomorAntrian,
                'queue_number'  => 'A' . str_pad($nomorAntrian, 3, '0', STR_PAD_LEFT),
                'jenis_layanan' => $jenisLayanan,
                'poli'          => $request->poli,
                'doctor_id'     => $request->doctor_id,
                'status'        => 'menunggu',
                'priority'      => $request->priority ?? 'normal',
                'prioritas'     => $request->priority === 'urgent' ? 1 : 0,
                'catatan'       => $request->notes,
                'booked_by'     => $user->id,
                'id_pengguna'   => null,
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
     * Daftar dokter & terapis untuk pasien
     * GET /api/pasien/doctors
     */
    public function getDoctors(Request $request): JsonResponse
    {
        $role = $request->query('role'); // optional filter: dokter | terapis

        $query = User::whereIn('role', ['dokter', 'terapis'])
            ->where('status', 'active')
            ->select('id', 'name', 'role', 'nip');

        if ($role && in_array($role, ['dokter', 'terapis'])) {
            $query->where('role', $role);
        }

        $doctors = $query->orderBy('role')->orderBy('name')->get()
            ->map(fn($d) => [
                'id'   => $d->id,
                'name' => $d->name,
                'role' => $d->role,
                'nip'  => $d->nip ?? '-',
            ]);

        return response()->json(['success' => true, 'data' => $doctors]);
    }

    /**
     * Update profile pasien
     * PUT /api/pasien/profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user    = $request->user();
            $patient = Patient::where('user_id', $user->id)->first();

            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pasien tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name'            => 'sometimes|string|max:255',
                'nik'             => [
                    'sometimes', 'string', 'digits_between:16,20',
                    function ($attribute, $value, $fail) use ($patient) {
                        $nikHash = hash('sha256', $value);
                        $exists  = Patient::where('nik_hash', $nikHash)
                            ->where('id_pasien', '!=', $patient->id_pasien)
                            ->exists();
                        if ($exists) {
                            $fail('NIK sudah terdaftar dalam sistem.');
                        }
                    }
                ],
                'date_of_birth'   => 'sometimes|date',
                'gender'          => 'sometimes|in:L,P,male,female,Laki-laki,Perempuan',
                'address'         => 'sometimes|string',
                'parent_name'     => 'sometimes|string|max:255',
                'parent_phone'    => 'sometimes|string|max:20',
                'parent_relation' => 'sometimes|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors'  => $validator->errors()
                ], 422);
            }

            // Update nama di tabel users juga
            if ($request->has('name')) {
                $user->update(['name' => $request->name]);
                $patient->nama_lengkap = $request->name;
            }

            // Map gender ke L/P
            if ($request->has('gender')) {
                $genderMap = ['male' => 'L', 'female' => 'P', 'laki-laki' => 'L', 'perempuan' => 'P', 'l' => 'L', 'p' => 'P'];
                $patient->jenis_kelamin = $genderMap[strtolower($request->gender)] ?? $request->gender;
            }

            if ($request->has('nik'))             $patient->nik             = $request->nik;
            if ($request->has('date_of_birth'))   $patient->tanggal_lahir   = $request->date_of_birth;
            if ($request->has('address'))         $patient->alamat          = $request->address;
            if ($request->has('parent_name'))     $patient->nama_wali       = $request->parent_name;
            if ($request->has('parent_phone'))    $patient->no_telepon_wali = $request->parent_phone;
            if ($request->has('parent_relation')) $patient->hubungan_wali   = $request->parent_relation;

            $patient->save();

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'data'    => [
                    'user'    => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
                    'patient' => [
                        'id_pasien'        => $patient->id_pasien,
                        'nrm'              => $patient->nrm,
                        'nama_lengkap'     => $patient->nama_lengkap,
                        'nik'              => $patient->nik,
                        'tanggal_lahir'    => $patient->tanggal_lahir?->format('Y-m-d'),
                        'jenis_kelamin'    => $patient->jenis_kelamin,
                        'alamat'           => $patient->alamat,
                        'nama_wali'        => $patient->nama_wali,
                        'no_telepon_wali'  => $patient->no_telepon_wali,
                        'hubungan_wali'    => $patient->hubungan_wali,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Patient update profile error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal update profil: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pasien's queues
     * GET /api/pasien/antrian-saya
     */
    public function getMyQueues(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $patient = Patient::where('user_id', $user->id)->first();

            if (!$patient) {
                return response()->json(['success' => false, 'message' => 'Data pasien tidak ditemukan'], 404);
            }

            $queues = \App\Models\Queue::where('id_pasien', $patient->id_pasien)
                ->with('doctor')
                ->orderBy('waktu_daftar', 'desc')
                ->get()
                ->map(function($q) {
                    return [
                        'id' => $q->id_antrian,
                        'nomor_antrian' => $q->queue_number ?? 'Q' . str_pad($q->nomor_antrian, 3, '0', STR_PAD_LEFT),
                        'poli' => $q->poli ?? $q->jenis_layanan ?? '-',
                        'dokter' => $q->doctor ? ['name' => $q->doctor->name] : null,
                        'status' => $q->status,
                        'tanggal' => $q->waktu_daftar,
                        'jenis_layanan' => $q->jenis_layanan,
                    ];
                });

            $activeQueue = $queues->firstWhere('status', 'menunggu') ?: $queues->firstWhere('status', 'dipanggil');
            $history = $queues->filter(function($q) {
                return !in_array($q['status'], ['menunggu', 'dipanggil']);
            })->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'active_queue' => $activeQueue,
                    'history' => $history
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Get my queues error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat antrian'
            ], 500);
        }
    }

    /**
     * GET /api/pasien/riwayat-medis
     */
    public function getMedicalHistory(Request $request): JsonResponse
    {
        try {
            $user    = $request->user();
            $patient = Patient::where('user_id', $user->id)->first();

            if (!$patient) {
                return response()->json(['success' => false, 'message' => 'Data pasien tidak ditemukan'], 404);
            }

            $assessments = \App\Models\MedicalAssessment::where('id_pasien', $patient->id_pasien)
                ->with('user:id,name')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($a) => [
                    'id'                => $a->id_assessment,
                    'tanggal_assessment'=> $a->tanggal_assessment,
                    'diagnosis'         => $a->diagnosis,
                    'icd10_code'        => $a->icd10_code ?? null,
                    'keluhan_utama'     => $a->keluhan_utama,
                    'riwayat_penyakit'  => $a->riwayat_penyakit,
                    'hasil_pemeriksaan' => $a->hasil_pemeriksaan,
                    'rencana_terapi'    => $a->rencana_terapi,
                    'obat_diresepkan'   => $a->obat_diresepkan,
                    'catatan_medis'     => $a->catatan_medis ?? $a->catatan_tambahan,
                    'status'            => $a->status,
                    'dokter'            => $a->user ? ['name' => $a->user->name] : null,
                    'created_at'        => $a->created_at,
                ]);

            $therapies = \App\Models\Therapy::where('id_pasien', $patient->id_pasien)
                ->with('terapis:id,name')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($t) => [
                    'id'          => $t->id_terapi,
                    'jenis_terapi'=> $t->nama_terapi,
                    'status'      => $t->status,
                    'terapis'     => $t->terapis ? ['name' => $t->terapis->name] : null,
                    'total_sesi'  => $t->monitorings()->count(),
                    'sesi_selesai'=> $t->monitorings()->where('kehadiran', 'hadir')->count(),
                    'created_at'  => $t->created_at,
                ]);

            return response()->json([
                'success' => true,
                'data'    => ['assessments' => $assessments, 'therapies' => $therapies],
            ]);

        } catch (\Exception $e) {
            Log::error('getMedicalHistory error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memuat riwayat'], 500);
        }
    }

    /**
     * GET /api/pasien/jadwal-terapi
     */
    public function getTherapySchedule(Request $request): JsonResponse
    {
        try {
            $user    = $request->user();
            $patient = Patient::where('user_id', $user->id)->first();

            if (!$patient) {
                return response()->json(['success' => false, 'message' => 'Data pasien tidak ditemukan'], 404);
            }

            $sessions = \App\Models\TherapyMonitoring::where('id_pasien', $patient->id_pasien)
                ->with([
                    'therapy:id_terapi,nama_terapi',
                    'terapis:id,name',
                ])
                ->orderBy('tanggal_sesi', 'asc')
                ->get()
                ->map(fn($s) => [
                    'id'             => $s->id_monitoring,
                    'tanggal_sesi'   => $s->tanggal_sesi,
                    'waktu_mulai'    => $s->waktu_mulai ? \Carbon\Carbon::parse($s->waktu_mulai)->format('H:i') : null,
                    'waktu_selesai'  => $s->waktu_selesai ? \Carbon\Carbon::parse($s->waktu_selesai)->format('H:i') : null,
                    'jenis_terapi'   => $s->therapy?->nama_terapi ?? 'Sesi Terapi',
                    'terapis'        => $s->terapis ? ['name' => $s->terapis->name] : null,
                    'kehadiran'      => $s->kehadiran,
                    'catatan'        => $s->catatan_perkembangan,
                    'progress_score' => $s->progress_score,
                ]);

            return response()->json([
                'success' => true,
                'data'    => ['schedules' => $sessions],
            ]);

        } catch (\Exception $e) {
            Log::error('getTherapySchedule error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memuat jadwal'], 500);
        }
    }
}