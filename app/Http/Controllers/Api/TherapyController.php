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
    public function store(\App\Http\Requests\StoreTherapyRequest $request)
    {
        $validated = $request->validated();

        $therapy = Therapy::create(array_merge($validated, [
            'id_terapis' => Auth::id(),
            'status' => 'terjadwal',
        ]));

        $therapy->load(['patient', 'terapis', 'assessment']);

        return response()->json([
            'success' => true,
            'message' => 'Program terapi berhasil dibuat.',
            'data' => new \App\Http\Resources\TherapyResource($therapy)
        ], 201);
    }

    /**
     * Detail Program Terapi
     */
    public function show(Therapy $therapy)
    {
        $therapy->load(['patient', 'terapis', 'assessment', 'monitorings']);

        return response()->json([
            'success' => true,
            'data' => new \App\Http\Resources\TherapyResource($therapy)
        ], 200);
    }

    /**
     * Update Program Terapi
     */
    public function update(Request $request, Therapy $therapy)
    {
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
        $therapy->load(['patient', 'terapis']);

        return response()->json([
            'success' => true,
            'message' => 'Program terapi berhasil diperbarui.',
            'data' => new \App\Http\Resources\TherapyResource($therapy)
        ], 200);
    }

    /**
     * Hapus Program Terapi
     */
    public function destroy(Therapy $therapy)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya admin yang dapat menghapus program terapi.'
            ], 403);
        }

        $therapy->delete();

        return response()->json([
            'success' => true,
            'message' => 'Program terapi berhasil dihapus.'
        ], 200);
    }
}