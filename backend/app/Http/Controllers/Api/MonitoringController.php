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

    /**
     * Delete Monitoring
     * Hanya dokter yang membuat atau admin yang bisa delete
     */
    public function destroy(TherapyMonitoring $monitoring)
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            // Admin bisa hapus apa saja
        } elseif ($user->role === 'dokter' || $user->role === 'terapis') {
            // Dokter/Terapis hanya bisa hapus monitoring miliknya sendiri
            if ($monitoring->id_terapis !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk menghapus monitoring ini.'
                ], 403);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        $monitoring->delete();

        return response()->json([
            'success' => true,
            'message' => 'Monitoring berhasil dihapus.'
        ], 200);
    }

    /**
     * Generate Laporan PDF Pemantauan Tumbuh Kembang Anak
     * Format profesional dengan struktur:
     * I. Informasi Umum
     * II. Hasil Pengukuran Pertumbuhan
     * III. Evaluasi Perkembangan (Milestone Checklist)
     * IV. Kesimpulan dan Rekomendasi
     */
    public function generateMonitoringReportPdf($id_pasien, $id_terapi = null)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'dokter', 'terapis'])) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya staff yang dapat generate laporan'
            ], 403);
        }

        try {
            $service = new \App\Services\MonitoringReportPdfService();
            $pdf = $service->generateMonitoringReport($id_pasien, $id_terapi);
            
            $patient = \App\Models\Patient::findOrFail($id_pasien);
            $filename = 'Laporan_Monitoring_' . str_replace(' ', '_', $patient->nama_lengkap) . '_' . now()->format('Y-m-d-His') . '.pdf';
            
            return $pdf->download($filename);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate laporan: ' . $e->getMessage()
            ], 400);
        }
    }
    public function generateAssessment(Request $request, $id_terapi)
    {
        $user = Auth::user();
        
        if ($user->role !== 'dokter' && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya dokter yang dapat membuat assessment'
            ], 403);
        }
        
        $therapy = Therapy::findOrFail($id_terapi);
        $monitorings = TherapyMonitoring::where('id_terapi', $id_terapi)
            ->where('kehadiran', 'hadir')
            ->orderBy('tanggal_sesi', 'desc')
            ->get();
        
        if ($monitorings->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada sesi monitoring yang hadir'
            ], 400);
        }
        
        // Compile progress notes
        $progressSummary = $monitorings->map(function($m) {
            return "Sesi (" . $m->tanggal_sesi->format('d/m/Y') . "):\n" .
                   "- Progress: {$m->catatan_perkembangan}\n" .
                   "- Kondisi: {$m->kondisi_pasien}\n";
        })->take(5)->join("\n\n");
        
        // Create assessment
        $assessment = \App\Models\MedicalAssessment::create([
            'id_pasien' => $therapy->id_pasien,
            'id_pengguna' => $user->id,
            'tanggal_assessment' => now(),
            'keluhan_utama' => "Monitoring Terapi: {$therapy->nama_terapi}\n\nRingkasan 5 Sesi Terakhir:\n{$progressSummary}",
            'diagnosis' => $therapy->deskripsi,
            'rencana_terapi' => $monitorings->first()->rekomendasi ?? 'Lanjutkan terapi',
            'catatan_medis' => "Assessment otomatis dari monitoring terapi #{$therapy->id_terapi}",
            'status' => 'draft',
            'hasil_pemeriksaan' => ['tensi' => '-', 'nadi' => '-', 'suhu' => '-']
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Assessment berhasil dibuat dari monitoring terapi',
            'data' => new \App\Http\Resources\AssessmentResource($assessment)
        ], 201);
    }
}
