# Super Admin Implementation - Quick Testing Guide

## Setup Test Data (Artisan Tinker)

```bash
php artisan tinker

# Create Super Admin user
$superAdmin = User::create([
    'name' => 'Super Admin',
    'email' => 'superadmin@test.com',
    'role' => 'super_admin',
    'nip' => 'SA001',
    'password' => Hash::make('password123'),
    'status' => 'active'
]);

# Create Admin user (for comparison)
$admin = User::create([
    'name' => 'Admin User',
    'email' => 'admin@test.com',
    'role' => 'admin',
    'nip' => 'A001',
    'password' => Hash::make('password123'),
    'status' => 'active'
]);

# Create Dokter user
$dokter = User::create([
    'name' => 'Dr. Test',
    'email' => 'dokter@test.com',
    'role' => 'dokter',
    'nip' => 'D001',
    'password' => Hash::make('password123'),
    'status' => 'active'
]);

# Create test audit log
SystemAuditLog::create([
    'user_id' => $superAdmin->id,
    'module' => 'user',
    'action' => 'create',
    'description' => 'Created test admin user',
    'ip_address' => '127.0.0.1',
    'new_values' => ['id' => $admin->id, 'email' => 'admin@test.com'],
    'status' => 'success'
]);

# Create test login history
LoginHistory::create([
    'user_id' => $superAdmin->id,
    'email' => 'superadmin@test.com',
    'ip_address' => '127.0.0.1',
    'user_agent' => 'Mozilla/5.0...',
    'browser' => 'Chrome',
    'os' => 'Windows',
    'success' => true,
    'login_at' => now()
]);

LoginHistory::create([
    'user_id' => null,
    'email' => 'invalid@test.com',
    'ip_address' => '192.168.1.1',
    'success' => false,
    'failure_reason' => 'Invalid credentials',
    'login_at' => now()
]);

exit
```

## Testing with cURL

### 1. Login as Super Admin
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "superadmin@test.com",
    "password": "password123"
  }' | jq .

# Save the token from response
TOKEN="your_token_here"
```

### 2. Test Dashboard Endpoint
```bash
curl -X GET http://localhost:8000/api/super-admin/dashboard \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" | jq .

# Expected response:
# {
#   "success": true,
#   "data": {
#     "total_users": 3,
#     "active_users": 3,
#     "failed_logins_today": 0,
#     "storage_used": { ... },
#     "today_formatted": "..."
#   }
# }
```

### 3. Test Audit Logs
```bash
curl -X GET "http://localhost:8000/api/super-admin/audit-logs?limit=10&page=1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" | jq .

# Expected: Paginated list of system audit logs
```

### 4. Test Login History
```bash
curl -X GET "http://localhost:8000/api/super-admin/login-history?limit=20&page=1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" | jq .

# Expected: List of login records
```

### 5. Test Failed Logins
```bash
curl -X GET "http://localhost:8000/api/super-admin/failed-logins?days=7" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" | jq .

# Expected: List of failed login attempts from past 7 days
```

### 6. Get Users List
```bash
curl -X GET "http://localhost:8000/api/super-admin/users?role=admin" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" | jq .

# Expected: List of admin users only
```

### 7. Create New User
```bash
curl -X POST http://localhost:8000/api/super-admin/users \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "New Admin",
    "email": "newadmin@test.com",
    "role": "admin",
    "nip": "NA001",
    "password": "securepass123"
  }' | jq .

# Expected: User created, audit log entry made
```

### 8. Update User
```bash
curl -X PUT http://localhost:8000/api/super-admin/users/2 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "suspended"
  }' | jq .

# Expected: User updated, audit log with old/new values
```

### 9. Reset Password
```bash
curl -X POST http://localhost:8000/api/super-admin/users/2/reset-password \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "password": "newpassword123"
  }' | jq .

# Expected: Password reset, audit log entry
```

### 10. Delete User
```bash
curl -X DELETE http://localhost:8000/api/super-admin/users/4 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" | jq .

# Expected: User soft deleted, audit log entry
```

### 11. TEST: Super Admin tries to access patient data (should fail)
```bash
curl -X GET http://localhost:8000/api/patients \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" | jq .

# Expected: 403 Forbidden
# Response: { "message": "Forbidden: Akses ditolak" }
```

### 12. TEST: Super Admin tries to access analytics (should fail)
```bash
curl -X GET "http://localhost:8000/api/analytics/dashboard?period=week" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" | jq .

# Expected: 403 Forbidden
# Response: { "success": false, "message": "Super admin akses ke dashboard sistem saja" }
```

### 13. LOGIN as ADMIN and test (should succeed)
```bash
# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@test.com",
    "password": "password123"
  }' | jq .

# Save token
ADMIN_TOKEN="token_here"

# Access patients (should work)
curl -X GET http://localhost:8000/api/patients \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" | jq .

# Expected: 200 OK with patient list

# Try to access super-admin (should fail)
curl -X GET http://localhost:8000/api/super-admin/users \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" | jq .

# Expected: 403 Forbidden
```

## Browser Testing (Manual)

### Frontend Testing

1. **Login sebagai Super Admin**
   - Navigate to http://localhost:5173 (or your dev server)
   - Login dengan superadmin@test.com / password123
   - Should see SuperAdminDashboard, NOT AdminDashboard

2. **Check Dashboard Components**
   - ✓ Title "Dashboard Super Admin"
   - ✓ 3 stat cards visible (Total User Aktif, Login Gagal, Storage)
   - ✓ 5 navigation buttons visible
   - ✓ Recent audit logs table with data

3. **Test Navigation Menu**
   - Click each button
   - Verify links work (even if pages not yet implemented, router shouldn't crash)

4. **Test Auto-refresh**
   - Keep dashboard open for 31 seconds
   - Verify audit logs table updates

5. **Login as Admin and verify**
   - Logout
   - Login sebagai admin@test.com / password123
   - Should see AdminDashboard with operational data
   - Should see patient stats, queues, assessments, etc.

6. **Test Logout**
   - Logout from Super Admin
   - Logout from Admin
   - Should redirect to login page

## Database Verification

```bash
php artisan tinker

# Check login_histories table
> DB::table('login_histories')->count()      # Should have entries
> DB::table('login_histories')->first()

# Check system_audit_logs table
> DB::table('system_audit_logs')->count()    # Should have entries
> DB::table('system_audit_logs')->latest()->first()

# Check users with role super_admin
> User::where('role', 'super_admin')->count()
> User::where('role', 'super_admin')->first()

# Verify relationships
> $superAdmin = User::where('role', 'super_admin')->first()
> $superAdmin->loginHistories()->count()      # Should have entries
> $superAdmin->auditLogs()->count()           # Should have entries

exit
```

## Performance Testing

```bash
# Test response time for dashboard
time curl -X GET http://localhost:8000/api/super-admin/dashboard \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" > /dev/null

# Expected: < 1 second

# Load test (basic)
for i in {1..100}; do
  curl -X GET http://localhost:8000/api/super-admin/dashboard \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" > /dev/null &
done
wait

# Monitor CPU/Memory usage while running
```

## Test Checklist

```
BACKEND
- [ ] Migrations run successfully
- [ ] Models can be instantiated
- [ ] Routes registered correctly
- [ ] Super admin dashboard endpoint returns correct data
- [ ] Super admin can CRUD users
- [ ] Super admin can view audit logs & login history
- [ ] Super admin CANNOT access patient endpoints
- [ ] Super admin CANNOT access analytics endpoint
- [ ] Admin can still access patient endpoints
- [ ] Admin CANNOT access super-admin endpoints
- [ ] Audit logs created for user actions
- [ ] Login history recorded correctly

FRONTEND
- [ ] SuperAdminDashboard component renders
- [ ] DashboardView shows SuperAdminDashboard for super_admin role
- [ ] DashboardView still shows AdminDashboard for admin role
- [ ] Stats cards display correctly
- [ ] Navigation menu renders 5 buttons
- [ ] Audit logs table shows data
- [ ] Auto-refresh works every 30 seconds
- [ ] Error handling works
- [ ] Loading state displays correctly

SECURITY
- [ ] Super admin cannot access /patients
- [ ] Super admin cannot access /queues
- [ ] Super admin cannot access /assessments
- [ ] Super admin cannot access /therapies
- [ ] Super admin cannot access /monitoring
- [ ] Super admin cannot access /analytics/dashboard
- [ ] Admin can still access /patients
- [ ] Admin can still access /queues
- [ ] Admin CANNOT access /super-admin routes

INTEGRATION
- [ ] User creation triggers audit log
- [ ] User update triggers audit log with old/new values
- [ ] User delete triggers audit log
- [ ] Password reset triggers audit log
- [ ] Failed login attempts recorded
- [ ] Successful login recorded
```

## Troubleshooting

### Migrations Failed
```bash
# Check what went wrong
php artisan migrate:refresh --seed --step

# If issue persists, rollback and check syntax
php artisan migrate:rollback
php artisan migrate:reset
```

### Model Not Found
```bash
# Verify composer autoload
composer dump-autoload

# Verify namespaces
php artisan make:model TestModel

# Clear cache
php artisan cache:clear
php artisan config:clear
```

### Route Not Found (404)
```bash
# List all routes
php artisan route:list | grep super-admin

# Verify route syntax in api.php
php artisan route:list --path="/super-admin"
```

### 403 Forbidden when should be 200
```bash
# Check role in database
DB::table('users')->where('id', $userId)->first();

# Check middleware
php artisan route:list --path="/super-admin"
```

### Frontend not rendering component
```bash
# Check browser console for errors
# Verify component is imported in DashboardView.vue
# Verify path is correct: src/modules/dashboard/views/SuperAdminDashboard.vue
# Run npm run build to check for compilation errors
```

---

**Happy Testing!** 🧪
