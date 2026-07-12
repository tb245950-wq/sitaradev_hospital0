<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\TherapyMonitoring;
use App\Models\Therapy;
use Illuminate\Support\Facades\View;
use PDF;

class MonitoringReportPdfService
{
    /**
     * Generate PDF Laporan Pemantauan Tumbuh Kembang Anak
     * 
     * Format mencakup:
     * I. Informasi Umum
     * II. Hasil Pengukuran Pertumbuhan
     * III. Evaluasi Perkembangan (Milestone Checklist)
     * IV. Kesimpulan dan Rekomendasi
     */
    public function generateMonitoringReport($id_pasien, $id_terapi = null)
    {
        $patient = Patient::findOrFail($id_pasien);
        
        // Get therapy jika tidak di-specify
        if (!$id_terapi) {
            $therapy = Therapy::where('id_pasien', $id_pasien)
                ->where('status', 'berjalan')
                ->first();
        } else {
            $therapy = Therapy::where('id_terapi', $id_terapi)
                ->where('id_pasien', $id_pasien)
                ->first();
        }
        
        if (!$therapy) {
            throw new \Exception('Terapi tidak ditemukan untuk pasien ini');
        }
        
        // Get monitoring sessions
        $monitorings = TherapyMonitoring::where('id_terapi', $therapy->id_terapi)
            ->where('kehadiran', 'hadir')
            ->orderBy('tanggal_sesi', 'asc')
            ->get();
        
        if ($monitorings->isEmpty()) {
            throw new \Exception('Belum ada sesi monitoring yang hadir');
        }
        
        // Compile data untuk laporan
        $reportData = [
            'patient' => $patient,
            'therapy' => $therapy,
            'monitorings' => $monitorings,
            'reportDate' => now(),
            'summaryData' => $this->compileSummaryData($monitorings, $patient, $therapy),
            'developmentData' => $this->compileDevelopmentData($monitorings),
            'progressTrend' => $this->compileProgressTrend($monitorings),
            'recommendations' => $this->compileRecommendations($monitorings),
        ];
        
        // Generate PDF
        $pdf = PDF::loadView('monitoring-report', $reportData);
        
        return $pdf;
    }
    
    /**
     * Compile data ringkasan untuk laporan
     */
    private function compileSummaryData($monitorings, $patient, $therapy)
    {
        $latestMonitoring = $monitorings->last();
        
        return [
            'nama_anak' => $patient->nama_lengkap,
            'tanggal_lahir' => $patient->tanggal_lahir,
            'usia' => $this->calculateAge($patient->tanggal_lahir),
            'nama_orang_tua' => $patient->nama_wali,
            'hubungan_wali' => $patient->hubungan_wali,
            'tanggal_pemeriksaan' => $latestMonitoring->tanggal_sesi->format('d M Y'),
            'pemeriksa' => 'Tim Klinik Tumbuh Kembang Anak',
            'jenis_terapi' => $therapy->nama_terapi,
            'status_terapi' => $therapy->status,
            'total_sesi' => $monitorings->count(),
            'rata_skor' => round($monitorings->avg('progress_score'), 2),
        ];
    }
    
    /**
     * Compile data perkembangan
     */
    private function compileDevelopmentData($monitorings)
    {
        $latest = $monitorings->last();
        
        // Parse catatan perkembangan untuk milestone checklist
        $development = [
            [
                'aspek' => 'Motorik Kasar',
                'pencapaian' => $this->extractMilestone($latest->catatan_perkembangan, 'motorik kasar'),
                'catatan' => $this->extractCatatan($latest->catatan_perkembangan, 'motorik kasar'),
                'status' => $latest->progress_score >= 70 ? 'Baik' : 'Perlu Perhatian'
            ],
            [
                'aspek' => 'Motorik Halus',
                'pencapaian' => $this->extractMilestone($latest->catatan_perkembangan, 'motorik halus'),
                'catatan' => $this->extractCatatan($latest->catatan_perkembangan, 'motorik halus'),
                'status' => $latest->progress_score >= 70 ? 'Baik' : 'Perlu Perhatian'
            ],
            [
                'aspek' => 'Bahasa & Komunikasi',
                'pencapaian' => $this->extractMilestone($latest->catatan_perkembangan, 'bahasa'),
                'catatan' => $this->extractCatatan($latest->catatan_perkembangan, 'bahasa'),
                'status' => $latest->progress_score >= 70 ? 'Baik' : 'Perlu Perhatian'
            ],
            [
                'aspek' => 'Sosial & Emosional',
                'pencapaian' => $this->extractMilestone($latest->catatan_perkembangan, 'sosial'),
                'catatan' => $this->extractCatatan($latest->catatan_perkembangan, 'sosial'),
                'status' => $latest->progress_score >= 70 ? 'Baik' : 'Perlu Perhatian'
            ],
        ];
        
        return $development;
    }
    
    /**
     * Compile tren perkembangan
     */
    private function compileProgressTrend($monitorings)
    {
        return $monitorings->map(function($m) {
            return [
                'tanggal' => $m->tanggal_sesi->format('d-m-Y'),
                'skor' => $m->progress_score,
                'kehadiran' => $m->kehadiran,
                'kondisi' => $m->kondisi_pasien,
                'catatan' => $m->catatan_perkembangan,
            ];
        })->values()->all();
    }
    
    /**
     * Compile rekomendasi dari monitoring terakhir
     */
    private function compileRecommendations($monitorings)
    {
        $latest = $monitorings->last();
        
        // Split rekomendasi dari dokter
        $recommendations = explode('|', $latest->rekomendasi ?? 'Lanjutkan terapi reguler');
        
        return array_map('trim', $recommendations);
    }
    
    /**
     * Calculate umur
     */
    private function calculateAge($birthDate)
    {
        $birth = \Carbon\Carbon::parse($birthDate);
        $now = \Carbon\Carbon::now();
        
        $years = $now->diffInYears($birth);
        $months = $now->copy()->subYears($years)->diffInMonths($birth);
        
        if ($years > 0) {
            return "{$years} Tahun {$months} Bulan";
        } else {
            return "{$months} Bulan";
        }
    }
    
    /**
     * Extract milestone dari catatan
     */
    private function extractMilestone($note, $category)
    {
        // Jika ada milestone marker, extract
        if (stripos($note, $category) !== false) {
            return ucfirst($category) . ' berkembang sesuai usia';
        }
        return 'Data pencapaian tersedia di detail monitoring';
    }
    
    /**
     * Extract catatan tambahan
     */
    private function extractCatatan($note, $category)
    {
        return $note ?? 'Lihat detail sesi untuk catatan lengkap';
    }
}
