# Super Admin Dashboard - Quick Reference

## Role-Based Dashboard View

```
┌─ User Login ─────────────────────────────────────┐
│                                                   │
├─ role = 'super_admin' ─────────────────────────┬─┤
│ ✓ SuperAdminDashboard                          │ │
│   - Total User Aktif                           │ │
│   - Login Gagal Hari Ini                       │ │
│   - Storage Terpakai                           │ │
│   - Recent Audit Logs Table                    │ │
│                                                │ │
│ ✓ Navigation Menu                             │ │
│   - Manajemen User                             │ │
│   - Manajemen Poli                             │ │
│   - Log Aktivitas                              │ │
│   - Backup                                     │ │
│   - Pengaturan                                 │ │
└────────────────────────────────────────────────┴─┘

┌─ role = 'admin' ───────────────────────────────┐
│ ✓ AdminDashboard (NO CHANGE)                   │
│   - Total Pasien                               │
│   - Pasien Baru Hari Ini                       │
│   - Antrian (Menunggu/Dipanggil/Selesai)      │
│   - Assessment Hari Ini                        │
│   - Terapi Aktif                               │
│   - Tren Kunjungan + Distribusi Diagnosis      │
│   - Antrian Aktif Hari Ini                     │
│   - Aktivitas Terbaru                          │
└────────────────────────────────────────────────┘

┌─ role = 'dokter' ──────────────────────────────┐
│ ✓ DoctorDashboard (NO CHANGE)                  │
│   - My Patients                                │
│   - Assessments Today                          │
│   - Visit Trends                               │
│   - Recent Activities                          │
└────────────────────────────────────────────────┘

┌─ role = 'terapis' ─────────────────────────────┐
│ ✓ TerapisDashboard (NO CHANGE)                 │
│   - My Sessions                                │
│   - Active Therapies                           │
│   - Attendance Rate                            │
│   - Recent Activities                          │
└────────────────────────────────────────────────┘
```

## API Endpoint Access Map

```
SUPER ADMIN (super_admin)
├─ ✓ /super-admin/*
│  ├─ GET    /dashboard               (stats)
│  ├─ GET    /audit-logs              (paginated)
│  ├─ GET    /login-history           (paginated)
│  ├─ GET    /failed-logins           (7 hari)
│  ├─ GET    /users                   (list)
│  ├─ POST   /users                   (create)
│  ├─ PUT    /users/{id}              (update)
│  ├─ DELETE /users/{id}              (delete)
│  ├─ POST   /users/{id}/reset-password
│  └─ GET    /polis                   (view only)
│
├─ ✗ /patients/*                      [403 Forbidden]
├─ ✗ /queues/*                        [403 Forbidden]
├─ ✗ /assessments/*                   [403 Forbidden]
├─ ✗ /therapies/*                     [403 Forbidden]
├─ ✗ /monitoring/*                    [403 Forbidden]
├─ ✗ /analytics/dashboard             [403 Forbidden]
└─ ✓ /pasien/*                        [Patient portal only]

ADMIN (admin)
├─ ✓ /patients/*
├─ ✓ /queues/*
├─ ✓ /assessments/*
├─ ✓ /therapies/*
├─ ✓ /monitoring/*
├─ ✓ /analytics/dashboard
├─ ✓ /polis (CRUD)
├─ ✗ /super-admin/*                   [403 Forbidden]
└─ ✓ /admin/users                     (Legacy endpoint)

DOKTER (dokter)
├─ ✓ /patients/* (read + update)
├─ ✓ /queues/*
├─ ✓ /assessments/*
├─ ✓ /therapies/*
├─ ✓ /monitoring/*
├─ ✓ /analytics/dashboard
├─ ✓ /polis (read only)
├─ ✗ /super-admin/*                   [403 Forbidden]
└─ ✓ /pasien/*                        [Patient portal only]

TERAPIS (terapis)
├─ ✓ /patients/* (read only)
├─ ✓ /queues/*
├─ ✓ /therapies/*
├─ ✓ /monitoring/*
├─ ✓ /analytics/dashboard
├─ ✓ /polis (read only)
├─ ✗ /assessments/*                   [403 Forbidden]
├─ ✗ /super-admin/*                   [403 Forbidden]
└─ ✓ /pasien/*                        [Patient portal only]
```

## Database Schema Changes

```
NEW TABLES:
├─ login_histories
│  ├─ id (PK)
│  ├─ user_id (FK → users.id)
│  ├─ email
│  ├─ ip_address
│  ├─ user_agent
│  ├─ browser
│  ├─ os
│  ├─ success (boolean)
│  ├─ failure_reason
│  ├─ login_at (timestamp)
│  ├─ created_at
│  └─ updated_at
│
└─ system_audit_logs
   ├─ id (PK)
   ├─ user_id (FK → users.id)
   ├─ module (user, backup, system, queue)
   ├─ action (create, update, delete, export, backup)
   ├─ description
   ├─ ip_address
   ├─ old_values (JSON)
   ├─ new_values (JSON)
   ├─ affected_records (JSON)
   ├─ status (success, failed, warning)
   ├─ error_message
   ├─ created_at
   └─ updated_at

EXISTING TABLES:
├─ users (NO CHANGE)
│  └─ Already has: nip, status, last_login_at fields
│
└─ Other tables (NO CHANGE)
```

## Files Structure

```
Backend:
├─ database/migrations/
│  ├─ 2026_06_29_120000_create_login_history_table.php [NEW]
│  └─ 2026_06_29_120100_create_system_audit_logs_table.php [NEW]
│
├─ app/Models/
│  ├─ LoginHistory.php [NEW]
│  └─ SystemAuditLog.php [NEW]
│
├─ app/Http/Controllers/Api/
│  ├─ SuperAdminController.php [NEW]
│  ├─ AnalyticsController.php [MODIFIED - added super_admin check]
│  └─ Other controllers [NO CHANGE]
│
└─ routes/
   └─ api.php [MODIFIED - added /super-admin routes]

Frontend:
├─ src/modules/dashboard/views/
│  ├─ SuperAdminDashboard.vue [NEW]
│  ├─ DashboardView.vue [MODIFIED - added SuperAdminDashboard render]
│  ├─ AdminDashboard.vue [NO CHANGE]
│  ├─ DoctorDashboard.vue [NO CHANGE]
│  └─ TerapisDashboard.vue [NO CHANGE]
│
├─ src/modules/analytics/components/
│  └─ StatCard.vue [REUSED - no changes]
│
└─ src/shared/composables/
   └─ useApi.js [REUSED - no changes]
```

## Data Flow - Super Admin Creating New User

```
┌─────────────────┐
│ SuperAdmin View │
│  (Frontend)     │
└────────┬────────┘
         │ POST /api/super-admin/users
         │ { name, email, role, password }
         ↓
┌──────────────────────────────┐
│ SuperAdminController         │
│ - Validate input             │
│ - Create user record         │
│ - Create audit log entry     │
└────────┬─────────────────────┘
         │
         ├→ INSERT users table
         │
         └→ INSERT system_audit_logs
            { user_id, module: 'user', action: 'create',
              description, new_values, status: 'success' }
         │
         ↓
┌──────────────────────────────┐
│ Response (201 Created)       │
│ { success, message, data }   │
└──────────────────────────────┘
         │
         ↓
┌──────────────────────────────┐
│ SuperAdmin View              │
│ - Show success message       │
│ - Refresh user list          │
│ - Show audit log entry       │
└──────────────────────────────┘
```

## Security Checks

```
MIDDLEWARE LAYERS:

1. Authentication
   Route::middleware('auth:sanctum') → User harus login

2. Role-Based Access
   Route::middleware('role:super_admin') → User harus super_admin
   
3. Controller-Level Check
   if ($user->role !== 'super_admin') → Return 403

4. Data Isolation
   - Super admin can ONLY see:
     * User management data (name, email, role, status)
     * Login history & audit logs
     * System storage info
     * Poli list (read-only)
   
   - Super admin CANNOT see:
     * Patient data (NIK, nama lengkap, alamat, dll)
     * Patient assessment & therapy records
     * Queue/antrian data
     * Medical records

5. Audit Trail
   - Every action logged to system_audit_logs
   - IP address recorded
   - Old & new values stored (JSON)
   - Status tracked (success/failed)
```

## Performance Considerations

```
✓ Indexed columns for fast queries:
  - login_histories: (user_id, login_at), (email, login_at), success
  - system_audit_logs: (user_id, created_at), (module, action, created_at), status

✓ Pagination: All list endpoints paginate (10-20 items default)

✓ Auto-refresh: Dashboard refreshes every 30 seconds (not aggressive)

✓ Caching: No caching on auth-required endpoints (always fresh)

⚠ Monitor: Log table size growth over time
   - Recommend: Archive old logs after 90 days
   - Implement: Log rotation policy
```

## Compliance Notes

```
✓ GDPR/Privacy:
  - No patient data visible to super admin
  - Audit trail for all user management actions
  - IP logging for security

✓ Audit Trail:
  - All changes tracked
  - User attribution
  - Timestamp & IP
  - Before/after values

✓ Role Separation:
  - Complete data isolation between roles
  - No cross-contamination of dashboards
  - Clear permission boundaries
```
