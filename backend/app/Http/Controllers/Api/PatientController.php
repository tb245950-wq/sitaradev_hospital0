<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    /**
     * FR-04: Pencarian Data Pasien & List Pasien
     * Mendukung pencarian berdasarkan Nama, NRM, atau NIK (NFR-03: < 2 detik)
     */
    public function index(Request $request)
    {
        $query = Patient::query();

        // Logika Pencarian (Search)
        // FIXED: Hapus search by NIK karena field terenkripsi (tidak bisa di-LIKE query)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nrm', 'like', "%{$search}%");
                // Removed: ->orWhere('nik', 'like', "%{$search}%")
                // NIK menggunakan EncryptedField cast, tidak bisa dicari dengan LIKE
            });
        }

        // Pagination 15 data per halaman untuk performa
        // Urutkan berdasarkan NRM (ascending) agar NRM terkecil (01) muncul pertama
        $patients = $query->orderBy('nrm', 'asc')->paginate(15);

        return \App\Http\Resources\PatientResource::collection($patients)->additional(['success' => true]);
    }

    /**
     * FR-03: Registrasi Pasien Baru
     */
    public function store(\App\Http\Requests\StorePatientRequest $request)
    {
        $patient = Patient::create($request->validated());

        // Create activity log
        \App\Models\ActivityLog::create([
            'id_pasien' => $patient->id_pasien,
            'id_pengguna' => Auth::id(),
            'activity_type' => 'Registrasi Pasien',
            'department' => 'Umum',
            'status' => 'Selesai',
            'description' => 'Pasien: ' . $patient->nama_lengkap
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pasien berhasil didaftarkan.',
            'data' => new \App\Http\Resources\PatientResource($patient)
        ], 201);
    }

    /**
     * FR-14: Detail Pasien & Riwayat Rekam Medis
     */
    public function show(Patient $patient)
    {
        $patient->load(['assessments', 'therapies', 'queues']);
        return new \App\Http\Resources\PatientResource($patient);
    }

    /**
     * FR-05: Pembaruan Data Pasien
     * Hanya Admin dan Dokter yang bisa mengupdate
     */
    public function update(Request $request, Patient $patient)
    {
        if (!in_array(Auth::user()->role, ['admin', 'dokter'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        $validated = $request->validate([
            'nrm' => ['sometimes', 'string', 'max:50', Rule::unique('patients')->ignore($patient->id_pasien, 'id_pasien')],
            'nik' => [
                'sometimes',
                'string',
                'digits_between:16,20',
                function ($attribute, $value, $fail) use ($patient) {
                    // Check nik_hash uniqueness, excluding current patient
                    $nikHash = hash('sha256', $value);
                    $exists = Patient::where('nik_hash', $nikHash)
                        ->where('id_pasien', '!=', $patient->id_pasien)
                        ->exists();
                    if ($exists) {
                        $fail('NIK sudah terdaftar dalam sistem.');
                    }
                }
            ],
            'nama_lengkap' => 'sometimes|string|max:255',
            'nama_panggilan' => 'nullable|string|max:255',
            'tanggal_lahir' => 'sometimes|date',
            'jenis_kelamin' => 'sometimes|in:L,P',
            'alamat' => 'sometimes|string',
            'no_telepon_wali' => 'sometimes|string|max:20',
            'nama_wali' => 'sometimes|string|max:255',
            'hubungan_wali' => 'sometimes|string|max:50',
            'riwayat_medis' => 'nullable|string',
        ]);

        $patient->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data pasien berhasil diperbarui.',
            'data' => new \App\Http\Resources\PatientResource($patient)
        ], 200);
    }

    /**
     * Hapus Data Pasien (Soft Delete)
     * Hanya Admin yang bisa menghapus
     */
    public function destroy(Patient $patient)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Admin yang dapat menghapus data pasien.'
            ], 403);
        }

        $patient->delete(); // Soft delete karena ada trait SoftDeletes di Model

        return response()->json([
            'success' => true,
            'message' => 'Data pasien berhasil dihapus.'
        ], 200);
    }

    /**
     * GET /api/patients/{id}/latest-assessment
     * Mendapatkan assessment terbaru milik pasien
     */
    public function latestAssessment(Patient $patient)
    {
        $assessment = $patient->assessments()
            ->orderBy('tanggal_assessment', 'desc')
            ->first();

        if (!$assessment) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada assessment untuk pasien ini.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new \App\Http\Resources\AssessmentResource($assessment)
        ], 200);
    }

    /**
     * GET /api/patients/{id}/progress-stats
     * Statistik perkembangan monitoring pasien
     */
    public function progressStats(Patient $patient)
    {
        $monitorings = \App\Models\TherapyMonitoring::where('id_pasien', $patient->id_pasien)
            ->with('therapy:id_terapi,nama_terapi')
            ->orderBy('tanggal_sesi', 'asc')
            ->get();

        if ($monitorings->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada data monitoring untuk pasien ini.'
            ], 404);
        }

        $stats = [
            'total_sesi'    => $monitorings->count(),
            'rata_rata_skor' => round($monitorings->avg('progress_score'), 2),
            'skor_tertinggi' => $monitorings->max('progress_score'),
            'skor_terendah'  => $monitorings->min('progress_score'),
            'perkembangan'  => $monitorings->map(fn($m) => [
                'tanggal'     => $m->tanggal_sesi?->format('Y-m-d'),
                'skor'        => $m->progress_score,
                'kehadiran'   => $m->kehadiran,
                'jenis_terapi'=> $m->therapy->nama_terapi ?? null,
            ])->values(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ], 200);
    }
}