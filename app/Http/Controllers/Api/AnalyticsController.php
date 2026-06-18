<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\MedicalAssessment;
use App\Models\Therapy;
use App\Models\TherapyMonitoring;
use App\Models\Queue;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * Get dashboard analytics based on role
     */
    public function getDashboardAnalytics(Request $request)
    {
        $user = $request->user();
        
        // Set timezone ke Asia/Jakarta (WIB)
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->copy()->startOfDay();
        $thisMonth = $now->copy()->startOfMonth();
        
        if ($user->role === 'admin') {
            return response()->json([
                'success' => true,
                'data' => $this->getAdminAnalytics($today, $thisMonth, $now),
                'today_formatted' => $now->isoFormat('dddd, D MMMM YYYY')
            ]);
        } elseif ($user->role === 'dokter') {
            return response()->json([
                'success' => true,
                'data' => $this->getDoctorStats($user, $today),
                'today_formatted' => $now->isoFormat('dddd, D MMMM YYYY')
            ]);
        } elseif ($user->role === 'terapis') {
            return response()->json([
                'success' => true,
                'data' => $this->getTherapistStats($user, $today),
                'today_formatted' => $now->isoFormat('dddd, D MMMM YYYY')
            ]);
        }
        
        return response()->json(['success' => false, 'message' => 'Role tidak dikenal'], 403);
    }

    private function getAdminAnalytics($today, $thisMonth, $now)
    {
        // Total Pasien
        $totalPatients = Patient::count();
        $patientsToday = Patient::whereDate('created_at', $today)->count();
        $patientsThisMonth = Patient::where('created_at', '>=', $thisMonth)->count();
        
        // Antrian hari ini
        $todayQueues = Queue::whereDate('created_at', $today)->get();
        $totalQueuesToday = $todayQueues->count();
        $waitingQueues = $todayQueues->where('status', 'menunggu')->count();
        $callingQueues = $todayQueues->where('status', 'dipanggil')->count();
        $completedQueues = $todayQueues->where('status', 'selesai')->count();
        
        // Assessment
        $totalAssessments = MedicalAssessment::count();
        $assessmentsToday = MedicalAssessment::whereDate('created_at', $today)->count();
        
        // Terapi
        $totalTherapies = Therapy::where('status', 'berjalan')->count();
        
        // Monitoring & Attendance
        $totalSessions = TherapyMonitoring::count();
        $sessionsCompleted = TherapyMonitoring::where('kehadiran', 'hadir')->count();
        $attendanceRate = $totalSessions > 0 ? round(($sessionsCompleted / $totalSessions) * 100, 1) : 0;
        
        // Tren kunjungan 7 hari terakhir
        $visitTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->startOfDay();
            $visitTrend[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->isoFormat('D MMM'),
                'patients' => Patient::whereDate('created_at', $date)->count(),
                'queues' => Queue::whereDate('created_at', $date)->count(),
                'assessments' => MedicalAssessment::whereDate('created_at', $date)->count()
            ];
        }
        
        // Distribusi diagnosis (Top 5)
        $diagnosisDistribution = MedicalAssessment::select('diagnosis', DB::raw('COUNT(*) as count'))
            ->whereNotNull('diagnosis')
            ->groupBy('diagnosis')
            ->orderByDesc('count')
            ->limit(20)
            ->get()
            ->map(function($item, $index) {
                $colors = ['#3b82f6', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6'];
                return [
                    'category' => $item->diagnosis,
                    'count' => (int)$item->count,
                    'color' => $colors[$index] ?? '#94a3b8'
                ];
            });
            
        // Aktivitas terbaru
        $recentActivities = ActivityLog::with(['patient', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($log) {
                return [
                    'time' => $log->created_at->format('H:i'),
                    'patient' => [
                        'name' => $log->patient->nama_lengkap ?? 'Unknown',
                        'nik' => $log->patient->nik ?? '-'
                    ],
                    'activity' => $log->activity_type,
                    'staff' => $log->user->name ?? 'System',
                    'poli' => $log->department ?? '-',
                    'status' => $log->status
                ];
            });

        return [
            'summary' => [
                'total_patients' => $totalPatients,
                'patients_today' => $patientsToday,
                'patients_this_month' => $patientsThisMonth,
                'total_queues_today' => $totalQueuesToday,
                'waiting_queues' => $waitingQueues,
                'calling_queues' => $callingQueues,
                'completed_queues' => $completedQueues,
                'total_assessments' => $totalAssessments,
                'assessments_today' => $assessmentsToday,
                'active_therapies' => $totalTherapies,
                'attendance_rate' => $attendanceRate,
            ],
            'visit_trend' => $visitTrend,
            'diagnosis_distribution' => $diagnosisDistribution,
            'recent_activities' => $recentActivities,
            'today' => $today->format('Y-m-d')
        ];
    }

    private function getDoctorStats($user, $today)
    {
        $myAssessments = MedicalAssessment::where('id_pengguna', $user->id)->count();
        $assessmentsToday = MedicalAssessment::where('id_pengguna', $user->id)
            ->whereDate('created_at', $today)->count();
        $myPatients = MedicalAssessment::where('id_pengguna', $user->id)
            ->distinct('id_pasien')->count('id_pasien');
        $waitingQueues = Queue::whereDate('created_at', $today)
            ->where('status', 'menunggu')->count();

        return [
            'summary' => [
                'my_assessments' => $myAssessments,
                'assessments_today' => $assessmentsToday,
                'my_patients' => $myPatients,
                'waiting_queues' => $waitingQueues,
            ],
            'recent_patients' => MedicalAssessment::where('id_pengguna', $user->id)
                ->with('patient')->orderBy('created_at', 'desc')->limit(5)->get()
        ];
    }

    private function getTherapistStats($user, $today)
    {
        $mySessions = TherapyMonitoring::where('id_terapis', $user->id)->count();
        $sessionsToday = TherapyMonitoring::where('id_terapis', $user->id)
            ->whereDate('tanggal_sesi', $today)->count();
        $activeTherapies = Therapy::where('id_terapis', $user->id)
            ->where('status', 'berjalan')->count();

        return [
            'summary' => [
                'my_sessions' => $mySessions,
                'sessions_today' => $sessionsToday,
                'active_therapies' => $activeTherapies,
            ],
            'today_schedule' => TherapyMonitoring::where('id_terapis', $user->id)
                ->whereDate('tanggal_sesi', $today)->with('patient')->get()
        ];
    }

    // Keperluan Legacy/Frontend yang memanggil endpoint individual
    public function getStats(Request $request) { return $this->getDashboardAnalytics($request); }
    public function getVisitTrends(Request $request) { 
        $data = $this->getDashboardAnalytics($request)->original['data'];
        return response()->json(['success' => true, 'data' => $data['visit_trend'] ?? []]);
    }
    public function getDiagnosisDistribution(Request $request) {
        $data = $this->getDashboardAnalytics($request)->original['data'];
        return response()->json(['success' => true, 'data' => $data['diagnosis_distribution'] ?? []]);
    }
    public function getRecentActivities(Request $request) {
        $data = $this->getDashboardAnalytics($request)->original['data'];
        return response()->json(['success' => true, 'data' => $data['recent_activities'] ?? []]);
    }
}
