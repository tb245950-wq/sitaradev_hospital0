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

        return response()->json([
            'success' => true,
            'data' => $queues
        ], 200);
    }

    /**
     * FR-06: Daftarkan Pasien ke Antrian
     * Auto-generate nomor antrian berdasarkan jenis layanan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_pasien' => 'required|exists:patients,id_pasien',
            'jenis_layanan' => 'required|in:assessment,terapi',
            'prioritas' => 'sometimes|integer|min:0|max:10',
            'catatan' => 'nullable|string',
        ]);

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

        // Buat antrian - SET SECARA EKSPLISIT
        $queue = Queue::create([
            'id_pasien' => (int) $validated['id_pasien'],
            'id_pengguna' => Auth::id(),
            'nomor_antrian' => $nomorAntrian,
            'jenis_layanan' => $validated['jenis_layanan'],
            'status' => 'menunggu',
            'prioritas' => $validated['prioritas'] ?? 0,
            'waktu_daftar' => now(),
            'catatan' => $validated['catatan'] ?? null,
        ]);

        $queue->load(['patient:id_pasien,nama_lengkap,nrm', 'user:id,name,role']);

        return response()->json([
            'success' => true,
            'message' => 'Pasien berhasil didaftarkan ke antrian.',
            'data' => $queue
        ], 201);
    }

    /**
     * Detail Antrian
     * ⚠️ DIPERBAIKI: Menggunakan manual query untuk bypass Route Model Binding
     */
    public function show($id_antrian)
    {
        $queue = Queue::where('id_antrian', $id_antrian)
            ->with(['patient:id_pasien,nama_lengkap,nrm', 'user:id,name,role', 'assessments'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $queue
        ], 200);
    }

    /**
     * FR-06: Update Status Antrian
     * Panggil pasien, tandai selesai, atau tidak hadir
     * ⚠️ DIPERBAIKI: Menggunakan manual query untuk bypass Route Model Binding
     */
    public function update(Request $request, $id_antrian)
    {
        $queue = Queue::where('id_antrian', $id_antrian)->firstOrFail();

        $validated = $request->validate([
            'status' => 'sometimes|in:menunggu,dipanggil,selesai,tidak_hadir',
            'prioritas' => 'sometimes|integer|min:0|max:10',
            'catatan' => 'nullable|string',
        ]);

        // Update status dan timestamp yang relevan
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
        $queue->load(['patient:id_pasien,nama_lengkap,nrm', 'user:id,name,role']);

        return response()->json([
            'success' => true,
            'message' => 'Status antrian berhasil diperbarui.',
            'data' => $queue
        ], 200);
    }

    /**
     * Hapus Antrian
     * ⚠️ DIPERBAIKI: Menggunakan manual query untuk bypass Route Model Binding
     */
    public function destroy($id_antrian)
    {
        $queue = Queue::where('id_antrian', $id_antrian)->firstOrFail();

        // Tidak boleh hapus antrian yang sedang dipanggil atau selesai
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
     * Generate Nomor Antrian Otomatis
     * Format: A001 untuk assessment, T001 untuk terapi
     */
    private function generateNomorAntrian(string $jenisLayanan): int
    {
        $today = today();

        $lastQueue = Queue::where('jenis_layanan', $jenisLayanan)
            ->whereDate('waktu_daftar', $today)
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

        $nextQueue->load(['patient:id_pasien,nama_lengkap,nrm', 'user:id,name,role']);

        return response()->json([
            'success' => true,
            'message' => 'Pasien berikutnya telah dipanggil.',
            'data' => $nextQueue
        ], 200);
    }
}