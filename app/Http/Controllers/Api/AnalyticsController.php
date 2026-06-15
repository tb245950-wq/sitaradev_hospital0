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
     * Get dashboard statistics
     */
    public function getStats(Request $request)
    {
        // Total Patients
        $totalPatients = Patient::count();
        $lastMonthPatients = Patient::whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->count();
        $patientGrowth = $lastMonthPatients > 0 
            ? round((($totalPatients - $lastMonthPatients) / $lastMonthPatients) * 100, 1)
            : 0;
        
        // Today's Therapy Sessions
        $todaySessions = Therapy::whereDate('tanggal_mulai', '<=', Carbon::today())
            ->where(function($q) {
                $q->whereNull('tanggal_selesai')
                  ->orWhereDate('tanggal_selesai', '>=', Carbon::today());
            })
            ->whereIn('status', ['terjadwal', 'berjalan'])
            ->count();
            
        $completedSessions = TherapyMonitoring::whereDate('tanggal_sesi', Carbon::today())
            ->where('kehadiran', 'hadir')
            ->count();
            
        $remainingSessions = max(0, $todaySessions - $completedSessions);
        
        // Waiting List
        $waitingList = Queue::where('status', 'menunggu')->count();
        $highPriority = Queue::where('status', 'menunggu')
            ->where('prioritas', '>', 0) // Assuming prioritas > 0 is high
            ->count();
        
        // Attendance Rate (this week)
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
        
        $attendanceData = TherapyMonitoring::whereBetween('tanggal_sesi', [$weekStart, $weekEnd])
            ->select('kehadiran', DB::raw('count(*) as count'))
            ->groupBy('kehadiran')
            ->get()
            ->pluck('count', 'kehadiran');
            
        $totalScheduled = $attendanceData->sum();
        $attended = $attendanceData->get('hadir', 0);
        
        $attendanceRate = $totalScheduled > 0 
            ? round(($attended / $totalScheduled) * 100, 1)
            : 0;
        
        return response()->json([
            'success' => true,
            'data' => [
                'total_patients' => [
                    'value' => $totalPatients,
                    'trend' => $patientGrowth,
                    'trend_label' => 'bulan ini'
                ],
                'today_sessions' => [
                    'value' => $todaySessions,
                    'completed' => $completedSessions,
                    'remaining' => $remainingSessions
                ],
                'waiting_list' => [
                    'value' => $waitingList,
                    'high_priority' => $highPriority
                ],
                'attendance_rate' => [
                    'value' => $attendanceRate,
                    'period' => 'Minggu Ini'
                ]
            ]
        ]);
    }
    
    /**
     * Get patient visit trends
     */
    public function getVisitTrends(Request $request)
    {
        $period = $request->input('period', 'month');
        
        $query = DB::table('patients')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            );
        
        switch ($period) {
            case 'week':
                $query->whereBetween('created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
                break;
            case 'month':
                $query->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year);
                break;
            case 'year':
                $query->whereYear('created_at', Carbon::now()->year)
                      ->select(
                          DB::raw('DATE_TRUNC(\'month\', created_at) as date'),
                          DB::raw('COUNT(*) as count')
                      );
                break;
        }
        
        $trends = $query->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $trends
        ]);
    }
    
    /**
     * Get diagnosis distribution
     */
    public function getDiagnosisDistribution(Request $request)
    {
        // Since we don't have diagnosis_category, we'll use diagnosis text
        // and try to group common ones or just use top 5
        $distribution = DB::table('medical_assessments')
            ->select('diagnosis as category', DB::raw('COUNT(*) as count'))
            ->whereNotNull('diagnosis')
            ->groupBy('diagnosis')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();
        
        // Color mapping
        $colors = ['#3b82f6', '#22c55e', '#60a5fa', '#a855f7', '#7c3aed'];
        
        $data = $distribution->map(function ($item, $index) use ($colors) {
            return [
                'category' => $item->category,
                'count' => $item->count,
                'color' => $colors[$index] ?? '#94a3b8'
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
    
    /**
     * Get recent activities
     */
    public function getRecentActivities(Request $request)
    {
        $limit = $request->input('limit', 10);
        
        $activities = ActivityLog::with(['patient', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'time' => $item->created_at->format('H:i'),
                    'patient' => [
                        'name' => $item->patient->nama_lengkap ?? 'Unknown',
                        'nik' => $item->patient->nik ?? '-'
                    ],
                    'activity' => $item->activity_type,
                    'staff' => $item->user->name ?? 'System',
                    'poli' => $item->department ?? '-',
                    'status' => $item->status
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    }
    
    /**
     * Get complete dashboard analytics
     */
    public function getDashboardAnalytics(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $this->getStats($request)->original['data'],
                'visit_trends' => $this->getVisitTrends($request)->original['data'],
                'diagnosis_distribution' => $this->getDiagnosisDistribution($request)->original['data'],
                'recent_activities' => $this->getRecentActivities($request)->original['data']
            ]
        ]);
    }
}
