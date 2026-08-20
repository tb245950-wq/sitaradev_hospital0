<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientPortalController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        // Active Queue today
        $activeQueue = Queue::where('id_pasien', $user->id)
            ->whereDate('created_at', today())
            ->whereIn('status', ['menunggu', 'dipanggil'])
            ->first();
            
        // Total Assessments
        $totalAssessments = \App\Models\MedicalAssessment::where('id_pasien', $user->id)->count();
        
        // Active Therapy Programs
        $activeTherapies = \App\Models\Therapy::where('id_pasien', $user->id)
            ->where('status', 'berjalan')
            ->count();
            
        // Upcoming Therapy Sessions this month
        $upcomingSessions = \App\Models\TherapyMonitoring::where('id_pasien', $user->id)
            ->whereMonth('tanggal_sesi', now()->month)
            ->whereYear('tanggal_sesi', now()->year)
            ->where('kehadiran', 'belum_hadir') // assuming this status exists or similar
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'activeQueue' => $activeQueue ? 1 : 0,
                'queueNumber' => $activeQueue ? $activeQueue->nomor_antrian : '',
                'upcomingTherapy' => $upcomingSessions,
                'totalAssessment' => $totalAssessments,
                'activeTherapy' => $activeTherapies
            ]
        ]);
    }

    public function getDoctors()
    {
        $doctors = User::where('role', 'dokter')->get(['id', 'name']);
        return response()->json(['success' => true, 'data' => $doctors]);
    }

    public function bookQueue(Request $request)
    {
        $user = $request->user();
        
        // Get patient record from patients table
        $patient = \App\Models\Patient::where('user_id', $user->id)->first();
        
        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Data pasien tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'poli' => 'required|in:umum,psikolog,terapi,tumbuh_kembang',
            'doctor_id' => 'required|exists:users,id',
            'type' => 'required|in:consultation,assessment,therapy,control',
            'priority' => 'sometimes|in:normal,urgent,emergency',
            'notes' => 'nullable|string',
        ]);

        $doctor = User::where('id', $validated['doctor_id'])
            ->where('role', 'dokter')
            ->where('status', 'active')
            ->first();

        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Dokter tidak tersedia'
            ], 400);
        }

        $number = $this->generateQueueNumber();

        $queue = Queue::create([
            'nomor_antrian' => $number,
            'queue_number' => 'A' . str_pad($number, 3, '0', STR_PAD_LEFT),
            'id_pasien' => $patient->id_pasien,
            'poli' => $validated['poli'],
            'doctor_id' => $validated['doctor_id'],
            'booked_by' => $user->id,
            'status' => 'waiting',
            'jenis_layanan' => $validated['type'],
            'waktu_daftar' => now(),
            'priority' => $validated['priority'] ?? 'normal',
            'prioritas' => 0,
            'catatan' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking antrian berhasil',
            'data' => $queue
        ], 201);
    }

    private function generateQueueNumber()
    {
        $lastQueue = Queue::whereDate('created_at', today())
            ->orderBy('id_antrian', 'desc')
            ->first();

        return $lastQueue ? intval($lastQueue->nomor_antrian) + 1 : 1;
    }

    public function getMyQueues()
    {
        $queues = Queue::where('id_pasien', Auth::id())->with('doctor')->get();
        return response()->json(['success' => true, 'data' => $queues]);
    }

    public function cancelQueue($id)
    {
        $queue = Queue::where('id_antrian', $id)->where('id_pasien', Auth::id())->firstOrFail();
        $queue->update(['status' => 'dibatalkan']);
        return response()->json(['success' => true, 'message' => 'Antrian dibatalkan.']);
    }

    public function schedule()
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function history()
    {
        return response()->json(['success' => true, 'data' => []]);
    }
}
