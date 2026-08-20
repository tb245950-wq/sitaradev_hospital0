<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users (Admin only)
     */
    public function index(Request $request)
    {
        // Authorization: Only admin can access
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya administrator yang dapat mengelola user.'
            ], 403);
        }

        // Get filters from request
        $search = $request->input('search', '');
        $role = $request->input('role');
        $status = $request->input('status');

        // Build query
        $query = User::query();

        // Apply filters
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%")
                  ->orWhere('nip', 'ILIKE', "%{$search}%");
            });
        }

        if ($role) {
            $query->where('role', $role);
        }

        if ($status) {
            $query->where('status', $status);
        }

        // Get paginated results
        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $users
        ], 200);
    }

    /**
     * Store a newly created user (Admin only)
     */
    public function store(Request $request)
    {
        // Authorization
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        // Validation
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'nip' => 'nullable|string|unique:users,nip',
            'role' => 'required|in:admin,dokter,terapis',
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'status' => 'sometimes|in:active,inactive,suspended',
        ]);

        // Auto-generate NIP if not provided
        if (!isset($validated['nip']) || empty($validated['nip'])) {
            $year = date('Y');
            $count = \App\Models\User::whereYear('created_at', $year)->count() + 1;
            $validated['nip'] = $year . str_pad($count, 4, '0', STR_PAD_LEFT);
        }

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'nip' => $validated['nip'] ?? null,
            'role' => $validated['role'],
            'password' => $validated['password'],
            'status' => $validated['status'] ?? 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil ditambahkan',
            'data' => $user
        ], 201);
    }

    /**
     * Display the specified user (Admin only)
     */
    public function show(Request $request, User $user)
    {
        // Authorization
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ], 200);
    }

    /**
     * Update the specified user (Admin only)
     */
    public function update(Request $request, User $user)
    {
        // Authorization
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        // Prevent admin from updating themselves through this method
        if ($user->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Gunakan profil settings untuk update akun sendiri'
            ], 400);
        }

        // Validation
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($user->id)],
            'nip' => ['nullable', 'string', Rule::unique('users')->ignore($user->id)],
            'role' => 'sometimes|required|in:admin,dokter,terapis',
            'status' => 'sometimes|in:active,inactive,suspended',
        ]);

        // Update user
        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diupdate',
            'data' => $user
        ], 200);
    }

    /**
     * Update user status (Activate/Deactivate/Suspend)
     */
    public function updateStatus(Request $request, User $user)
    {
        // Authorization
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        // Prevent admin from deactivating themselves
        if ($user->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat mengubah status akun sendiri'
            ], 400);
        }

        // Validation
        $validated = $request->validate([
            'status' => 'required|in:active,inactive,suspended'
        ]);

        // Update status
        $user->update(['status' => $validated['status']]);

        $message = match($validated['status']) {
            'active' => 'User berhasil diaktifkan',
            'inactive' => 'User berhasil dinonaktifkan',
            'suspended' => 'User berhasil ditangguhkan',
        };

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $user
        ], 200);
    }

    /**
     * Reset user password (Admin only)
     */
    public function resetPassword(Request $request, User $user)
    {
        // Authorization
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        // Validation
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Update password
        $user->update([
            'password' => $validated['password']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password user berhasil direset'
        ], 200);
    }

    /**
     * Remove the specified user (Soft delete - Admin only)
     */
    public function destroy(Request $request, User $user)
    {
        // Authorization
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        // Prevent admin from deleting themselves
        if ($user->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat menghapus akun sendiri'
            ], 400);
        }

        // Soft delete
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus'
        ], 200);
    }

    /**
     * Get daftar pasien terdaftar (Admin only)
     * Menampilkan pasien beserta info user account mereka
     */
    public function getPatients(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $search = $request->input('search', '');

        $query = \App\Models\Patient::with('user:id,email,created_at,last_login_at')
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'ILIKE', "%{$search}%")
                  ->orWhere('nrm', 'ILIKE', "%{$search}%");
            });
        }

        $patients = $query->paginate(20);

        $data = $patients->map(function ($p) {
            return [
                'id'           => $p->id_pasien,
                'nrm'          => $p->nrm,
                'nama'         => $p->nama_lengkap,
                'jenis_kelamin'=> $p->jenis_kelamin,
                'tanggal_lahir'=> $p->tanggal_lahir?->format('Y-m-d'),
                'nama_wali'    => $p->nama_wali,
                'created_at'   => $p->created_at,
                // Info akun portal pasien
                'akun' => $p->user ? [
                    'email'          => $p->user->email,
                    'password_info'  => 'password123', // default password untuk pasien baru
                    'last_login_at'  => $p->user->last_login_at,
                    'created_at'     => $p->user->created_at,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
            'meta'    => [
                'current_page' => $patients->currentPage(),
                'last_page'    => $patients->lastPage(),
                'total'        => $patients->total(),
            ],
        ]);
    }

    /**
     * Get riwayat login/logout semua user (Admin only)
     */
    public function getLoginHistory(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $limit  = $request->input('limit', 50);
        $search = $request->input('search', '');

        $query = \App\Models\LoginHistory::with('user:id,name,email,role')
            ->orderByDesc('login_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'ILIKE', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'ILIKE', "%{$search}%"));
            });
        }

        $logs = $query->limit($limit)->get()->map(function ($log) {
            return [
                'id'             => $log->id,
                'email'          => $log->email,
                'user'           => $log->user ? [
                    'name' => $log->user->name,
                    'role' => $log->user->role,
                ] : null,
                'ip_address'     => $log->ip_address,
                'browser'        => $log->browser,
                'os'             => $log->os,
                'success'        => $log->success,
                'failure_reason' => $log->failure_reason,
                'login_at'       => $log->login_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $logs,
        ]);
    }
}