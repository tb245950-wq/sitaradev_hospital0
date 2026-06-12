<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\MedicalAssessment;
use App\Models\Therapy;
use App\Models\TherapyMonitoring;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    public function getMonthlyStats($tahun, $bulan)
    {
        $tanggalAwal = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $tanggalAkhir = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();

        $stats = [
            'total_pasien_baru' => Patient::whereBetween('created_at', [$tanggalAwal, $tanggalAkhir])->count(),
            'total_assessment' => MedicalAssessment::whereBetween('tanggal_assessment', [$tanggalAwal, $tanggalAkhir])->count(),
            'total_monitoring' => TherapyMonitoring::whereBetween('tanggal_sesi', [$tanggalAwal, $tanggalAkhir])->count(),
            'total_terapi_baru' => Therapy::whereBetween('tanggal_mulai', [$tanggalAwal, $tanggalAkhir])->count(),
            'rata_rata_skor' => round(TherapyMonitoring::whereBetween('tanggal_sesi', [$tanggalAwal, $tanggalAkhir])->avg('progress_score') ?? 0, 2),
        ];

        $trenHarianData = TherapyMonitoring::whereBetween('tanggal_sesi', [$tanggalAwal, $tanggalAkhir])
            ->select(
                DB::raw('DATE(tanggal_sesi) as tanggal'),
                DB::raw('COUNT(*) as total_monitoring'),
                DB::raw('AVG(progress_score) as rata_skor')
            )
            ->groupBy('tanggal')
            ->get()
            ->keyBy('tanggal');

        $pasienBaruData = Patient::whereBetween('created_at', [$tanggalAwal, $tanggalAkhir])
            ->select(DB::raw('DATE(created_at) as tanggal'), DB::raw('COUNT(*) as total'))
            ->groupBy('tanggal')
            ->get()
            ->keyBy('tanggal');

        $trenHarian = [];
        $currentDate = $tanggalAwal->copy();
        while ($currentDate->lte($tanggalAkhir)) {
            $dateStr = $currentDate->format('Y-m-d');
            $trenHarian[] = [
                'tanggal' => $dateStr,
                'pasien_baru' => $pasienBaruData[$dateStr]->total ?? 0,
                'monitoring' => $trenHarianData[$dateStr]->total_monitoring ?? 0,
                'rata_skor' => round($trenHarianData[$dateStr]->rata_skor ?? 0, 2),
            ];
            $currentDate->addDay();
        }

        $topDiagnosis = MedicalAssessment::whereBetween('tanggal_assessment', [$tanggalAwal, $tanggalAkhir])
            ->select('diagnosis', DB::raw('count(*) as total'))
            ->groupBy('diagnosis')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return [
            'periode' => [
                'nama_bulan' => $tanggalAwal->locale('id')->monthName,
                'tahun' => $tahun,
            ],
            'ringkasan' => $stats,
            'tren_harian' => $trenHarian,
            'top_diagnosis' => $topDiagnosis,
        ];
    }
}
