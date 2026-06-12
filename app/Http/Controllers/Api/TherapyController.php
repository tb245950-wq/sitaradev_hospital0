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
            'user:id,name,role',
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

        // Filter by jenis terapi
        if ($request->has('jenis_terapi') && $request->jenis_terapi != '') {
            $query->where('jenis_terapi', $request->jenis_terapi);
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('jenis_terapi', 'like', "%{$search}%")
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
     * Hanya dokter atau terapis yang bisa membuat
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
            'jenis_terapi' => 'required|string|max:100',
            'deskripsi' => 'required|string',
            'frekuensi' => 'required|string|max:50',
            'durasi_menit' => 'required|integer|min:1',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'catatan' => 'nullable|string',
        ]);

        $therapy = Therapy::create([
            'id_pasien' => $validated['id_pasien'],
            'id_assessment' => $validated['id_assessment'] ?? null,
            'id_pengguna' => Auth::id(),
            'jenis_terapi' => $validated['jenis_terapi'],
            'deskripsi' => $validated['deskripsi'],
            'frekuensi' => $validated['frekuensi'],
            'durasi_menit' => $validated['durasi_menit'],
            'status' => 'aktif',
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
            'catatan' => $validated['catatan'] ?? null,
        ]);

        $therapy->load(['patient:id_pasien,nama_lengkap,nrm', 'user:id,name,role', 'assessment:id_assessment,diagnosis']);

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
            ->with(['patient', 'user:id,name,role', 'assessment', 'monitorings'])
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
            'jenis_terapi' => 'sometimes|string|max:100',
            'deskripsi' => 'sometimes|string',
            'frekuensi' => 'sometimes|string|max:50',
            'durasi_menit' => 'sometimes|integer|min:1',
            'status' => 'sometimes|in:aktif,selesai,dihentikan',
            'tanggal_selesai' => 'nullable|date',
            'catatan' => 'nullable|string',
        ]);

        $therapy->update($validated);
        $therapy->load(['patient:id_pasien,nama_lengkap,nrm', 'user:id,name,role']);

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