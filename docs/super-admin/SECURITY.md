# 🔐 Super Admin — Security Guide

**Status:** ✅ Production Ready  
**Terakhir diupdate:** 30 Juni 2026

---

## Arsitektur Keamanan

Sistem menggunakan **multi-layer protection** untuk memastikan isolasi data antara Super Admin dan role klinis:

```
Layer 1: Authentication     → auth:sanctum (token harus valid)
Layer 2: Role Middleware     → role:super_admin (role harus cocok)
Layer 3: Controller Check    → if ($user->role !== 'super_admin') → 403
Layer 4: Data Isolation      → Super admin TIDAK pernah query patient data
Layer 5: Audit Trail         → Semua aksi tercatat di system_audit_logs
```

---

## Role Matrix & Akses Data

| Fitur | super_admin | admin | dokter | terapis |
|-------|:-----------:|:-----:|:------:|:-------:|
| User Management | ✅ | ❌ | ❌ | ❌ |
| Poli Management | ✅ | ❌ | ❌ | ❌ |
| Audit Logs | ✅ | ❌ | ❌ | ❌ |
| Login History | ✅ | ❌ | ❌ | ❌ |
| Data Pasien | ❌ | ✅ | ✅ | ✅ |
| Antrian | ❌ | ✅ | ✅ | ✅ |
| Assessment | ❌ | ✅ | ✅ | ❌ |
| Terapi | ❌ | ✅ | ✅ | ✅ |
| Monitoring | ❌ | ✅ | ✅ | ✅ |
| Analytics Dashboard | ❌ | ✅ | ✅ | ✅ |

---

## Middleware & Role Check

### Backend — RoleMiddleware
**File:** `app/Http/Middleware/RoleMiddleware.php`

```php
public function handle(Request $request, Closure $next, ...$roles): Response
{
    if (!$request->user()) {
        return response()->json(['message' => 'Unauthenticated'], 401);
    }

    if (!in_array($request->user()->role, $roles)) {
        return response()->json(['message' => 'Forbidden: Akses ditolak'], 403);
    }

    if ($request->user()->status !== 'active') {
        return response()->json(['message' => 'Akun tidak aktif'], 403);
    }

    return $next($request);
}
```

### Backend — CheckRole Middleware (API Debug Logging)
**File:** `app/Http/Middleware/CheckRole.php`

```php
// Mencatat semua akses untuk audit (remove di production)
\Log::info('CheckRole Middleware', [
    'user_id'       => $user?->id,
    'user_role'     => $user?->role,
    'required_roles'=> $roles,
    'path'          => $request->path(),
]);
```

### Registrasi Middleware
**File:** `bootstrap/app.php` (Laravel 11 — tidak pakai Kernel.php)

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->statefulApi();
    $middleware->alias([
        'role' => RoleMiddleware::class,
    ]);
})
```

---

## Isolasi Data Pasien

Super Admin **tidak pernah** bisa mengakses data pasien:

```php
// routes/api.php
// Endpoint berikut HANYA untuk admin, dokter, terapis:
Route::middleware('role:admin,dokter,terapis')->prefix('patients')->group(...);
Route::middleware('role:admin,dokter,terapis')->prefix('queues')->group(...);
Route::middleware('role:admin,dokter')->prefix('assessments')->group(...);
Route::middleware('role:admin,dokter,terapis')->group(function () {
    Route::apiResource('therapies', ...);
    Route::apiResource('monitoring', ...);
});
```

```php
// AnalyticsController.php — Super admin diblokir
if ($user->role === 'super_admin') {
    return response()->json([
        'success' => false,
        'message' => 'Super admin akses ke dashboard sistem saja'
    ], 403);
}
```

---

## Audit Trail

Setiap aksi Super Admin dicatat di `system_audit_logs`:

```php
SystemAuditLog::create([
    'user_id'     => $user->id,
    'module'      => 'user',                     // user, backup, system, queue
    'action'      => 'create',                   // create, update, delete, reset_password
    'description' => 'Created user: john@...',
    'ip_address'  => $request->ip(),
    'old_values'  => $oldData,                   // JSON, untuk update/delete
    'new_values'  => $newData,                   // JSON
    'status'      => 'success',                  // success, failed, warning
]);
```

### Informasi yang Dicatat
- ✅ User ID yang melakukan aksi
- ✅ Module & action (create, update, delete, reset_password)
- ✅ IP Address
- ✅ Timestamp (created_at)
- ✅ Old values & new values (JSON) untuk audit trail lengkap
- ✅ Status aksi (success/failed/warning)

---

## Login History Tracking

Setiap login (berhasil maupun gagal) dicatat di `login_histories`:

```php
LoginHistory::create([
    'user_id'        => $user?->id,
    'email'          => $request->email,
    'ip_address'     => $request->ip(),
    'user_agent'     => $request->userAgent(),
    'browser'        => $browser,
    'os'             => $os,
    'success'        => true/false,
    'failure_reason' => 'Invalid credentials',  // jika gagal
    'login_at'       => now(),
]);
```

---

## Frontend — Navigation Guard Security

### Perbaikan Infinite Loop
Router guard mencegah redirect loop antar halaman:

```javascript
// Cegah redirect ke halaman yang sama
if (to.path === from.path) return next(false)

// Role tidak cocok → redirect ke halaman yang tepat
if (!to.meta.roles.includes(userRole)) {
    if (userRole === 'super_admin') return next('/super-admin/dashboard')
    else return next('/dashboard')
}
```

### Proteksi Route Frontend
Semua route super admin menggunakan `meta.roles`:
```javascript
meta: { requiresAuth: true, roles: ['super_admin'] }
```

Navigation guard memverifikasi role dari `localStorage` sebelum membuka halaman.

---

## Separation of Concerns

```
Super Admin (System Administration):
  ✅ Manage users (semua role)
  ✅ Manage system settings
  ✅ View audit logs & login history
  ✅ Backup/restore database
  ❌ TIDAK BISA akses data medis pasien
  ❌ TIDAK BISA operasional klinik

Admin Klinik (Clinical Operations):
  ✅ Manage patients
  ✅ Manage queues
  ✅ View/create medical records
  ❌ TIDAK BISA manage users
  ❌ TIDAK BISA system settings
  ❌ TIDAK BISA audit logs
```

---

## Checklist Keamanan

### Pre-Production
- [ ] Hapus semua `console.log` di production build
- [ ] Hapus debug logging dari CheckRole middleware
- [ ] Pastikan semua route super-admin punya middleware `role:super_admin`
- [ ] Test: admin mencoba `/super-admin/users` → harus 403
- [ ] Test: super_admin mencoba `/api/patients` → harus 403
- [ ] Test: login gagal tercatat di `login_histories`
- [ ] Test: buat/edit/hapus user tercatat di `system_audit_logs`

### Post-Deployment
- [ ] Monitor `storage/logs/laravel.log` untuk unauthorized access
- [ ] Review login_histories secara berkala
- [ ] Archive system_audit_logs setelah 90 hari
- [ ] Review failed login attempts untuk deteksi brute force

---

## Troubleshooting Security

### "403 Forbidden saat Super Admin login"
```bash
# Cek role di database
php artisan tinker
User::where('email', 'superadmin@sitara.com')->first(['id', 'email', 'role', 'status']);
# Harus: role = 'super_admin', status = 'active'
```

### "Admin bisa akses /super-admin (seharusnya 403)"
```bash
# Cek middleware terdaftar
php artisan route:list --path=super-admin
# Kolom Middleware harus: api, auth:sanctum, role:super_admin
```

### "Audit log tidak tercatat"
```bash
# Cek tabel ada
php artisan tinker
DB::table('system_audit_logs')->count();
# Cek model
new App\Models\SystemAuditLog();
```

---

*Lihat juga: [IMPLEMENTATION.md](./IMPLEMENTATION.md) | [ROUTES.md](./ROUTES.md) | [TESTING.md](./TESTING.md)*
