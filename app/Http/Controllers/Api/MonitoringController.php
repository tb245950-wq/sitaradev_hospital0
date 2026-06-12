<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TherapyMonitoring;
use App\Models\Therapy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonitoringController extends Controller
{
    /**
     * FR-09: List Monitoring Terapi
     */
    public function index(Request $request)
    {
        $query = TherapyMonitoring::with([
            'therapy:id_terapi,jenis_terapi,status',
            'patient:id_pasien,nama_lengkap,nrm',
            'user:id,name,role'
        ]);

        // Filter by terapi
        if ($request->has('id_terapi') && $request->id_terapi != '') {
            $query->where('id_terapi', $request->id_terapi);
        }

        // Filter by pasien
        if ($request->has('id_pasien') && $request->id_pasien != '') {
            $query->where('id_pasien', $request->id_pasien);
        }

        // Filter by tanggal
        if ($request->has('tanggal') && $request->tanggal != '') {
            $query->whereDate('tanggal_monitoring', $request->tanggal);
        }

        // Filter by rentang tanggal
        if ($request->has('tanggal_awal') && $request->has('tanggal_akhir')) {
            $query->whereBetween('tanggal_monitoring', [$request->tanggal_awal, $request->tanggal_akhir]);
        }

        $monitorings = $query->orderBy('tanggal_monitoring', 'desc')
                            ->orderBy('sesi_ke', 'desc')
                            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $monitorings
        ], 200);
    }

    /**
     * FR-09: Buat Monitoring Baru
     * Hanya dokter atau terapis yang bisa membuat
     */
    public function store(Request $request)
    {
        if (!in_array(Auth::user()->role, ['dokter', 'terapis'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya dokter atau terapis yang dapat membuat monitoring.'
            ], 403);
        }

        $validated = $request->validate([
            'id_terapi' => 'required|exists:therapies,id_terapi',
            'tanggal_monitoring' => 'sometimes|date',
            'progress' => 'required|string',
            'catatan_terapis' => 'required|string',
            'skor_perkembangan' => 'required|integer|min:0|max:100',
            'kendala' => 'nullable|string',
            'rekomendasi' => 'nullable|string',
        ]);

        // Ambil data terapi untuk dapat id_pasien
        $therapy = Therapy::where('id_terapi', $validated['id_terapi'])->first();

        // Auto-generate sesi_ke
        $sesiKe = TherapyMonitoring::where('id_terapi', $validated['id_terapi'])
            ->max('sesi_ke') + 1;

        $monitoring = TherapyMonitoring::create([
            'id_terapi' => $validated['id_terapi'],
            'id_pasien' => $therapy->id_pasien,
            'id_pengguna' => Auth::id(),
            'tanggal_monitoring' => $validated['tanggal_monitoring'] ?? now(),
            'sesi_ke' => $sesiKe,
            'progress' => $validated['progress'],
            'catatan_terapis' => $validated['catatan_terapis'],
            'skor_perkembangan' => $validated['skor_perkembangan'],
            'kendala' => $validated['kendala'] ?? null,
            'rekomendasi' => $validated['rekomendasi'] ?? null,
        ]);

        $monitoring->load(['therapy:id_terapi,jenis_terapi', 'patient:id_pasien,nama_lengkap,nrm', 'user:id,name,role']);

        return response()->json([
            'success' => true,
            'message' => 'Monitoring terapi berhasil dibuat.',
            'data' => $monitoring
        ], 201);
    }

    /**
     * Detail Monitoring
     */
    public function show($id)
    {
        $monitoring = TherapyMonitoring::where('id_monitoring', $id)
            ->with(['therapy', 'patient', 'user:id,name,role'])
            ->first();

        if (!$monitoring) {
            return response()->json([
                'success' => false,
                'message' => 'Monitoring tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $monitoring
        ], 200);
    }

    /**
     * Update Monitoring
     */
    public function update(Request $request, $id)
    {
        $monitoring = TherapyMonitoring::where('id_monitoring', $id)->first();

        if (!$monitoring) {
            return response()->json([
                'success' => false,
                'message' => 'Monitoring tidak ditemukan.'
            ], 404);
        }

        if (!in_array(Auth::user()->role, ['dokter', 'terapis', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        $validated = $request->validate([
            'progress' => 'sometimes|string',
            'catatan_terapis' => 'sometimes|string',
            'skor_perkembangan' => 'sometimes|integer|min:0|max:100',
            'kendala' => 'nullable|string',
            'rekomendasi' => 'nullable|string',
        ]);

        $monitoring->update($validated);
        $monitoring->load(['therapy:id_terapi,jenis_terapi', 'patient:id_pasien,nama_lengkap,nrm', 'user:id,name,role']);

        return response()->json([
            'success' => true,
            'message' => 'Monitoring berhasil diperbarui.',
            'data' => $monitoring
        ], 200);
    }

    /**
     * Statistik Perkembangan Pasien
     * Endpoint khusus untuk melihat grafik perkembangan
     */
    public function progressStats($id_pasien)
    {
        $monitorings = TherapyMonitoring::where('id_pasien', $id_pasien)
            ->with('therapy:id_terapi,jenis_terapi')
            ->orderBy('tanggal_monitoring', 'asc')
            ->get();

        if ($monitorings->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada data monitoring untuk pasien ini.'
            ], 404);
        }

        // Hitung statistik
        $stats = [
            'total_sesi' => $monitorings->count(),
            'rata_rata_skor' => round($monitorings->avg('skor_perkembangan'), 2),
            'skor_tertinggi' => $monitorings->max('skor_perkembangan'),
            'skor_terendah' => $monitorings->min('skor_perkembangan'),
            'perkembangan' => [],
        ];

        foreach ($monitorings as $m) {
            $stats['perkembangan'][] = [
                'tanggal' => $m->tanggal_monitoring->format('Y-m-d'),
                'sesi_ke' => $m->sesi_ke,
                'skor' => $m->skor_perkembangan,
                'jenis_terapi' => $m->therapy->jenis_terapi ?? null,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $stats
        ], 200);
    }
}