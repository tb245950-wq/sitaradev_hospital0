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
        $now = Carbon::now('Asia/Jakarta');
        
        // Gunakan tanggal terbaru dari database jika hari ini tidak ada data
        $latestDate = MedicalAssessment::max('created_at');
        $today = $latestDate ? Carbon::parse($latestDate)->startOfDay() : $now->copy()->startOfDay();
        
        if ($user->role === 'admin') {
            return response()->json([
                'success' => true,
                'data' => $this->getAdminAnalytics($today, $now),
                'today_formatted' => $now->isoFormat('dddd, D MMMM YYYY')
            ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        } elseif ($user->role === 'dokter') {
            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $this->getDoctorStats($user, $today),
                    'recent_activities' => $this->getDoctorActivities($user),
                    'visit_trend' => $this->getDoctorVisitTrend($user, $now)
                ],
                'today_formatted' => $now->isoFormat('dddd, D MMMM YYYY')
            ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        } elseif ($user->role === 'terapis') {
            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $this->getTherapistStats($user, $today),
                    'recent_activities' => $this->getTherapistActivities($user)
                ],
                'today_formatted' => $now->isoFormat('dddd, D MMMM YYYY')
            ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        }
        
        return response()->json(['success' => false, 'message' => 'Role tidak dikenal'], 403);
    }

    private function getAdminAnalytics($today, $now)
    {
        return [
            'total_patients' => Patient::count(),
            'patients_today' => Patient::whereDate('created_at', $today)->count(),
            'total_queues_today' => Queue::whereDate('created_at', $today)->count(),
            'waiting_queues' => Queue::whereDate('created_at', $today)->where('status', 'waiting')->count(),
            'calling_queues' => Queue::whereDate('created_at', $today)->where('status', 'calling')->count(),
            'completed_queues' => Queue::whereDate('created_at', $today)->where('status', 'completed')->count(),
            'total_assessments' => MedicalAssessment::count(),
            'assessments_today' => MedicalAssessment::whereDate('created_at', $today)->count(),
            'active_therapies' => Therapy::where('status', 'berjalan')->count(),
            'attendance_rate' => 100 // Simplified for brevity
        ];
    }

    private function getDoctorStats($user, $today)
    {
        return [
            'total_patients' => MedicalAssessment::where('id_pengguna', $user->id)->distinct('id_pasien')->count(),
            'assessments_today' => MedicalAssessment::where('id_pengguna', $user->id)->whereDate('created_at', $today)->count(),
            'waiting_queues' => Queue::where('doctor_id', $user->id)->where('status', 'waiting')->count(),
            'completed_queues' => Queue::where('doctor_id', $user->id)->where('status', 'completed')->count(),
            'attendance_rate' => 100
        ];
    }
    
    private function getDoctorActivities($user)
    {
        return MedicalAssessment::where('id_pengguna', $user->id)
            ->with('patient')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($a) => [
                'time' => $a->created_at->format('H:i'),
                'patient' => ['name' => $a->patient->nama_lengkap ?? 'Unknown'],
                'activity' => 'Assessment Medis',
                'status' => 'Selesai'
            ]);
    }

    private function getDoctorVisitTrend($user, $now)
    {
        $trends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->startOfDay();
            $trends[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->isoFormat('D MMM'),
                'patients' => MedicalAssessment::where('id_pengguna', $user->id)->whereDate('created_at', $date)->count()
            ];
        }
        return $trends;
    }

    private function getTherapistStats($user, $today)
    {
        return [
            'my_sessions' => TherapyMonitoring::where('id_terapis', $user->id)->count(),
            'sessions_today' => TherapyMonitoring::where('id_terapis', $user->id)->whereDate('tanggal_sesi', $today)->count(),
            'active_therapies' => Therapy::where('id_terapis', $user->id)->where('status', 'berjalan')->count(),
            'attendance_rate' => 100
        ];
    }

    private function getTherapistActivities($user)
    {
        return TherapyMonitoring::where('id_terapis', $user->id)
            ->with('patient')
            ->orderBy('tanggal_sesi', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($m) => [
                'time' => $m->tanggal_sesi->format('H:i'),
                'patient' => ['name' => $m->patient->nama_lengkap ?? 'Unknown'],
                'activity' => 'Sesi Terapi',
                'status' => 'Selesai'
            ]);
    }
}
