# Super Admin Implementation - Deployment Checklist

## Pre-Deployment

- [ ] Review semua file yang dibuat/diubah
- [ ] Backup database
- [ ] Test di development environment

## Database Migrations

```bash
# Run migrations untuk create login_histories & system_audit_logs tables
php artisan migrate

# Verify tables tercipta
php artisan tinker
> DB::table('login_histories')->count()
> DB::table('system_audit_logs')->count()
```

## Backend Setup

### 1. Verify Models
```bash
php artisan tinker
> new App\Models\LoginHistory()
> new App\Models\SystemAuditLog()
```

### 2. Test Routes
```bash
# List routes untuk super-admin
php artisan route:list | grep super-admin

# Expected output:
# GET  /super-admin/dashboard
# GET  /super-admin/audit-logs
# GET  /super-admin/login-history
# GET  /super-admin/failed-logins
# GET  /super-admin/users
# POST /super-admin/users
# PUT  /super-admin/users/{userToUpdate}
# DELETE /super-admin/users/{userToDelete}
# POST /super-admin/users/{userToReset}/reset-password
# GET  /super-admin/polis
```

### 3. Test Controllers
```bash
php artisan tinker

# Create test super_admin user jika belum ada
> $user = User::create([
  'name' => 'Super Admin',
  'email' => 'superadmin@test.com',
  'role' => 'super_admin',
  'password' => Hash::make('password123'),
  'status' => 'active'
])

# Verify endpoint response structure
> $request = new Request(['user' => $user])
> $controller = new SuperAdminController()
> $response = $controller->getDashboardStats($request)
```

## Frontend Setup

### 1. Verify Components
- [ ] SuperAdminDashboard.vue imports correctly
- [ ] StatCard component accessible
- [ ] useApi composable available

### 2. Test Routing
```bash
cd frontend
npm run dev

# Login dengan super_admin role
# Navigate ke /dashboard
# Verify SuperAdminDashboard muncul bukan AdminDashboard
```

### 3. Test API Calls
```bash
# Open browser DevTools > Network tab

# Login sebagai super_admin
POST /api/login
  Response: token

# Get dashboard stats
GET /api/super-admin/dashboard
  Response: { total_users, active_users, failed_logins_today, storage_used, today_formatted }

# Get audit logs
GET /api/super-admin/audit-logs?limit=10&page=1
  Response: paginated list

# Get users list
GET /api/super-admin/users
  Response: list of all staff users
```

## Security Verification

### 1. Test Role Isolation
```bash
# Login sebagai admin
GET /api/super-admin/dashboard → 403 Forbidden ✓

# Login sebagai super_admin
GET /api/patients → 403 Forbidden ✓
GET /api/queues → 403 Forbidden ✓
GET /api/assessments → 403 Forbidden ✓

# Login sebagai admin (should work as before)
GET /api/patients → 200 OK ✓
GET /api/queues → 200 OK ✓
GET /api/assessments → 200 OK ✓
```

### 2. Test Access Control
```bash
# As super_admin, try to:
GET /api/patients/1 → 403 Forbidden ✓
POST /api/queues → 403 Forbidden ✓
PUT /api/assessments/1 → 403 Forbidden ✓

# As admin, verify same endpoints still work
GET /api/patients/1 → 200 OK ✓
POST /api/queues → 200 OK ✓
PUT /api/assessments/1 → 200 OK ✓
```

## Functionality Tests

### 1. Dashboard Stats
- [ ] Total User Aktif menampilkan jumlah benar
- [ ] Login Gagal Hari Ini menampilkan count
- [ ] Storage Terpakai menampilkan percentage

### 2. Navigation Menu
- [ ] All 5 buttons render correctly
- [ ] Links to correct routes (future implementation)

### 3. Audit Logs Table
- [ ] Table displays with proper columns
- [ ] Auto-refresh every 30 seconds
- [ ] Status badges display correctly

### 4. User Management
```bash
# Test create user
POST /api/super-admin/users
{
  "name": "Test User",
  "email": "test@example.com",
  "role": "admin",
  "password": "securepass123"
}
# Verify: system_audit_logs entry created

# Test update user
PUT /api/super-admin/users/2
{
  "status": "suspended"
}
# Verify: system_audit_logs entry with old/new values

# Test delete user
DELETE /api/super-admin/users/2
# Verify: system_audit_logs entry with deleted user info

# Test reset password
POST /api/super-admin/users/3/reset-password
{
  "password": "newpassword123"
}
# Verify: system_audit_logs entry
```

## Integration Tests

### 1. Login History Logging
- Verify login attempts recorded di login_histories table
- Check IP address, user agent captured
- Check success vs failed status

### 2. Audit Trail
- Create user → system_audit_logs entry ✓
- Update user → system_audit_logs entry with old/new values ✓
- Delete user → system_audit_logs entry ✓
- Reset password → system_audit_logs entry ✓

## Post-Deployment

- [ ] Monitor error logs untuk errors
- [ ] Verify all users can access their respective dashboards
- [ ] Test with multiple browsers/devices
- [ ] Verify responsive design mobile/tablet/desktop
- [ ] Check performance (API response time < 2s)

## Rollback Plan (if needed)

```bash
# Rollback migrations
php artisan migrate:rollback

# Remove files
rm -f database/migrations/2026_06_29_120000_create_login_history_table.php
rm -f database/migrations/2026_06_29_120100_create_system_audit_logs_table.php
rm -f app/Models/LoginHistory.php
rm -f app/Models/SystemAuditLog.php
rm -f app/Http/Controllers/Api/SuperAdminController.php
rm -f frontend/src/modules/dashboard/views/SuperAdminDashboard.vue

# Revert api.php & DashboardView.vue & AnalyticsController to previous version
git checkout routes/api.php
git checkout frontend/src/modules/dashboard/views/DashboardView.vue
git checkout app/Http/Controllers/Api/AnalyticsController.php
```

## Sign-off

- [ ] QA Approval
- [ ] Product Owner Approval
- [ ] Deploy to Staging
- [ ] Deploy to Production
- [ ] Monitor for 24 hours

---

**Date Completed**: ___________
**Deployed By**: ___________
**Notes**: ___________
