<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicalAssessment;
use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AssessmentController extends Controller
{
    /**
     * FR-07: List Assessment dengan Filter
     * Mendukung filter berdasarkan pasien, dokter, tanggal
     */
    public function index(Request $request)
    {
        $query = MedicalAssessment::with([
            'patient:id_pasien,nama_lengkap,nrm',
            'user:id,name,role',
            'queue:id_antrian,nomor_antrian,status'
        ]);

        // Filter berdasarkan pasien
        if ($request->has('id_pasien') && $request->id_pasien != '') {
            $query->where('id_pasien', $request->id_pasien);
        }

        // Filter berdasarkan dokter
        if ($request->has('id_dokter') && $request->id_dokter != '') {
            $query->where('id_pengguna', $request->id_dokter);
        }

        // Filter berdasarkan tanggal assessment
        if ($request->has('tanggal') && $request->tanggal != '') {
            $query->whereDate('tanggal_assessment', $request->tanggal);
        }

        // Filter berdasarkan antrian
        if ($request->has('id_antrian') && $request->id_antrian != '') {
            $query->where('id_antrian', $request->id_antrian);
        }

        // Search berdasarkan diagnosis atau keluhan
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('keluhan_utama', 'like', "%{$search}%")
                  ->orWhere('diagnosis', 'like', "%{$search}%")
                  ->orWhere('hasil_pemeriksaan', 'like', "%{$search}%");
            });
        }

        $assessments = $query->orderBy('tanggal_assessment', 'desc')
                            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $assessments
        ], 200);
    }

    /**
     * FR-07: Buat Assessment Medis Baru
     */
    public function store(\App\Http\Requests\StoreAssessmentRequest $request)
    {
        $validated = $request->validated();

        // Cek apakah sudah ada assessment untuk antrian ini
        if (isset($validated['id_antrian'])) {
            $existingAssessment = MedicalAssessment::where('id_antrian', $validated['id_antrian'])->first();
            
            if ($existingAssessment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assessment untuk antrian ini sudah pernah dibuat.',
                    'data' => $existingAssessment
                ], 409);
            }
        }

        $assessment = MedicalAssessment::create(array_merge($validated, [
            'id_pengguna' => Auth::id(),
            'tanggal_assessment' => $validated['tanggal_assessment'] ?? now(),
            'catatan_medis' => $validated['catatan_medis'] ?? '',
        ]));

        $assessment->load(['patient', 'user', 'queue']);

        return response()->json([
            'success' => true,
            'message' => 'Assessment medis berhasil dibuat.',
            'data' => new \App\Http\Resources\AssessmentResource($assessment)
        ], 201);
    }

    /**
     * Detail Assessment Medis
     */
    public function show(MedicalAssessment $assessment)
    {
        $assessment->load([
            'patient',
            'user:id,name,role',
            'queue',
            'therapies'
        ]);

        return response()->json([
            'success' => true,
            'data' => new \App\Http\Resources\AssessmentResource($assessment)
        ], 200);
    }

    /**
     * Update Assessment Medis
     * Hanya dokter yang membuat assessment atau admin yang bisa update
     */
    public function update(Request $request, MedicalAssessment $assessment)
    {
        // Validasi role
        if (Auth::user()->role !== 'dokter' && Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        // Cek authorization - hanya dokter yang membuat atau admin yang bisa update
        if (Auth::user()->role !== 'admin' && $assessment->id_pengguna !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengupdate assessment ini.'
            ], 403);
        }

        $validated = $request->validate([
            'keluhan_utama' => 'sometimes|string',
            'riwayat_penyakit' => 'nullable|string',
            'hasil_pemeriksaan' => 'sometimes|array',
            'hasil_pemeriksaan.tensi' => 'sometimes|string',
            'hasil_pemeriksaan.nadi' => 'sometimes|string',
            'hasil_pemeriksaan.suhu' => 'sometimes|string',
            'hasil_pemeriksaan.berat_badan' => 'sometimes|numeric',
            'hasil_pemeriksaan.tinggi_badan' => 'sometimes|numeric',
            'diagnosis' => 'sometimes|string',
            'rencana_terapi' => 'nullable|string',
            'obat_diresepkan' => 'nullable|array',
            'catatan_tambahan' => 'nullable|string',
        ]);

        $assessment->update($validated);
        $assessment->load(['patient', 'user']);

        return response()->json([
            'success' => true,
            'message' => 'Assessment medis berhasil diperbarui.',
            'data' => new \App\Http\Resources\AssessmentResource($assessment)
        ], 200);
    }

    /**
     * Hapus Assessment
     * Hanya admin yang bisa menghapus
     */
    public function destroy(MedicalAssessment $assessment)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya admin yang dapat menghapus assessment medis.'
            ], 403);
        }

        $assessment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Assessment medis berhasil dihapus.'
        ], 200);
    }

    /**
     * Get Assessment Terbaru untuk Pasien
     */
    public function latestByPatient($id_pasien)
    {
        $assessment = MedicalAssessment::where('id_pasien', $id_pasien)
            ->with(['patient', 'user'])
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
}