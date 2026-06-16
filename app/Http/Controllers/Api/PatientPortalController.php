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
        $myQueues = Queue::where('id_pasien', $user->id)->count();
        
        return response()->json([
            'success' => true,
            'data' => [
                'name' => $user->name,
                'total_queues' => $myQueues,
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
        $patientId = $user->id;

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

        $queueType = match($validated['type']) {
            'consultation' => 'consultation',
            'assessment' => 'assessment',
            'therapy' => 'therapy',
            'control' => 'consultation',
        };

        $queue = Queue::create([
            'nomor_antrian' => $number,
            'id_pasien' => $patientId,
            'id_pengguna' => $validated['doctor_id'],
            'poli' => $validated['poli'],
            'doctor_id' => $validated['doctor_id'],
            'booked_by' => 'patient',
            'status' => 'menunggu',
            'jenis_layanan' => 'assessment',
            'waktu_daftar' => now(),
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
