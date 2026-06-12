<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Therapy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TherapyController extends Controller
{
    /**
     * FR-08: List Program Terapi
     */
    public function index(Request $request)
    {
        $query = Therapy::with([
            'patient:id_pasien,nama_lengkap,nrm',
            'terapis:id,name,role',
            'assessment:id_assessment,diagnosis'
        ]);

        // Filter by pasien
        if ($request->has('id_pasien') && $request->id_pasien != '') {
            $query->where('id_pasien', $request->id_pasien);
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by nama terapi
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_terapi', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $therapies = $query->orderBy('tanggal_mulai', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $therapies
        ], 200);
    }

    /**
     * FR-08: Buat Program Terapi Baru
     */
    public function store(Request $request)
    {
        if (!in_array(Auth::user()->role, ['dokter', 'terapis'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya dokter atau terapis yang dapat membuat program terapi.'
            ], 403);
        }

        $validated = $request->validate([
            'id_pasien' => 'required|exists:patients,id_pasien',
            'id_assessment' => 'sometimes|exists:medical_assessments,id_assessment',
            'nama_terapi' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'dosis' => 'sometimes|string|max:255',
            'durasi_hari' => 'required|integer|min:1',
            'frekuensi_per_minggu' => 'required|integer|min:1|max:7',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $therapy = Therapy::create([
            'id_pasien' => $validated['id_pasien'],
            'id_assessment' => $validated['id_assessment'] ?? null,
            'id_terapis' => Auth::id(),
            'nama_terapi' => $validated['nama_terapi'],
            'deskripsi' => $validated['deskripsi'],
            'dosis' => $validated['dosis'] ?? null,
            'durasi_hari' => $validated['durasi_hari'],
            'frekuensi_per_minggu' => $validated['frekuensi_per_minggu'],
            'status' => 'terjadwal',
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
        ]);

        $therapy->load(['patient:id_pasien,nama_lengkap,nrm', 'terapis:id,name,role', 'assessment:id_assessment,diagnosis']);

        return response()->json([
            'success' => true,
            'message' => 'Program terapi berhasil dibuat.',
            'data' => $therapy
        ], 201);
    }

    /**
     * Detail Program Terapi
     */
    public function show($id)
    {
        $therapy = Therapy::where('id_terapi', $id)
            ->with(['patient', 'terapis:id,name,role', 'assessment', 'monitorings'])
            ->first();

        if (!$therapy) {
            return response()->json([
                'success' => false,
                'message' => 'Program terapi tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $therapy
        ], 200);
    }

    /**
     * Update Program Terapi
     */
    public function update(Request $request, $id)
    {
        $therapy = Therapy::where('id_terapi', $id)->first();

        if (!$therapy) {
            return response()->json([
                'success' => false,
                'message' => 'Program terapi tidak ditemukan.'
            ], 404);
        }

        if (!in_array(Auth::user()->role, ['dokter', 'terapis', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        $validated = $request->validate([
            'nama_terapi' => 'sometimes|string|max:255',
            'deskripsi' => 'sometimes|string',
            'dosis' => 'sometimes|string|max:255',
            'durasi_hari' => 'sometimes|integer|min:1',
            'frekuensi_per_minggu' => 'sometimes|integer|min:1|max:7',
            'status' => 'sometimes|in:terjadwal,aktif,selesai,dihentikan',
            'tanggal_selesai' => 'nullable|date',
        ]);

        $therapy->update($validated);
        $therapy->load(['patient:id_pasien,nama_lengkap,nrm', 'terapis:id,name,role']);

        return response()->json([
            'success' => true,
            'message' => 'Program terapi berhasil diperbarui.',
            'data' => $therapy
        ], 200);
    }

    /**
     * Hapus Program Terapi
     */
    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya admin yang dapat menghapus program terapi.'
            ], 403);
        }

        $therapy = Therapy::where('id_terapi', $id)->first();

        if (!$therapy) {
            return response()->json([
                'success' => false,
                'message' => 'Program terapi tidak ditemukan.'
            ], 404);
        }

        $therapy->delete();

        return response()->json([
            'success' => true,
            'message' => 'Program terapi berhasil dihapus.'
        ], 200);
    }
}