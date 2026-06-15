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
            'password' => Hash::make($validated['password']),
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
            'password' => Hash::make($validated['password'])
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
}