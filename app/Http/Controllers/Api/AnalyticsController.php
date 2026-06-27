<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\MedicalAssessment;
use App\Models\Therapy;
use App\Models\TherapyMonitoring;
use App\Models\Queue;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function getDashboardAnalytics(Request $request)
    {
        $user = $request->user();
        $now    = Carbon::now('Asia/Jakarta');
        $today  = $now->copy()->startOfDay();
        $period = in_array($request->query('period'), ['week', 'month', 'year'])
            ? $request->query('period')
            : 'week';

        $data = match($user->role) {
            'admin'   => $this->adminData($today, $now, $period),
            'dokter'  => $this->dokterData($user, $today, $now, $period),
            'terapis' => $this->terapisData($user, $today, $now),
            default   => null,
        };

        if ($data === null) {
            return response()->json(['success' => false, 'message' => 'Role tidak dikenal'], 403);
        }

        return response()->json([
            'success'          => true,
            'data'             => $data,
            'today_formatted'  => $now->locale('id')->isoFormat('dddd, D MMMM YYYY'),
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    /* ─── Admin ─────────────────────────────────────────────── */
    private function adminData($today, $now, string $period = 'week'): array
    {
        $totalQueues   = Queue::whereDate('created_at', $today)->count();
        $waiting       = Queue::whereDate('created_at', $today)->where('status', 'waiting')->count();
        $calling       = Queue::whereDate('created_at', $today)->whereIn('status', ['calling', 'serving'])->count();
        $completed     = Queue::whereDate('created_at', $today)->where('status', 'completed')->count();
        $totalAssess   = MedicalAssessment::count();
        $assessToday   = MedicalAssessment::whereDate('created_at', $today)->count();
        $activeTherapy = Therapy::where('status', 'berjalan')->count();

        $visitTrend = $this->buildVisitTrend($period, $now, function($start, $end) {
            return Queue::whereBetween('created_at', [$start, $end])->count();
        });

        // Distribusi diagnosis (top 5)
        $diagDist = MedicalAssessment::selectRaw('diagnosis, COUNT(*) as total')
            ->whereNotNull('diagnosis')
            ->groupBy('diagnosis')
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('total', 'diagnosis')
            ->toArray();

        // Aktivitas terbaru (5 assessment terbaru)
        $recentActivities = MedicalAssessment::with('patient')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($a) => [
                'time'     => $a->created_at->format('H:i'),
                'patient'  => ['name' => $a->patient->nama_lengkap ?? 'Unknown'],
                'activity' => 'Assessment Medis',
                'status'   => 'Selesai',
            ]);

        return [
            'total_patients'    => Patient::count(),
            'patients_today'    => Patient::whereDate('created_at', $today)->count(),
            'total_queues_today'=> $totalQueues,
            'waiting_queues'    => $waiting,
            'calling_queues'    => $calling,
            'completed_queues'  => $completed,
            'total_assessments' => $totalAssess,
            'assessments_today' => $assessToday,
            'active_therapies'  => $activeTherapy,
            'attendance_rate'   => $totalQueues > 0 ? round(($completed / $totalQueues) * 100) : 0,
            'visit_trend'       => $visitTrend,
            'diagnosis_dist'    => $diagDist,
            'recent_activities' => $recentActivities,
        ];
    }

    /* ─── Dokter ─────────────────────────────────────────────── */
    private function dokterData($user, $today, $now, string $period = 'week'): array
    {
        $myPatients  = MedicalAssessment::where('id_pengguna', $user->id)->distinct('id_pasien')->count('id_pasien');
        $assessToday = MedicalAssessment::where('id_pengguna', $user->id)->whereDate('created_at', $today)->count();
        $waiting     = Queue::whereDate('created_at', $today)->where('status', 'waiting')->count();
        $completed   = Queue::whereDate('created_at', $today)->where('status', 'completed')->count();

        $visitTrend = $this->buildVisitTrend($period, $now, function($start, $end) use ($user) {
            return MedicalAssessment::where('id_pengguna', $user->id)
                ->whereBetween('created_at', [$start, $end])
                ->count();
        });

        $recentActivities = MedicalAssessment::where('id_pengguna', $user->id)
            ->with('patient')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($a) => [
                'time'     => $a->created_at->format('H:i'),
                'patient'  => ['name' => $a->patient->nama_lengkap ?? 'Unknown'],
                'activity' => 'Assessment Medis',
                'status'   => 'Selesai',
            ]);

        return [
            'summary' => [
                'total_patients'    => $myPatients,
                'assessments_today' => $assessToday,
                'waiting_queues'    => $waiting,
                'completed_queues'  => $completed,
                'attendance_rate'   => $myPatients > 0 ? round(($assessToday / max($myPatients, 1)) * 100) : 0,
            ],
            'visit_trend'       => $visitTrend,
            'recent_activities' => $recentActivities,
        ];
    }

    /* ─── Helper: build visit trend by period ─────────────────── */
    private function buildVisitTrend(string $period, $now, callable $countFn): array
    {
        $trend = [];

        if ($period === 'week') {
            // 7 hari terakhir, per hari
            for ($i = 6; $i >= 0; $i--) {
                $d     = $now->copy()->subDays($i)->startOfDay();
                $end   = $d->copy()->endOfDay();
                $trend[] = [
                    'date'     => $d->format('Y-m-d'),
                    'label'    => $d->locale('id')->isoFormat('D MMM'),
                    'patients' => $countFn($d, $end),
                ];
            }
        } elseif ($period === 'month') {
            // 30 hari terakhir, per hari
            for ($i = 29; $i >= 0; $i--) {
                $d     = $now->copy()->subDays($i)->startOfDay();
                $end   = $d->copy()->endOfDay();
                $trend[] = [
                    'date'     => $d->format('Y-m-d'),
                    'label'    => $d->locale('id')->isoFormat('D MMM'),
                    'patients' => $countFn($d, $end),
                ];
            }
        } elseif ($period === 'year') {
            // 12 bulan terakhir, per bulan
            for ($i = 11; $i >= 0; $i--) {
                $d     = $now->copy()->subMonths($i)->startOfMonth();
                $end   = $d->copy()->endOfMonth();
                $trend[] = [
                    'date'     => $d->format('Y-m-d'),
                    'label'    => $d->locale('id')->isoFormat('MMM YYYY'),
                    'patients' => $countFn($d, $end),
                ];
            }
        }

        return $trend;
    }

    /* ─── Terapis ─────────────────────────────────────────────── */
    private function terapisData($user, $today, $now): array
    {
        $mySessions    = TherapyMonitoring::where('id_terapis', $user->id)->count();
        $sessionsToday = TherapyMonitoring::where('id_terapis', $user->id)->whereDate('tanggal_sesi', $today)->count();
        $activeTherapy = Therapy::where('id_terapis', $user->id)->where('status', 'berjalan')->count();
        $hadirTotal    = TherapyMonitoring::where('id_terapis', $user->id)->where('kehadiran', 'hadir')->count();
        $attendRate    = $mySessions > 0 ? round(($hadirTotal / $mySessions) * 100) : 0;

        $recentActivities = TherapyMonitoring::where('id_terapis', $user->id)
            ->with('patient')
            ->orderByDesc('tanggal_sesi')
            ->limit(5)
            ->get()
            ->map(fn($m) => [
                'time'     => optional($m->tanggal_sesi)->format('H:i') ?? '-',
                'patient'  => ['name' => optional($m->patient)->nama_lengkap ?? 'Unknown'],
                'activity' => 'Sesi Terapi',
                'status'   => ucfirst($m->kehadiran ?? '-'),
            ]);

        return [
            'summary' => [
                'my_sessions'       => $mySessions,
                'sessions_today'    => $sessionsToday,
                'active_therapies'  => $activeTherapy,
                'attendance_rate'   => $attendRate,
            ],
            'recent_activities' => $recentActivities,
        ];
    }
}
