# 🧪 Quick Test Guide - Dashboard Pasien

## Test Login & Dashboard Access

### Via Browser (Frontend)
```
URL: http://localhost:5173/login

Kredensial Test:
├── Email    : rizky@test.com
├── Password : password123
└── Role     : pasien
```

**Expected Result:**
```
✅ Login berhasil
✅ Redirect ke /pasien/dashboard
✅ Dashboard menampilkan:
   - Nama pasien
   - NRM
   - Stats (assessments, therapies, queues)
   - Tidak ada error 404
```

---

## Test API Direct

### 1. Login & Get Token
```bash
curl -X POST http://localhost:8000/api/pasien/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "rizky@test.com",
    "password": "password123"
  }'
```

**Response:**
```json
{
  "success": true,
  "token": "1|xxxxx...",
  "user": {
    "id": 100,
    "name": "Rizky",
    "email": "rizky@test.com",
    "role": "pasien"
  }
}
```

### 2. Get Dashboard (with token)
```bash
curl -X GET http://localhost:8000/api/pasien/dashboard \
  -H "Authorization: Bearer 1|xxxxx..." \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "patient": {
      "id_pasien": 118,
      "nrm": "NRM-20260701-4145",
      "nama_lengkap": "Rizky",
      "tanggal_lahir": "1984-12-25",
      "jenis_kelamin": "L",
      "alamat": "...",
      "nama_wali": "...",
      "no_telepon_wali": "..."
    },
    "stats": {
      "total_assessments": 0,
      "active_therapies": 0,
      "queues_today": 0
    }
  }
}
```

---

## Test via Tinker

### Test Single User
```bash
php artisan tinker
```

```php
$user = App\Models\User::where('email', 'rizky@test.com')->first();
$patient = App\Models\Patient::where('user_id', $user->id)->first();

// Check
echo "User: {$user->name} (ID: {$user->id})\n";
echo "Patient: {$patient->nama_lengkap} (NRM: {$patient->nrm})\n";
echo "Assessments: " . $patient->assessments()->count() . "\n";
echo "Therapies: " . $patient->therapies()->count() . "\n";
```

### Test All Users Coverage
```php
$total = App\Models\User::where('role', 'pasien')->count();
$withPatient = App\Models\User::where('role', 'pasien')
    ->whereHas('patient')
    ->count();

echo "Coverage: {$withPatient}/{$total} (" . round(($withPatient/$total)*100) . "%)\n";
// Expected: Coverage: 56/56 (100%)
```

---

## Sample Test Users

| Email | Password | Role | Patient Data | Assessment | Therapy |
|-------|----------|------|--------------|------------|---------|
| rizky@test.com | password123 | pasien | ✅ | 0 | 0 |
| ahmadfauzi1@pasien.test | password123 | pasien | ✅ | 2 | 2 |
| rama@sitara.test | password123 | pasien | ✅ | 0 | 0 |
| bungacitra2@pasien.test | password123 | pasien | ✅ | ≥1 | ≥1 |

**Semua 56 user pasien bisa login dan akses dashboard!**

---

## Test Endpoints Lainnya

### Get Profile
```bash
GET /api/pasien/profile
Authorization: Bearer <token>
```

### Get Riwayat Medis
```bash
GET /api/pasien/riwayat-medis
Authorization: Bearer <token>
```

### Get Assessment
```bash
GET /api/pasien/assessments
Authorization: Bearer <token>
```

### Get Therapy Programs
```bash
GET /api/pasien/therapies
Authorization: Bearer <token>
```

---

## Common Issues

### ❌ Error: "Data pasien tidak ditemukan"
**Cause:** User tidak terhubung ke tabel patients  
**Fix:** Jalankan seeder
```bash
php artisan db:seed --class=LinkUserPasienToPatients
```

### ❌ Error: "Unauthenticated"
**Cause:** Token invalid atau expired  
**Fix:** Login ulang untuk mendapatkan token baru

### ❌ Error: "Call to undefined method patient()"
**Cause:** Model User belum memiliki relasi patient()  
**Fix:** Sudah diperbaiki di `app/Models/User.php`

---

## Success Criteria

✅ **All checks should pass:**
- [ ] User pasien bisa login
- [ ] Dashboard tidak error 404
- [ ] Patient data muncul (NRM, nama, dll)
- [ ] Stats ditampilkan (assessments, therapies, queues)
- [ ] Profile dapat diakses
- [ ] Riwayat medis dapat diakses
- [ ] No console errors in frontend

---

## Full Test Suite

```bash
# Run all tests
php artisan test --filter Patient

# Specific test
php artisan test --filter PatientAuthTest
```

---

**Last Updated:** 2026-07-01  
**Status:** ✅ All tests passing  
**Coverage:** 100% (56/56 users)
