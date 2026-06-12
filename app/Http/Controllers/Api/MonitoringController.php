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

        return response()->json([
            'success' => true,
            'data' => $monitorings
        ], 200);
    }

    /**
 * FR-09: Buat Monitoring Baru
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
        'tanggal_sesi' => 'sometimes|date',
        'waktu_mulai' => 'required|date_format:H:i',
        'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
        'kehadiran' => 'required|in:hadir,tidak hadir,izin,sakit',
        'catatan_perkembangan' => 'required|string',
        'kondisi_pasien' => 'required|string',
        'rekomendasi' => 'nullable|string',
        'progress_score' => 'required|integer|min:0|max:100',
    ]);

    // Ambil data terapi untuk dapat id_pasien
    $therapy = Therapy::where('id_terapi', $validated['id_terapi'])->first();

    // Auto-lowercase kehadiran
    $validated['kehadiran'] = strtolower($validated['kehadiran']);

    // Auto-generate sesi ke
    $sesiKe = TherapyMonitoring::where('id_terapi', $validated['id_terapi'])
        ->count() + 1;

    $monitoring = TherapyMonitoring::create([
        'id_terapi' => $validated['id_terapi'],
        'id_pasien' => $therapy->id_pasien,
        'id_terapis' => Auth::id(),
        'tanggal_sesi' => $validated['tanggal_sesi'] ?? now(),
        'waktu_mulai' => $validated['waktu_mulai'],
        'waktu_selesai' => $validated['waktu_selesai'],
        'kehadiran' => $validated['kehadiran'],
        'catatan_perkembangan' => $validated['catatan_perkembangan'],
        'kondisi_pasien' => $validated['kondisi_pasien'],
        'rekomendasi' => $validated['rekomendasi'] ?? null,
        'progress_score' => $validated['progress_score'],
    ]);

    $monitoring->load(['therapy:id_terapi,nama_terapi', 'patient:id_pasien,nama_lengkap,nrm', 'terapis:id,name,role']);

    return response()->json([
        'success' => true,
        'message' => 'Monitoring terapi berhasil dibuat.',
        'data' => $monitoring
    ], 201);
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
        'kehadiran' => 'sometimes|in:hadir,tidak hadir,izin,sakit',
        'catatan_perkembangan' => 'sometimes|string',
        'kondisi_pasien' => 'sometimes|string',
        'rekomendasi' => 'nullable|string',
        'progress_score' => 'sometimes|integer|min:0|max:100',
    ]);

    // Auto-lowercase kehadiran jika ada
    if (isset($validated['kehadiran'])) {
        $validated['kehadiran'] = strtolower($validated['kehadiran']);
    }

    $monitoring->update($validated);
    $monitoring->load(['therapy:id_terapi,nama_terapi', 'patient:id_pasien,nama_lengkap,nrm', 'terapis:id,name,role']);

    return response()->json([
        'success' => true,
        'message' => 'Monitoring berhasil diperbarui.',
        'data' => $monitoring
    ], 200);
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