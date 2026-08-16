# 🧪 Quick Test - Riwayat Medis Pasien

## ✅ Status: FIXED

**Problem:** Error "Gagal memuat riwayat" saat akses /pasien/riwayat  
**Cause:** Relasi `pengguna` tidak ada di model (harusnya `user`)  
**Fix:** Updated `PatientAuthController::getMedicalHistory()`

---

## 🎯 Users untuk Testing

### ✅ Users dengan Data Lengkap (Recommended)

| Email | Password | Assessments | Therapies | Status |
|-------|----------|-------------|-----------|--------|
| **ahmadfauzi1@pasien.test** | password123 | 2 | 2 | ✅ READY |
| **bungacitra2@pasien.test** | password123 | 2 | 2 | ✅ READY |
| **cahyopratama3@pasien.test** | password123 | 2 | 2 | ✅ READY |
| dinamarlina4@pasien.test | password123 | 2 | 2 | ✅ READY |
| ekosaputra5@pasien.test | password123 | 2 | 2 | ✅ READY |
| fitrihandayani6@pasien.test | password123 | 2 | 2 | ✅ READY |
| gilangramadhan7@pasien.test | password123 | 2 | 2 | ✅ READY |
| hanirahmawati8@pasien.test | password123 | 2 | 2 | ✅ READY |
| ivansetiawan9@pasien.test | password123 | 2 | 2 | ✅ READY |
| juliaandriani10@pasien.test | password123 | 2 | 2 | ✅ READY |

### ⚠️ Users Tanpa Data (Empty State Test)

| Email | Password | Assessments | Therapies | Note |
|-------|----------|-------------|-----------|------|
| rizky@test.com | password123 | 0 | 0 | Empty state |
| rama@sitara.test | password123 | 0 | 0 | Empty state |

---

## 🧪 Test Manual (Browser)

### Step 1: Login
```
URL: http://localhost:5173/pasien/login

Kredensial:
├── Email    : ahmadfauzi1@pasien.test
├── Password : password123
└── Role     : pasien
```

### Step 2: Akses Riwayat Medis
```
1. Setelah login, redirect ke dashboard
2. Klik menu "Riwayat Medis" di sidebar
3. URL: http://localhost:5173/pasien/riwayat
```

### Expected Result ✅
```
✅ Loading spinner muncul sebentar
✅ Halaman berisi 2 section:
   - Assessment Medis (2 records)
   - Program Terapi (2 records)
✅ Tidak ada error "Gagal memuat riwayat"
✅ Data tampil lengkap dengan:
   - Diagnosis
   - Nama dokter
   - Tanggal
   - Status terapi
   - Progress sesi
```

### Failed Result ❌ (Jika belum fix)
```
❌ Background merah muda
❌ Pesan: "Gagal memuat riwayat"
❌ Tombol "Coba Lagi"
```

---

## 🧪 Test via API (curl)

### Step 1: Login
```bash
curl -X POST http://localhost:8000/api/pasien/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "ahmadfauzi1@pasien.test",
    "password": "password123"
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "token": "1|xxxxx...",
    "user": {
      "id": 102,
      "name": "Ahmad Fauzi",
      "email": "ahmadfauzi1@pasien.test",
      "role": "pasien"
    }
  }
}
```

**Copy token dari response!**

### Step 2: Get Riwayat Medis
```bash
curl -X GET http://localhost:8000/api/pasien/riwayat-medis \
  -H "Authorization: Bearer 1|xxxxx..." \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "assessments": [
      {
        "id": 52,
        "diagnosis": "Autism Spectrum Disorder (ASD)",
        "icd10_code": null,
        "catatan_medis": null,
        "dokter": {
          "name": "Muhammad Mughni"
        },
        "created_at": "2026-07-01T14:38:37.000000Z"
      }
    ],
    "therapies": [
      {
        "id": 33,
        "jenis_terapi": "Terapi Wicara",
        "status": "berjalan",
        "terapis": {
          "name": "Terapis Sitara"
        },
        "total_sesi": 4,
        "sesi_selesai": 3,
        "created_at": "2026-07-01T14:38:37.000000Z"
      }
    ]
  }
}
```

---

## 🧪 Test via Tinker

### Quick Test
```bash
php artisan tinker
```

```php
// Test dengan user yang punya data
$user = App\Models\User::where('email', 'ahmadfauzi1@pasien.test')->first();
$patient = App\Models\Patient::where('user_id', $user->id)->first();

$assessments = App\Models\MedicalAssessment::where('id_pasien', $patient->id_pasien)
    ->with('user:id,name')
    ->get();

$therapies = App\Models\Therapy::where('id_pasien', $patient->id_pasien)
    ->with('terapis:id,name')
    ->get();

echo "Assessments: {$assessments->count()}\n";
echo "Therapies: {$therapies->count()}\n";
// Expected: Assessments: 2, Therapies: 2
```

### Batch Test (All Users)
```php
$users = App\Models\User::where('role', 'pasien')
    ->whereHas('patient.assessments')
    ->whereHas('patient.therapies')
    ->take(5)
    ->get();

foreach ($users as $user) {
    $patient = $user->patient;
    $aCount = $patient->assessments()->count();
    $tCount = $patient->therapies()->count();
    echo "{$user->email}: A={$aCount}, T={$tCount}\n";
}
```

---

## ✅ Success Checklist

### API Response
- [ ] `success: true`
- [ ] `data.assessments` adalah array
- [ ] `data.therapies` adalah array
- [ ] Jika ada data: items memiliki struktur lengkap
- [ ] Jika tidak ada data: array kosong `[]`

### Frontend Display
- [ ] Loading state muncul
- [ ] Data assessment muncul (jika ada)
- [ ] Data therapy muncul (jika ada)
- [ ] Empty state muncul (jika tidak ada data)
- [ ] Tidak ada error message merah
- [ ] Tidak ada console errors

---

## 🐛 Troubleshooting

### Error: "Gagal memuat riwayat"
**Check:**
1. Apakah fix sudah diterapkan di controller?
2. Restart Laravel server: `php artisan serve`
3. Clear cache: `php artisan cache:clear`

### Error: "Data pasien tidak ditemukan"
**Fix:**
```bash
php artisan db:seed --class=LinkUserPasienToPatients
```

### Empty State (Tidak ada data)
**Normal!** User baru belum punya assessment/therapy.  
Gunakan user dari list "dengan data lengkap" di atas.

---

## 📝 Related Docs

- [FIX_RIWAYAT_MEDIS_ERROR.md](../fixes/FIX_RIWAYAT_MEDIS_ERROR.md)
- [KREDENSIAL_LOGIN_SEMUA_USER.txt](../../KREDENSIAL_LOGIN_SEMUA_USER.txt)
- [QUICK_TEST_DASHBOARD_PASIEN.md](../../QUICK_TEST_DASHBOARD_PASIEN.md)

---

**Last Updated:** 2026-07-01 19:35  
**Status:** ✅ FIXED & VERIFIED  
**Test Coverage:** 50+ users with data
