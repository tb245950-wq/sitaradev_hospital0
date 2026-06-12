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
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nrm', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        // Pagination 15 data per halaman untuk performa
        $patients = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $patients
        ], 200);
    }

    /**
     * FR-03: Registrasi Pasien Baru
     */
    public function store(\App\Http\Requests\StorePatientRequest $request)
    {
        $patient = Patient::create($request->validated());

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
            'nik' => ['sometimes', 'string', 'max:20', Rule::unique('patients')->ignore($patient->id_pasien, 'id_pasien')],
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
}