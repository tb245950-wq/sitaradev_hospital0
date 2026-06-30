<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LoginHistory;
use App\Models\SystemAuditLog;
use App\Models\Poli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SuperAdminController extends Controller
{
    /**
     * Dashboard statistics untuk Super Admin
     */
    public function getDashboardStats(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $now = Carbon::now('Asia/Jakarta');
        $today = $now->copy()->startOfDay();

        return response()->json([
            'success' => true,
            'data' => [
                'total_users' => User::where('role', '!=', 'pasien')->count(),
                'active_users' => User::where('role', '!=', 'pasien')->where('status', 'active')->count(),
                'failed_logins_today' => LoginHistory::where('success', false)->whereDate('login_at', $today)->count(),
                'storage_used' => $this->getStorageUsage(),
                'today_formatted' => $now->locale('id')->isoFormat('dddd, D MMMM YYYY'),
            ],
        ]);
    }

    /**
     * Get recent system audit logs
     */
    public function getAuditLogs(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $limit = $request->query('limit', 10);
        $page = $request->query('page', 1);

        $logs = SystemAuditLog::with('user:id,name,email')
            ->orderByDesc('created_at')
            ->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'total' => $logs->total(),
                'per_page' => $logs->perPage(),
            ],
        ]);
    }

    /**
     * Get login history
     */
    public function getLoginHistory(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $limit = $request->query('limit', 20);
        $page = $request->query('page', 1);
        $success = $request->query('success');

        $query = LoginHistory::with('user:id,name,email,role')
            ->orderByDesc('login_at');

        if ($success !== null) {
            $query->where('success', filter_var($success, FILTER_VALIDATE_BOOLEAN));
        }

        $logs = $query->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'total' => $logs->total(),
                'per_page' => $logs->perPage(),
            ],
        ]);
    }

    /**
     * Get failed login attempts
     */
    public function getFailedLogins(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $days = $request->query('days', 7);
        $since = Carbon::now('Asia/Jakarta')->subDays($days)->startOfDay();

        $logs = LoginHistory::where('success', false)
            ->whereBetween('login_at', [$since, Carbon::now('Asia/Jakarta')])
            ->orderByDesc('login_at')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    /**
     * User Management - Get all users
     */
    public function getUsers(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $role = $request->query('role');
        $status = $request->query('status');

        $query = User::where('role', '!=', 'pasien');

        if ($role) {
            $query->where('role', $role);
        }
        if ($status) {
            $query->where('status', $status);
        }

        $users = $query->select('id', 'name', 'email', 'role', 'nip', 'status', 'last_login_at', 'created_at')
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Create new user
     */
    public function createUser(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'role' => 'required|in:admin,dokter,terapis',
            'nip' => 'nullable|string|unique:users',
            'password' => 'required|string|min:8',
        ]);

        try {
            $newUser = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'nip' => $validated['nip'] ?? null,
                'password' => Hash::make($validated['password']),
                'status' => 'active',
            ]);

            SystemAuditLog::create([
                'user_id' => $user->id,
                'module' => 'user',
                'action' => 'create',
                'description' => "Created user: {$newUser->name} ({$newUser->email})",
                'ip_address' => $request->ip(),
                'new_values' => [
                    'id' => $newUser->id,
                    'name' => $newUser->name,
                    'email' => $newUser->email,
                    'role' => $newUser->role,
                ],
                'status' => 'success',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $newUser,
            ], 201);
        } catch (\Exception $e) {
            SystemAuditLog::create([
                'user_id' => $user->id,
                'module' => 'user',
                'action' => 'create',
                'description' => "Failed to create user: {$validated['email']}",
                'ip_address' => $request->ip(),
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to create user'], 500);
        }
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, User $userToUpdate)
    {
        $user = $request->user();
        if ($user->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $userToUpdate->id,
            'role' => 'sometimes|in:admin,dokter,terapis',
            'nip' => 'sometimes|nullable|string|unique:users,nip,' . $userToUpdate->id,
            'status' => 'sometimes|in:active,inactive,suspended',
        ]);

        $oldValues = $userToUpdate->only(['name', 'email', 'role', 'status', 'nip']);

        try {
            $userToUpdate->update($validated);

            SystemAuditLog::create([
                'user_id' => $user->id,
                'module' => 'user',
                'action' => 'update',
                'description' => "Updated user: {$userToUpdate->name} ({$userToUpdate->email})",
                'ip_address' => $request->ip(),
                'old_values' => $oldValues,
                'new_values' => $userToUpdate->only(array_keys($validated)),
                'status' => 'success',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $userToUpdate,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update user'], 500);
        }
    }

    /**
     * Delete user
     */
    public function deleteUser(Request $request, User $userToDelete)
    {
        $user = $request->user();
        if ($user->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($userToDelete->id === $user->id) {
            return response()->json(['error' => 'Cannot delete yourself'], 400);
        }

        try {
            $deletedUserData = $userToDelete->only(['id', 'name', 'email', 'role']);
            $userToDelete->delete();

            SystemAuditLog::create([
                'user_id' => $user->id,
                'module' => 'user',
                'action' => 'delete',
                'description' => "Deleted user: {$deletedUserData['name']} ({$deletedUserData['email']})",
                'ip_address' => $request->ip(),
                'old_values' => $deletedUserData,
                'status' => 'success',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete user'], 500);
        }
    }

    /**
     * Reset user password
     */
    public function resetUserPassword(Request $request, User $userToReset)
    {
        $user = $request->user();
        if ($user->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'password' => 'required|string|min:8',
        ]);

        try {
            $userToReset->update(['password' => Hash::make($validated['password'])]);

            SystemAuditLog::create([
                'user_id' => $user->id,
                'module' => 'user',
                'action' => 'reset_password',
                'description' => "Reset password for user: {$userToReset->name} ({$userToReset->email})",
                'ip_address' => $request->ip(),
                'status' => 'success',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to reset password'], 500);
        }
    }

    /**
     * Get Poli/Layanan (Services)
     */
    public function getPolis(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $polis = Poli::select('id', 'nama_poli', 'deskripsi', 'status', 'created_at')
            ->orderBy('nama_poli')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $polis,
        ]);
    }

    /**
     * Helper: Get storage usage
     */
    private function getStorageUsage(): array
    {
        try {
            $logSize = (int) shell_exec("du -sb " . storage_path() . " 2>/dev/null | cut -f1") ?? 0;
            $dbSize = (int) shell_exec("ls -l " . database_path('database.sqlite') . " 2>/dev/null | awk '{print $5}'") ?? 0;
            
            $totalSize = $logSize + $dbSize;
            $usedPercent = min(100, round(($totalSize / (5 * 1024 * 1024 * 1024)) * 100)); // Assume 5GB limit

            return [
                'logs_mb' => round($logSize / 1024 / 1024, 2),
                'database_mb' => round($dbSize / 1024 / 1024, 2),
                'total_mb' => round($totalSize / 1024 / 1024, 2),
                'used_percent' => $usedPercent,
            ];
        } catch (\Exception $e) {
            return [
                'logs_mb' => 0,
                'database_mb' => 0,
                'total_mb' => 0,
                'used_percent' => 0,
            ];
        }
    }
}
