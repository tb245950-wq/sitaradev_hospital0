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

        $staffQuery = User::where('role', '!=', 'pasien');

        return response()->json([
            'success' => true,
            'data' => [
                'total_users'         => (clone $staffQuery)->count(),
                'active_users'        => (clone $staffQuery)->where('status', 'active')->count(),
                'inactive_users'      => (clone $staffQuery)->where('status', 'inactive')->count(),
                'total_polis'         => Poli::count(),
                'failed_logins_today' => LoginHistory::where('success', false)->whereDate('login_at', $today)->count(),
                'storage_used'        => $this->getStorageUsage(),
                'today_formatted'     => $now->locale('id')->isoFormat('dddd, D MMMM YYYY'),
            ],
        ]);
    }

    /**
     * GET /super-admin/activity-logs
     * Semua ActivityLog (antrian, assessment, terapi, pasien) untuk Super Admin
     */
    public function getActivityLogs(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $limit  = (int) $request->query('limit', 15);
        $page   = (int) $request->query('page', 1);
        $search = $request->query('search');
        $status = $request->query('status');
        $type   = $request->query('type');   // activity_type filter
        $date   = $request->query('date');   // filter tanggal YYYY-MM-DD

        $query = \App\Models\ActivityLog::with([
                'patient:id_pasien,nama_lengkap,nrm',
                'user:id,name,email,role',
            ])
            ->orderByDesc('created_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('activity_type', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('patient', fn($p) => $p->where('nama_lengkap', 'like', "%{$search}%"));
            });
        }
        if ($status) $query->where('status', $status);
        if ($type)   $query->where('activity_type', $type);
        if ($date)   $query->whereDate('created_at', $date);

        $logs = $query->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'success'    => true,
            'data'       => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
                'total'        => $logs->total(),
                'per_page'     => $logs->perPage(),
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

        $limit  = (int) $request->query('limit', 15);
        $page   = (int) $request->query('page', 1);
        $module = $request->query('module');
        $action = $request->query('action');
        $status = $request->query('status');
        $search = $request->query('search');
        $anomalyOnly = $request->boolean('anomaly_only', false);

        // Aksi yang dianggap anomali
        $anomalyActions = ['delete', 'reset_password', 'export', 'download'];

        $query = SystemAuditLog::with('user:id,name,email,role')
            ->orderByDesc('created_at');

        if ($module) $query->where('module', $module);
        if ($action) $query->where('action', $action);
        if ($status) $query->where('status', $status);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%"));
            });
        }
        if ($anomalyOnly) {
            $query->where(function ($q) use ($anomalyActions) {
                $q->whereIn('action', $anomalyActions)
                  ->orWhere('status', 'failed');
            });
        }

        $logs = $query->paginate($limit, ['*'], 'page', $page);

        $items = collect($logs->items())->map(function ($log) use ($anomalyActions) {
            $isAnomaly = in_array($log->action, $anomalyActions) || $log->status === 'failed';
            return array_merge($log->toArray(), ['is_anomaly' => $isAnomaly]);
        });

        // Hitung ringkasan anomali
        $anomalyCount = SystemAuditLog::where(function ($q) use ($anomalyActions) {
            $q->whereIn('action', $anomalyActions)->orWhere('status', 'failed');
        })->whereDate('created_at', today())->count();

        return response()->json([
            'success'      => true,
            'data'         => $items,
            'anomaly_today'=> $anomalyCount,
            'pagination'   => [
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
                'total'        => $logs->total(),
                'per_page'     => $logs->perPage(),
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
     * User Management - Get all users (termasuk pasien)
     */
    public function getUsers(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $role = $request->query('role');
        $status = $request->query('status');

        $query = User::query();

        if ($role) {
            $query->where('role', $role);
        }
        if ($status) {
            $query->where('status', $status);
        }

        $perPage = (int) $request->query('per_page', 25);
        $page    = (int) $request->query('page', 1);

        $paginated = $query->select('id', 'name', 'email', 'role', 'nip', 'status', 'last_login_at', 'created_at')
            ->orderBy('role')
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success'      => true,
            'data'         => $paginated->items(),
            'total'        => $paginated->total(),
            'per_page'     => $paginated->perPage(),
            'current_page' => $paginated->currentPage(),
            'last_page'    => $paginated->lastPage(),
            'from'         => $paginated->firstItem(),
            'to'           => $paginated->lastItem(),
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

        $polis = Poli::select('id', 'nama', 'deskripsi', 'status', 'created_at')
            ->orderBy('nama')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $polis,
        ]);
    }

    // =========================================================
    // BACKUP
    // =========================================================

    /**
     * GET /super-admin/backups — list backup files
     */
    public function getBackups(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $files = collect(glob($backupDir . '/*.sql'))
            ->map(fn($path) => [
                'name'       => basename($path),
                'size'       => filesize($path),
                'created_at' => date('Y-m-d H:i:s', filemtime($path)),
            ])
            ->sortByDesc('created_at')
            ->values();

        return response()->json(['success' => true, 'data' => $files]);
    }

    /**
     * POST /super-admin/backup — create new backup
     */
    public function createBackup(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename  = 'backup_' . now()->format('Y-m-d_His') . '.sql';
        $filepath  = $backupDir . '/' . $filename;

        try {
            // Ambil konfigurasi DB
            $dbConfig = config('database.connections.' . config('database.default'));

            if ($dbConfig['driver'] === 'pgsql') {
                $host     = $dbConfig['host'];
                $port     = $dbConfig['port'] ?? 5432;
                $dbname   = $dbConfig['database'];
                $username = $dbConfig['username'];
                $password = $dbConfig['password'];

                // Set PGPASSWORD agar pg_dump tidak minta password interaktif
                $env     = "PGPASSWORD=" . escapeshellarg($password);
                $command = "$env pg_dump -h " . escapeshellarg($host)
                    . " -p " . intval($port)
                    . " -U " . escapeshellarg($username)
                    . " " . escapeshellarg($dbname)
                    . " > " . escapeshellarg($filepath)
                    . " 2>&1";

                exec($command, $output, $returnCode);

            } elseif ($dbConfig['driver'] === 'mysql') {
                $host     = $dbConfig['host'];
                $port     = $dbConfig['port'] ?? 3306;
                $dbname   = $dbConfig['database'];
                $username = $dbConfig['username'];
                $password = $dbConfig['password'];

                $command = "mysqldump -h " . escapeshellarg($host)
                    . " -P " . intval($port)
                    . " -u " . escapeshellarg($username)
                    . " -p" . escapeshellarg($password)
                    . " " . escapeshellarg($dbname)
                    . " > " . escapeshellarg($filepath)
                    . " 2>&1";

                exec($command, $output, $returnCode);

            } elseif ($dbConfig['driver'] === 'sqlite') {
                $source = $dbConfig['database'];
                copy($source, $filepath . '.db');
                $filepath = $filepath . '.db';
                $filename = $filename . '.db';
                $returnCode = 0;
            } else {
                return response()->json(['success' => false, 'message' => 'Driver database tidak didukung untuk backup otomatis.'], 422);
            }

            if (isset($returnCode) && $returnCode !== 0) {
                return response()->json(['success' => false, 'message' => 'Backup gagal. Pastikan pg_dump/mysqldump tersedia di server.'], 500);
            }

            // Catat ke audit log
            SystemAuditLog::create([
                'user_id'     => $user->id,
                'module'      => 'backup',
                'action'      => 'create',
                'description' => "Backup database dibuat: {$filename}",
                'ip_address'  => $request->ip(),
                'status'      => 'success',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Backup berhasil dibuat',
                'data'    => [
                    'name'       => $filename,
                    'size'       => file_exists($filepath) ? filesize($filepath) : 0,
                    'created_at' => now()->format('Y-m-d H:i:s'),
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Backup failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Backup gagal: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================
    // SETTINGS
    // =========================================================

    /** File path tempat settings disimpan */
    private function settingsPath(): string
    {
        return storage_path('app/system_settings.json');
    }

    /**
     * GET /super-admin/settings — ambil pengaturan sistem
     */
    public function getSettings(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $defaults = [
            'clinic_name'      => config('app.name', 'SITARA'),
            'clinic_email'     => '',
            'clinic_phone'     => '',
            'clinic_address'   => '',
            'smtp_host'        => config('mail.mailers.smtp.host', ''),
            'smtp_port'        => config('mail.mailers.smtp.port', 587),
            'smtp_email'       => config('mail.from.address', ''),
            'session_timeout'  => 30,
            'require_2fa'      => false,
        ];

        $path = $this->settingsPath();
        if (file_exists($path)) {
            $saved    = json_decode(file_get_contents($path), true) ?? [];
            $settings = array_merge($defaults, $saved);
        } else {
            $settings = $defaults;
        }

        return response()->json(['success' => true, 'data' => $settings]);
    }

    /**
     * POST /super-admin/settings — simpan pengaturan sistem
     */
    public function saveSettings(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'clinic_name'     => 'sometimes|string|max:255',
            'clinic_email'    => 'sometimes|nullable|email',
            'clinic_phone'    => 'sometimes|nullable|string|max:50',
            'clinic_address'  => 'sometimes|nullable|string|max:500',
            'smtp_host'       => 'sometimes|nullable|string|max:255',
            'smtp_port'       => 'sometimes|nullable|integer|min:1|max:65535',
            'smtp_email'      => 'sometimes|nullable|email',
            'session_timeout' => 'sometimes|nullable|integer|min:5|max:1440',
            'require_2fa'     => 'sometimes|boolean',
        ]);

        $path = $this->settingsPath();

        // Merge dengan settings yang sudah ada
        $existing = file_exists($path)
            ? (json_decode(file_get_contents($path), true) ?? [])
            : [];

        $newSettings = array_merge($existing, $validated);

        file_put_contents($path, json_encode($newSettings, JSON_PRETTY_PRINT));

        SystemAuditLog::create([
            'user_id'     => $user->id,
            'module'      => 'settings',
            'action'      => 'update',
            'description' => 'Pengaturan sistem diperbarui',
            'ip_address'  => $request->ip(),
            'new_values'  => $validated,
            'status'      => 'success',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan berhasil disimpan',
            'data'    => $newSettings,
        ]);
    }

    /**
     * GET /super-admin/backups/{filename}/download — download backup file
     */
    public function downloadBackup(Request $request, $filename)
    {
        $user = $request->user();
        if ($user->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $backupDir = storage_path('app/backups');
        $filepath = $backupDir . '/' . basename($filename); // Prevent path traversal

        // Validasi file ada dan berformat .sql
        if (!file_exists($filepath) || !str_ends_with($filepath, '.sql')) {
            return response()->json(['error' => 'File not found'], 404);
        }

        // Log audit
        SystemAuditLog::create([
            'user_id'     => $user->id,
            'module'      => 'backup',
            'action'      => 'download',
            'description' => "Downloaded backup: {$filename}",
            'ip_address'  => $request->ip(),
            'status'      => 'success',
        ]);

        // Download file
        return response()->download($filepath, $filename, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * GET /super-admin/export/csv — export semua data ke CSV
     */
    public function exportToCSV(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $exportDir = storage_path('app/exports');
            if (!is_dir($exportDir)) {
                mkdir($exportDir, 0755, true);
            }

            $timestamp = now()->format('Y-m-d_His');
            $zipFile = $exportDir . '/export_' . $timestamp . '.zip';

            // Buat ZIP file
            $zip = new \ZipArchive();
            if ($zip->open($zipFile, \ZipArchive::CREATE) !== true) {
                return response()->json(['error' => 'Failed to create ZIP file'], 500);
            }

            // 1. EXPORT USERS (Non-pasien)
            $users = User::where('role', '!=', 'pasien')
                ->get(['id', 'name', 'email', 'role', 'nip', 'status', 'created_at']);
            $usersCSV = $this->generateCSV('Users (Staff)', [
                ['ID', 'Nama', 'Email', 'Role', 'NIP', 'Status', 'Dibuat'],
            ], $users->map(fn($u) => [
                $u->id, $u->name, $u->email, $u->role, $u->nip ?? '-', $u->status, $u->created_at->format('Y-m-d H:i:s')
            ])->toArray());
            $zip->addFromString('01_users_staff.csv', $usersCSV);

            // 2. EXPORT PATIENTS
            $patients = \App\Models\Patient::with('user')
                ->get(['id_pasien', 'nrm', 'nik', 'nama_lengkap', 'jenis_kelamin', 'tanggal_lahir', 'alamat', 'user_id']);
            $patientsCSV = $this->generateCSV('Patients', [
                ['ID Pasien', 'NRM', 'NIK', 'Nama Lengkap', 'JK', 'Tanggal Lahir', 'Alamat', 'User ID'],
            ], $patients->map(fn($p) => [
                $p->id_pasien, $p->nrm, $p->nik, $p->nama_lengkap, $p->jenis_kelamin, $p->tanggal_lahir, $p->alamat, $p->user_id
            ])->toArray());
            $zip->addFromString('02_patients.csv', $patientsCSV);

            // 3. EXPORT QUEUES
            $queues = \App\Models\Queue::with('patient', 'user')
                ->orderByDesc('waktu_daftar')
                ->limit(500)
                ->get(['id_antrian', 'id_pasien', 'nomor_antrian', 'jenis_layanan', 'status', 'poli', 'waktu_daftar', 'waktu_selesai']);
            $queuesCSV = $this->generateCSV('Antrian', [
                ['ID Antrian', 'ID Pasien', 'Nomor Antrian', 'Layanan', 'Status', 'Poli', 'Daftar', 'Selesai'],
            ], $queues->map(fn($q) => [
                $q->id_antrian, $q->id_pasien, $q->nomor_antrian, $q->jenis_layanan, $q->status, $q->poli,
                $q->waktu_daftar->format('Y-m-d H:i:s'), $q->waktu_selesai?->format('Y-m-d H:i:s') ?? '-'
            ])->toArray());
            $zip->addFromString('03_queues.csv', $queuesCSV);

            // 4. EXPORT ASSESSMENTS
            $assessments = \App\Models\MedicalAssessment::with('patient', 'user')
                ->orderByDesc('tanggal_assessment')
                ->limit(500)
                ->get(['id_assessment', 'id_pasien', 'keluhan_utama', 'diagnosis', 'rencana_terapi', 'status', 'tanggal_assessment']);
            $assessmentsCSV = $this->generateCSV('Medical Assessments', [
                ['ID Assessment', 'ID Pasien', 'Keluhan Utama', 'Diagnosis', 'Rencana Terapi', 'Status', 'Tanggal'],
            ], $assessments->map(fn($a) => [
                $a->id_assessment, $a->id_pasien, $a->keluhan_utama, $a->diagnosis, $a->rencana_terapi, $a->status, $a->tanggal_assessment
            ])->toArray());
            $zip->addFromString('04_assessments.csv', $assessmentsCSV);

            // 5. EXPORT THERAPIES
            $therapies = \App\Models\Therapy::with('patient', 'terapis')
                ->orderByDesc('tanggal_mulai')
                ->limit(500)
                ->get(['id_terapi', 'id_pasien', 'nama_terapi', 'deskripsi', 'status', 'tanggal_mulai', 'tanggal_selesai']);
            $therapiesCSV = $this->generateCSV('Therapies', [
                ['ID Terapi', 'ID Pasien', 'Nama Terapi', 'Deskripsi', 'Status', 'Mulai', 'Selesai'],
            ], $therapies->map(fn($t) => [
                $t->id_terapi, $t->id_pasien, $t->nama_terapi, $t->deskripsi, $t->status,
                $t->tanggal_mulai, $t->tanggal_selesai ?? '-'
            ])->toArray());
            $zip->addFromString('05_therapies.csv', $therapiesCSV);

            // 6. EXPORT MONITORING SESSIONS
            $monitorings = \App\Models\TherapyMonitoring::with('therapy', 'patient')
                ->orderByDesc('tanggal_sesi')
                ->limit(1000)
                ->get(['id_monitoring', 'id_terapi', 'id_pasien', 'kehadiran', 'kondisi_pasien', 'progress_score', 'tanggal_sesi']);
            $monitoringsCSV = $this->generateCSV('Therapy Monitorings', [
                ['ID Monitoring', 'ID Terapi', 'ID Pasien', 'Kehadiran', 'Kondisi Pasien', 'Progress Score', 'Tanggal Sesi'],
            ], $monitorings->map(fn($m) => [
                $m->id_monitoring, $m->id_terapi, $m->id_pasien, $m->kehadiran, $m->kondisi_pasien, $m->progress_score, $m->tanggal_sesi
            ])->toArray());
            $zip->addFromString('06_monitorings.csv', $monitoringsCSV);

            // 7. EXPORT AUDIT LOGS
            $auditLogs = SystemAuditLog::orderByDesc('created_at')
                ->limit(500)
                ->get(['id', 'user_id', 'module', 'action', 'description', 'status', 'created_at']);
            $auditCSV = $this->generateCSV('Audit Logs', [
                ['ID', 'User ID', 'Module', 'Action', 'Description', 'Status', 'Created'],
            ], $auditLogs->map(fn($al) => [
                $al->id, $al->user_id, $al->module, $al->action, $al->description, $al->status, $al->created_at->format('Y-m-d H:i:s')
            ])->toArray());
            $zip->addFromString('07_audit_logs.csv', $auditCSV);

            $zip->close();

            // Log audit
            SystemAuditLog::create([
                'user_id'     => $user->id,
                'module'      => 'backup',
                'action'      => 'export',
                'description' => 'Exported all data to CSV (ZIP)',
                'ip_address'  => $request->ip(),
                'status'      => 'success',
            ]);

            // Download ZIP
            return response()->download($zipFile, 'export_' . $timestamp . '.zip', [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment; filename="export_' . $timestamp . '.zip"',
            ]);

        } catch (\Exception $e) {
            \Log::error('Export failed: ' . $e->getMessage());
            return response()->json(['error' => 'Export gagal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper: Generate CSV from data
     */
    private function generateCSV(string $title, array $headers, array $data): string
    {
        $output = fopen('php://memory', 'w');
        
        // BOM untuk Excel UTF-8
        fwrite($output, "\xEF\xBB\xBF");
        
        // Tulis header
        foreach ($headers as $row) {
            fputcsv($output, $row);
        }
        
        // Tulis data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
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
