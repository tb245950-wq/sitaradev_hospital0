<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    /**
     * FR-06: List Antrian dengan Filter
     * Mendukung filter berdasarkan status, jenis_layanan, dan tanggal
     */
    public function index(Request $request)
    {
        $query = Queue::with(['patient:id_pasien,nama_lengkap,nrm', 'user:id,name,role']);

        // Filter berdasarkan status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan jenis layanan
        if ($request->has('jenis_layanan') && $request->jenis_layanan != '') {
            $query->where('jenis_layanan', $request->jenis_layanan);
        }

        // Filter berdasarkan tanggal
        if ($request->has('tanggal') && $request->tanggal != '') {
            $query->whereDate('waktu_daftar', $request->tanggal);
        }

        // Filter berdasarkan pasien (search by NRM atau nama)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('patient', function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nrm', 'like', "%{$search}%");
            });
        }

        // Urutkan berdasarkan prioritas (desc) lalu nomor antrian (asc)
        $queues = $query->orderBy('prioritas', 'desc')
                        ->orderBy('nomor_antrian', 'asc')
                        ->paginate(15);

        return \App\Http\Resources\QueueResource::collection($queues)->additional(['success' => true]);
    }

    /**
     * FR-06: Daftarkan Pasien ke Antrian
     */
    public function store(\App\Http\Requests\StoreQueueRequest $request)
    {
        $validated = $request->validated();

        // Cek duplikasi
        $existingQueue = Queue::where('id_pasien', $validated['id_pasien'])
            ->whereDate('waktu_daftar', today())
            ->whereIn('status', ['menunggu', 'dipanggil'])
            ->first();

        if ($existingQueue) {
            return response()->json([
                'success' => false,
                'message' => 'Pasien sudah terdaftar dalam antrian hari ini.',
                'data' => $existingQueue
            ], 409);
        }

        // Generate nomor antrian
        $nomorAntrian = $this->generateNomorAntrian($validated['jenis_layanan']);

        $queue = Queue::create([
            'id_pasien' => $validated['id_pasien'],
            'id_pengguna' => Auth::id(),
            'nomor_antrian' => $nomorAntrian,
            'jenis_layanan' => $validated['jenis_layanan'],
            'status' => 'menunggu',
            'prioritas' => $validated['prioritas'] ?? 0,
            'waktu_daftar' => now(),
            'catatan' => $validated['catatan'] ?? null,
        ]);

        // Create activity log
        \App\Models\ActivityLog::create([
            'id_pasien' => $validated['id_pasien'],
            'id_pengguna' => Auth::id(),
            'activity_type' => 'Registrasi Antrian',
            'department' => $validated['jenis_layanan'] === 'terapi' ? 'Terapi' : 'Umum',
            'status' => 'Baru',
            'description' => 'Nomor Antrian: Q' . str_pad($nomorAntrian, 3, '0', STR_PAD_LEFT)
        ]);

        $queue->load(['patient', 'user']);

        return response()->json([
            'success' => true,
            'message' => 'Pasien berhasil didaftarkan ke antrian.',
            'data' => new \App\Http\Resources\QueueResource($queue)
        ], 201);
    }

    /**
 * Detail Antrian
 */
public function show(Queue $queue)
{
    $queue->load(['patient', 'user', 'assessments']);

    return response()->json([
        'success' => true,
        'data' => new \App\Http\Resources\QueueResource($queue)
    ], 200);
}

/**
 * Update Status Antrian
 */
public function update(Request $request, Queue $queue)
{
    $validated = $request->validate([
        'status' => 'sometimes|in:menunggu,dipanggil,selesai,tidak_hadir',
        'prioritas' => 'sometimes|integer|min:0|max:10',
        'catatan' => 'nullable|string',
    ]);

    if (isset($validated['status'])) {
        $queue->status = $validated['status'];

        if ($validated['status'] === 'dipanggil') {
            $queue->waktu_panggil = now();
        } elseif ($validated['status'] === 'selesai') {
            $queue->waktu_selesai = now();
        }
    }

    if (isset($validated['prioritas'])) {
        $queue->prioritas = $validated['prioritas'];
    }

    if (isset($validated['catatan'])) {
        $queue->catatan = $validated['catatan'];
    }

    $queue->save();
    $queue->load(['patient', 'user']);

    return response()->json([
        'success' => true,
        'message' => 'Status antrian berhasil diperbarui.',
        'data' => new \App\Http\Resources\QueueResource($queue)
    ], 200);
}

/**
 * Hapus Antrian
 */
public function destroy(Queue $queue)
{
    if (in_array($queue->status, ['dipanggil', 'selesai'])) {
        return response()->json([
            'success' => false,
            'message' => 'Tidak dapat menghapus antrian yang sedang dipanggil atau sudah selesai.'
        ], 400);
    }

    $queue->delete();

    return response()->json([
        'success' => true,
        'message' => 'Antrian berhasil dihapus.'
    ], 200);
}

    /**
     * GET /api/queues/stats
     */
    public function stats()
    {
        $today = today();
        
        return response()->json([
            'success' => true,
            'data' => [
                'waiting' => Queue::where('status', 'menunggu')->whereDate('waktu_daftar', $today)->count(),
                'calling' => Queue::where('status', 'dipanggil')->whereDate('waktu_daftar', $today)->count(),
                'completed' => Queue::where('status', 'selesai')->whereDate('waktu_daftar', $today)->count(),
                'high_priority' => Queue::where('prioritas', '>', 5)
                    ->where('status', 'menunggu')
                    ->whereDate('waktu_daftar', $today)
                    ->count()
            ]
        ]);
    }

    /**
     * Generate Nomor Antrian Otomatis
     * Format: Q001, Q002, dst
     */
    private function generateNomorAntrian(string $jenisLayanan): int
    {
        $today = today();

        $lastQueue = Queue::whereDate('waktu_daftar', $today)
            ->orderBy('nomor_antrian', 'desc')
            ->first();

        return $lastQueue ? $lastQueue->nomor_antrian + 1 : 1;
    }

    /**
     * Panggil Pasien Berikutnya
     * Endpoint khusus untuk memanggil pasien berikutnya dalam antrian
     */
    public function callNext(Request $request)
    {
        $jenisLayanan = $request->input('jenis_layanan', 'assessment');

        // Cari pasien berikutnya dengan prioritas tertinggi
        $nextQueue = Queue::where('jenis_layanan', $jenisLayanan)
            ->where('status', 'menunggu')
            ->orderBy('prioritas', 'desc')
            ->orderBy('nomor_antrian', 'asc')
            ->first();

        if (!$nextQueue) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada antrian yang menunggu.'
            ], 404);
        }

        // Update status menjadi dipanggil
        $nextQueue->status = 'dipanggil';
        $nextQueue->waktu_panggil = now();
        $nextQueue->save();

        // Create activity log
        \App\Models\ActivityLog::create([
            'id_pasien' => $nextQueue->id_pasien,
            'id_pengguna' => Auth::id(),
            'activity_type' => 'Panggil Antrian',
            'department' => $nextQueue->jenis_layanan === 'terapi' ? 'Terapi' : 'Umum',
            'status' => 'Berlangsung',
            'description' => 'Memanggil: Q' . str_pad($nextQueue->nomor_antrian, 3, '0', STR_PAD_LEFT)
        ]);

        $nextQueue->load(['patient:id_pasien,nama_lengkap,nrm', 'user:id,name,role']);

        return response()->json([
            'success' => true,
            'message' => 'Pasien berikutnya telah dipanggil.',
            'data' => $nextQueue
        ], 200);
    }
}