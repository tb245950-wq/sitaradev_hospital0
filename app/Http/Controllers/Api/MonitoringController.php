<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TherapyMonitoring;
use App\Models\Therapy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\MonitoringResource;

class MonitoringController extends Controller
{
    /**
     * FR-09: List Monitoring Terapi
     */
    public function index(Request $request)
    {
        $query = TherapyMonitoring::with([
            'therapy:id_terapi,nama_terapi,status',
            'patient:id_pasien,nama_lengkap,nrm',
            'terapis:id,name,role'
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
            $query->whereDate('tanggal_sesi', $request->tanggal);
        }

        $monitorings = $query->orderBy('tanggal_sesi', 'desc')
                            ->paginate(15);

        return MonitoringResource::collection($monitorings)->additional(['success' => true]);
    }

    /**
     * FR-09: Buat Monitoring Baru
     */
    public function store(\App\Http\Requests\StoreMonitoringRequest $request)
    {
        $validated = $request->validated();
        $therapy = Therapy::findOrFail($validated['id_terapi']);

        $monitoring = TherapyMonitoring::create(array_merge($validated, [
            'id_pasien' => $therapy->id_pasien,
            'id_terapis' => Auth::id(),
            'tanggal_sesi' => $validated['tanggal_sesi'] ?? now(),
        ]));

        $monitoring->load(['therapy', 'patient', 'terapis']);

        return response()->json([
            'success' => true,
            'message' => 'Monitoring terapi berhasil dibuat.',
            'data' => new MonitoringResource($monitoring)
        ], 201);
    }

    /**
     * Update Monitoring
     */
    public function update(Request $request, TherapyMonitoring $monitoring)
    {
        if (!in_array(Auth::user()->role, ['dokter', 'terapis', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        $validated = $request->validate([
            'kehadiran' => 'sometimes|string',
            'catatan_perkembangan' => 'sometimes|string',
            'kondisi_pasien' => 'sometimes|string',
            'rekomendasi' => 'nullable|string',
            'progress_score' => 'sometimes|integer|min:0|max:100',
        ]);

        // Handle space in kehadiran (e.g. "tidak hadir" -> "tidak_hadir")
        if (isset($validated['kehadiran'])) {
            $validated['kehadiran'] = str_replace(' ', '_', strtolower($validated['kehadiran']));
            
            if (!in_array($validated['kehadiran'], ['hadir', 'tidak_hadir', 'izin', 'sakit'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status kehadiran tidak valid. Pilihan: hadir, tidak hadir, izin, sakit.'
                ], 422);
            }
        }

        $monitoring->update($validated);
        $monitoring->load(['therapy', 'patient', 'terapis']);

        return response()->json([
            'success' => true,
            'message' => 'Monitoring berhasil diperbarui.',
            'data' => new MonitoringResource($monitoring)
        ], 200);
    }

    /**
     * Detail Monitoring
     */
    public function show(TherapyMonitoring $monitoring)
    {
        $monitoring->load(['therapy', 'patient', 'terapis']);
        return new MonitoringResource($monitoring);
    }

    /**
     * Statistik Perkembangan Pasien
     */
    public function progressStats($id_pasien)
    {
        $monitorings = TherapyMonitoring::where('id_pasien', $id_pasien)
            ->with('therapy:id_terapi,nama_terapi')
            ->orderBy('tanggal_sesi', 'asc')
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
            'rata_rata_skor' => round($monitorings->avg('progress_score'), 2),
            'skor_tertinggi' => $monitorings->max('progress_score'),
            'skor_terendah' => $monitorings->min('progress_score'),
            'perkembangan' => [],
        ];

        foreach ($monitorings as $m) {
            $stats['perkembangan'][] = [
                'tanggal' => $m->tanggal_sesi->format('Y-m-d'),
                'skor' => $m->progress_score,
                'kehadiran' => $m->kehadiran,
                'jenis_terapi' => $m->therapy->nama_terapi ?? null,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $stats
        ], 200);
    }
}
