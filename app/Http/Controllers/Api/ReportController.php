<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Queue;
use App\Models\MedicalAssessment;
use App\Models\Therapy;
use App\Models\TherapyMonitoring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * FR-10: Laporan Harian
     */
    public function daily(Request $request)
    {
        if (!in_array(Auth::user()->role, ['admin', 'dokter'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya Admin atau Dokter yang dapat melihat laporan.'
            ], 403);
        }

        $tanggal = $request->input('tanggal', now()->format('Y-m-d'));

        $pasienBaru = Patient::whereDate('created_at', $tanggal)->count();
        $totalPasienAktif = Patient::count();

        $totalAntrian = Queue::whereDate('waktu_daftar', $tanggal)->count();
        $antrianSelesai = Queue::whereDate('waktu_daftar', $tanggal)
            ->where('status', 'selesai')->count();
        $antrianTidakHadir = Queue::whereDate('waktu_daftar', $tanggal)
            ->where('status', 'tidak_hadir')->count();

        $totalAssessment = MedicalAssessment::whereDate('tanggal_assessment', $tanggal)->count();

        $totalTerapiAktif = Therapy::where('status', 'aktif')->count();
        $totalTerapiBaru = Therapy::whereDate('tanggal_mulai', $tanggal)->count();

        $totalMonitoring = TherapyMonitoring::whereDate('tanggal_sesi', $tanggal)->count();
        $rataRataSkor = TherapyMonitoring::whereDate('tanggal_sesi', $tanggal)
            ->avg('progress_score');

        $antrianPerJenis = Queue::whereDate('waktu_daftar', $tanggal)
            ->select('jenis_layanan', DB::raw('count(*) as total'))
            ->groupBy('jenis_layanan')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'tanggal' => $tanggal,
                'ringkasan' => [
                    'pasien_baru' => $pasienBaru,
                    'total_pasien_aktif' => $totalPasienAktif,
                    'total_antrian' => $totalAntrian,
                    'antrian_selesai' => $antrianSelesai,
                    'antrian_tidak_hadir' => $antrianTidakHadir,
                    'total_assessment' => $totalAssessment,
                    'total_terapi_aktif' => $totalTerapiAktif,
                    'total_terapi_baru' => $totalTerapiBaru,
                    'total_monitoring' => $totalMonitoring,
                    'rata_rata_skor_monitoring' => round($rataRataSkor ?? 0, 2),
                ],
                'antrian_per_jenis' => $antrianPerJenis,
                'dilaporkan_oleh' => Auth::user()->name,
            ]
        ], 200);
    }

    public function monthly(Request $request)
    {
        $tahun = $request->input('tahun', now()->year);
        $bulan = $request->input('bulan', now()->month);

        $tanggalAwal = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $tanggalAkhir = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();

        $stats = [
            'total_pasien_baru' => Patient::whereBetween('created_at', [$tanggalAwal, $tanggalAkhir])->count(),
            'total_assessment' => MedicalAssessment::whereBetween('tanggal_assessment', [$tanggalAwal, $tanggalAkhir])->count(),
            'total_monitoring' => TherapyMonitoring::whereBetween('tanggal_sesi', [$tanggalAwal, $tanggalAkhir])->count(),
            'total_terapi_baru' => Therapy::whereBetween('tanggal_mulai', [$tanggalAwal, $tanggalAkhir])->count(),
            'rata_rata_skor' => round(TherapyMonitoring::whereBetween('tanggal_sesi', [$tanggalAwal, $tanggalAkhir])->avg('progress_score') ?? 0, 2),
        ];

        // OPTIMIZED: Ambil tren harian dalam SATU query besar menggunakan Group By
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

        // Mapping ke array tren harian untuk frontend
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

        return response()->json([
            'success' => true,
            'data' => [
                'periode' => [
                    'nama_bulan' => $tanggalAwal->locale('id')->monthName,
                    'tahun' => $tahun,
                ],
                'ringkasan' => $stats,
                'tren_harian' => $trenHarian,
                'top_diagnosis' => $topDiagnosis,
            ]
        ], 200);
    }

    /**
     * FR-12: Laporan Per Pasien
     */
    public function patientReport($id_pasien)
    {
        if (!in_array(Auth::user()->role, ['admin', 'dokter', 'terapis'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        $patient = Patient::where('id_pasien', $id_pasien)->first();

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Pasien tidak ditemukan.'
            ], 404);
        }

        $patient->load([
            'assessments' => function($q) {
                $q->orderBy('tanggal_assessment', 'desc');
            },
            'assessments.user:id,name,role',
            'therapies' => function($q) {
                $q->orderBy('tanggal_mulai', 'desc');
            },
            'therapies.monitorings' => function($q) {
                $q->orderBy('tanggal_sesi', 'asc');
            },
            'queues' => function($q) {
                $q->orderBy('waktu_daftar', 'desc');
            },
        ]);

        $totalAssessment = $patient->assessments->count();
        $totalTerapi = $patient->therapies->count();
        $totalMonitoring = $patient->therapies->sum(function($t) {
            return $t->monitorings->count();
        });

        $allMonitorings = $patient->therapies->flatMap(function($t) {
            return $t->monitorings;
        })->sortBy('tanggal_sesi');

        $progressTrend = $allMonitorings->map(function($m) {
            return [
                'tanggal' => $m->tanggal_sesi->format('Y-m-d'),
                'skor' => $m->progress_score,
                'jenis_terapi' => $m->therapy->nama_terapi ?? null,
            ];
        })->values();

        $rataRataSkor = $allMonitorings->avg('progress_score');
        $kehadiranStats = $allMonitorings->groupBy('kehadiran')->map->count();

        return response()->json([
            'success' => true,
            'data' => [
                'pasien' => $patient,
                'statistik' => [
                    'total_assessment' => $totalAssessment,
                    'total_terapi' => $totalTerapi,
                    'total_sesi_monitoring' => $totalMonitoring,
                    'rata_rata_skor' => round($rataRataSkor ?? 0, 2),
                    'kehadiran' => $kehadiranStats,
                ],
                'riwayat_assessment' => $patient->assessments,
                'riwayat_terapi' => $patient->therapies,
                'riwayat_antrian' => $patient->queues,
                'tren_progress' => $progressTrend,
            ]
        ], 200);
    }

    /**
     * Dashboard Ringkas
     */
    public function dashboard()
    {
        if (!in_array(Auth::user()->role, ['admin', 'dokter', 'terapis'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        $today = now()->format('Y-m-d');

        return response()->json([
            'success' => true,
            'data' => [
                'tanggal' => $today,
                'total_pasien' => Patient::count(),
                'pasien_hari_ini' => Patient::whereDate('created_at', $today)->count(),
                'antrian_menunggu' => Queue::where('status', 'menunggu')->count(),
                'antrian_hari_ini' => Queue::whereDate('waktu_daftar', $today)->count(),
                'assessment_hari_ini' => MedicalAssessment::whereDate('tanggal_assessment', $today)->count(),
                'monitoring_hari_ini' => TherapyMonitoring::whereDate('tanggal_sesi', $today)->count(),
                'terapi_aktif' => Therapy::where('status', 'aktif')->count(),
            ]
        ], 200);
    }
}