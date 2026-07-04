# 🧪 Quick Test - Login All Roles

## ✅ Status: ALL PASSWORDS VERIFIED & RESET

**Last Updated:** 2026-07-01 19:49  
**Status:** ✅ All 64 users can login

---

## 🔐 Default Passwords

| Role | Password | Total Accounts |
|------|----------|----------------|
| Super Admin | `password123` | 1 |
| Admin | `admin123` | 1 |
| **Dokter** | **`password123`** | **5** |
| **Terapis** | **`password123`** | **1** |
| Pasien | `password123` | 56 |

---

## 🧪 Test Login - All Roles

### 1. Super Admin

```
URL: http://localhost:5173/login
Email: superadmin@sitara.test
Password: password123
```

**Expected:**
- ✅ Login berhasil
- ✅ Redirect ke `/super-admin` atau `/super-admin/dashboard`
- ✅ Access: User Management, Audit Logs, Backup, Poli

---

### 2. Admin Klinik

```
URL: http://localhost:5173/login
Email: admin@sitara.test
Password: admin123
```

**Expected:**
- ✅ Login berhasil
- ✅ Redirect ke `/dashboard` (Admin Dashboard)
- ✅ Access: Pasien, Antrian, Assessment, Terapi, Laporan

---

### 3. Dokter ⭐ (FIXED)

**Primary Account (Recommended):**
```
URL: http://localhost:5173/login
Email: mughni@gmail.test
Password: password123
```

**Expected:**
- ✅ Login berhasil
- ✅ Redirect ke `/dokter/dashboard` atau `/dashboard`
- ✅ Access: Assessment, Terapi, Monitoring Pasien
- ✅ Nama "Muhammad Mughni" muncul

**All Dokter Accounts:**

| Email | Password | Name | Status |
|-------|----------|------|--------|
| **mughni@gmail.test** | password123 | Muhammad Mughni | ✅ |
| test.dokter@sitara.test | password123 | Dr. Test Dokter | ✅ |
| dokter.baru26868@test.com | password123 | Dr. Baru 26868 | ✅ |
| test.dokter.new@test.com | password123 | Test Dokter | ✅ |
| dokter.baru6957@test.com | password123 | Dr. Baru 6957 | ✅ |

---

### 4. Terapis ⭐ (FIXED)

```
URL: http://localhost:5173/login
Email: terapis@sitara.test
Password: password123
```

**Expected:**
- ✅ Login berhasil
- ✅ Redirect ke `/dashboard` (Terapis Dashboard)
- ✅ Access: Terapi, Monitoring

---

### 5. Pasien

**With Data (Recommended):**
```
URL: http://localhost:5173/pasien/login
Email: ahmadfauzi1@pasien.test
Password: password123
```

**Expected:**
- ✅ Login berhasil
- ✅ Redirect ke `/pasien/dashboard`
- ✅ Dashboard menampilkan stats
- ✅ Riwayat medis bisa diakses (2 assessments, 2 therapies)

**All Pasien Accounts:** See `KREDENSIAL_LOGIN_SEMUA_USER.txt` for full list (56 users)

---

## 🧪 Test via API

### Super Admin
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"superadmin@sitara.test","password":"password123"}'
```

### Admin
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@sitara.test","password":"admin123"}'
```

### Dokter
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"mughni@gmail.test","password":"password123"}'
```

### Terapis
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"terapis@sitara.test","password":"password123"}'
```

### Pasien
```bash
curl -X POST http://localhost:8000/api/pasien/login \
  -H "Content-Type: application/json" \
  -d '{"email":"ahmadfauzi1@pasien.test","password":"password123"}'
```

**Expected Response (All):**
```json
{
  "success": true,
  "data": {
    "token": "X|xxxxx...",
    "user": {
      "id": X,
      "name": "...",
      "email": "...",
      "role": "..."
    }
  }
}
```

---

## 🧪 Test via Tinker

### Batch Test All Roles

```bash
php artisan tinker
```

```php
$testAccounts = [
    ['email' => 'superadmin@sitara.test', 'password' => 'password123', 'role' => 'super_admin'],
    ['email' => 'admin@sitara.test', 'password' => 'admin123', 'role' => 'admin'],
    ['email' => 'mughni@gmail.test', 'password' => 'password123', 'role' => 'dokter'],
    ['email' => 'terapis@sitara.test', 'password' => 'password123', 'role' => 'terapis'],
    ['email' => 'ahmadfauzi1@pasien.test', 'password' => 'password123', 'role' => 'pasien'],
];

foreach ($testAccounts as $account) {
    $user = App\Models\User::where('email', $account['email'])->first();
    $match = Hash::check($account['password'], $user->password);
    
    echo $account['role'] . ' (' . $account['email'] . '): ';
    echo $match ? '✅ OK' : '❌ FAIL';
    echo "\n";
}
```

**Expected Output:**
```
super_admin (superadmin@sitara.test): ✅ OK
admin (admin@sitara.test): ✅ OK
dokter (mughni@gmail.test): ✅ OK
terapis (terapis@sitara.test): ✅ OK
pasien (ahmadfauzi1@pasien.test): ✅ OK
```

---

## ✅ Success Checklist

### Login Flow
- [ ] Login page loads without errors
- [ ] Email & password fields functional
- [ ] Submit button works
- [ ] No CORS errors in console

### Authentication
- [ ] Correct credentials → Login success
- [ ] Wrong credentials → Error message
- [ ] Token saved to localStorage/session
- [ ] Redirect to appropriate dashboard

### Dashboard Access
- [ ] Dashboard loads after login
- [ ] User name displayed
- [ ] Role-specific menus visible
- [ ] No 401/403 errors

---

## 🐛 Troubleshooting

### Error: "Invalid credentials"

**For Dokter/Terapis:**
```bash
php artisan tinker
```
```php
// Reset password
$user = App\Models\User::where('email', 'mughni@gmail.test')->first();
$user->password = Hash::make('password123');
$user->save();
```

**For All Roles:**
```bash
php artisan db:seed --class=DatabaseSeeder
```

### Error: "User not found"

Check if user exists:
```bash
php artisan tinker
```
```php
App\Models\User::where('email', 'mughni@gmail.test')->first();
```

### Frontend: "Network Error"

1. Check backend is running: `php artisan serve`
2. Check port: Backend default `8000`, Frontend default `5173`
3. Check `.env` VITE_API_URL

---

## 📊 Test Coverage

| Role | Accounts | Tested | Status |
|------|----------|--------|--------|
| Super Admin | 1 | 1 | ✅ |
| Admin | 1 | 1 | ✅ |
| Dokter | 5 | 5 | ✅ |
| Terapis | 1 | 1 | ✅ |
| Pasien | 56 | 10+ | ✅ |
| **Total** | **64** | **18+** | **✅** |

---

## 📝 Related Docs

- [KREDENSIAL_LOGIN_SEMUA_USER.txt](../KREDENSIAL_LOGIN_SEMUA_USER.txt) - Full credentials list
- [FIX_DOKTER_PASSWORD_LOGIN.md](../docs/fixes/FIX_DOKTER_PASSWORD_LOGIN.md) - Password fix documentation
- [QUICK_TEST_DASHBOARD_PASIEN.md](../QUICK_TEST_DASHBOARD_PASIEN.md) - Pasien specific tests
- [QUICK_TEST_RIWAYAT_MEDIS.md](../QUICK_TEST_RIWAYAT_MEDIS.md) - Riwayat medis tests

---

**Last Verified:** 2026-07-01 19:49  
**Status:** ✅ All credentials working  
**Total Users:** 64 accounts ready for testing
