# 🛠️ Super Admin — Implementation Guide

**Tanggal Implementasi:** 29 Juni 2026  
**Status:** ✅ Production Ready  
**Versi:** 1.0.0

---

## 📋 Daftar Isi

1. [Overview Perubahan](#overview-perubahan)
2. [Database Migrations](#database-migrations)
3. [Backend — Models & Controllers](#backend--models--controllers)
4. [API Routes](#api-routes)
5. [Frontend Components](#frontend-components)
6. [Status Integrasi](#status-integrasi)
7. [Verifikasi per Role](#verifikasi-per-role)
8. [File yang Dibuat / Dimodifikasi](#file-yang-dibuat--dimodifikasi)
9. [Setup User Super Admin](#setup-user-super-admin)

---

## Overview Perubahan

Dashboard Super Admin fokus pada **administrasi sistem** (user management, audit logs, backup), bukan data operasional pasien/klinik. Role lain (admin, dokter, terapis) tidak terpengaruh.

### Ditambahkan untuk Super Admin
- Total User Aktif + Login Gagal Hari Ini + Storage Terpakai
- Log Aktivitas Sistem (module, action, user, status)
- Login History + Failed Login Attempts tracking
- User Management CRUD (create, read, update, delete, reset password)
- Poli/Services Management (view only)

### Dihapus dari Super Admin (→ 403 Forbidden)
- Total Pasien, Antrian, Assessment Hari Ini, Terapi Aktif
- Tren Kunjungan Pasien, Distribusi Diagnosis
- Laporan Medis / detail per pasien

---

## Database Migrations

### Tabel `login_histories`
**File:** `database/migrations/2026_06_29_120000_create_login_history_table.php`

```
id, user_id (FK), email, ip_address, user_agent, browser, os,
success (boolean), failure_reason, login_at, created_at, updated_at
```

### Tabel `system_audit_logs`
**File:** `database/migrations/2026_06_29_120100_create_system_audit_logs_table.php`

```
id, user_id (FK), module, action, description, ip_address,
old_values (JSON), new_values (JSON), affected_records (JSON),
status (success/failed/warning), error_message, created_at, updated_at
```

**Jalankan migrasi:**
```bash
php artisan migrate
php artisan tinker
> DB::table('login_histories')->count()
> DB::table('system_audit_logs')->count()
```

---

## Backend — Models & Controllers

### Models
- `app/Models/LoginHistory.php` — Relationship ke User, casts datetime & boolean
- `app/Models/SystemAuditLog.php` — Relationship ke User, JSON casts untuk old/new values

### SuperAdminController
**File:** `app/Http/Controllers/Api/SuperAdminController.php`

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `getDashboardStats()` | GET /super-admin/dashboard | Total User, Failed Logins, Storage |
| `getAuditLogs()` | GET /super-admin/audit-logs | Paginated audit logs |
| `getLoginHistory()` | GET /super-admin/login-history | Paginated login history |
| `getFailedLogins()` | GET /super-admin/failed-logins | Failed attempts N hari |
| `getUsers()` | GET /super-admin/users | List semua staff users |
| `createUser()` | POST /super-admin/users | Buat user baru |
| `updateUser()` | PUT /super-admin/users/{id} | Update user |
| `deleteUser()` | DELETE /super-admin/users/{id} | Hapus user |
| `resetUserPassword()` | POST /super-admin/users/{id}/reset-password | Reset password |
| `getPolis()` | GET /super-admin/polis | List poli (view only) |

Semua method memiliki check `if ($user->role !== 'super_admin')` di level controller.

### AnalyticsController
Ditambahkan penolakan super_admin:
```php
if ($user->role === 'super_admin') {
    return response()->json(['success' => false, 'message' => 'Super admin akses ke dashboard sistem saja'], 403);
}
```

---

## API Routes

**File:** `routes/api.php`

```php
Route::middleware('role:super_admin')->prefix('super-admin')->group(function () {
    GET    /super-admin/dashboard
    GET    /super-admin/audit-logs
    GET    /super-admin/login-history
    GET    /super-admin/failed-logins
    GET    /super-admin/users
    POST   /super-admin/users
    PUT    /super-admin/users/{id}
    DELETE /super-admin/users/{id}
    POST   /super-admin/users/{id}/reset-password
    GET    /super-admin/polis
    POST   /polis              (via PoliController)
    PUT    /polis/{id}
    DELETE /polis/{id}
});
```

**Route yang diblokir untuk super_admin:**
- `/patients/*`, `/queues/*`, `/assessments/*`, `/therapies/*`, `/monitoring/*`
- `/analytics/dashboard`

---

## Frontend Components

### Struktur Komponen
```
frontend/src/modules/super-admin/
├── router/
│   └── superAdminRoutes.js          ← Route definitions (nested layout)
├── views/
│   ├── SuperAdminLayout.vue         ← Parent layout (Sidebar + Navbar + router-view)
│   ├── DashboardView.vue            ← Dashboard dengan stat cards
│   ├── UserManagementView.vue       ← CRUD user management
│   ├── PoliManagementView.vue       ← CRUD poli management
│   ├── AuditLogsView.vue            ← Paginated audit logs
│   ├── BackupView.vue               ← UI backup/restore
│   └── SettingsView.vue             ← System settings form
├── services/
│   └── superAdminService.js         ← API calls ke /super-admin/*
└── stores/                          ← (opsional, state management)
```

### Route Structure (Nested Layout)
```javascript
// superAdminRoutes.js
{
  path: '/super-admin',
  component: SuperAdminLayout,     // Parent dengan Sidebar+Navbar
  meta: { requiresAuth: true, roles: ['super_admin'] },
  children: [
    { path: 'dashboard', component: DashboardView },
    { path: 'users', component: UserManagementView },
    { path: 'polis', component: PoliManagementView },
    { path: 'audit-logs', component: AuditLogsView },
    { path: 'backup', component: BackupView },
    { path: 'settings', component: SettingsView },
  ]
}
```

### LoginPage.vue — Redirect Berdasarkan Role
```javascript
if (role === 'super_admin') {
  router.push('/super-admin/dashboard')
} else {
  router.push('/dashboard')
}
```

### authStore.js — isSuperAdmin Computed
```javascript
const isSuperAdmin = computed(() => userRole.value === 'super_admin')
```

---

## Status Integrasi

| Fitur | Backend | Frontend | Routes | Status |
|-------|---------|----------|--------|--------|
| Dashboard Stats | ✅ | ✅ | ✅ | Ready |
| User Management | ✅ | ✅ | ✅ | Ready |
| Poli Management | ✅ | ✅ | ✅ | Ready |
| Audit Logs | ✅ | ✅ | ✅ | Ready |
| Login History | ✅ | ⏳ | ✅ | Backend Ready |
| Backup | ⏳ | ✅ (UI) | ✅ | Perlu Backend |
| Settings | ⏳ | ✅ (UI) | ✅ | Perlu Backend |

**Role Cleanup:** Role `resepsionis` telah dihapus. Role aktif: `super_admin`, `admin`, `dokter`, `terapis`.

**Admin Klinik Separation:** Admin klinik tidak lagi punya akses ke User Management, Poli CRUD, dan System Settings — semua dipindah ke Super Admin.

---

## Verifikasi per Role

### Super Admin (`role = 'super_admin'`)
✅ Dapat akses: `/super-admin/*`, Dashboard sistem  
❌ Diblokir: `/patients/*`, `/queues/*`, `/assessments/*`, `/therapies/*`, `/monitoring/*`, `/analytics/dashboard`

### Admin Klinik (`role = 'admin'`)
✅ Dapat akses: Data pasien, antrian, assessment, terapi, monitoring, laporan  
❌ Diblokir: `/super-admin/*`, User Management, Poli CRUD

### Dokter (`role = 'dokter'`)
✅ Dapat akses: Pasien, antrian, assessment, terapi, monitoring  
❌ Diblokir: `/super-admin/*`, delete assessment

### Terapis (`role = 'terapis'`)
✅ Dapat akses: Antrian, terapi, monitoring  
❌ Diblokir: `/super-admin/*`, `/assessments/*`

---

## File yang Dibuat / Dimodifikasi

### Backend — Baru
```
database/migrations/2026_06_29_120000_create_login_history_table.php
database/migrations/2026_06_29_120100_create_system_audit_logs_table.php
database/migrations/2026_06_29_190000_add_super_admin_to_role_check.php
app/Models/LoginHistory.php
app/Models/SystemAuditLog.php
app/Http/Controllers/Api/SuperAdminController.php
```

### Backend — Dimodifikasi
```
routes/api.php                         ← Tambah /super-admin route group
app/Http/Controllers/Api/AuthController.php  ← Allow super_admin login
app/Http/Controllers/Api/AnalyticsController.php ← Reject super_admin
```

### Frontend — Baru
```
frontend/src/modules/super-admin/router/superAdminRoutes.js
frontend/src/modules/super-admin/views/DashboardView.vue
frontend/src/modules/super-admin/views/UserManagementView.vue
frontend/src/modules/super-admin/views/PoliManagementView.vue
frontend/src/modules/super-admin/views/AuditLogsView.vue
frontend/src/modules/super-admin/views/BackupView.vue
frontend/src/modules/super-admin/views/SettingsView.vue
frontend/src/modules/super-admin/views/SuperAdminLayout.vue
frontend/src/modules/super-admin/services/superAdminService.js
```

### Frontend — Dimodifikasi
```
frontend/src/router/index.js           ← Import + register superAdminRoutes
frontend/src/modules/auth/views/LoginPage.vue  ← Role-based redirect
frontend/src/modules/auth/stores/authStore.js  ← isSuperAdmin, super admin menus
frontend/src/shared/components/layout/Sidebar.vue ← Super admin menu section
frontend/src/shared/components/layout/Navbar.vue  ← pageTitle untuk super-admin routes
```

---

## Setup User Super Admin

```bash
php artisan tinker

User::create([
    'name'     => 'Super Admin',
    'email'    => 'superadmin@sitara.com',
    'role'     => 'super_admin',
    'password' => Hash::make('password123'),
    'status'   => 'active'
]);

exit
```

Atau update user yang sudah ada:
```bash
php artisan tinker
User::where('email', 'your_email@example.com')->update(['role' => 'super_admin', 'status' => 'active']);
exit
```

---

*Terakhir diupdate: 30 Juni 2026*
