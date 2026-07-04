# 🐛 Fix: Riwayat Medis - "Gagal Memuat Riwayat"

**Tanggal:** 2026-07-01  
**Status:** ✅ FIXED  
**Severity:** HIGH (API Error)

---

## 📋 Deskripsi Masalah

Ketika user dengan role `pasien` mengakses halaman **Riwayat Medis** (`/pasien/riwayat`), muncul error:

```
Gagal memuat riwayat
```

**Tampilan:**
- Background merah muda
- Pesan error
- Tombol "Coba Lagi"

---

## 🔍 Root Cause Analysis

### 1. **Relationship Name Mismatch**

File: `app/Http/Controllers/Api/PatientAuthController.php`

**Method:** `getMedicalHistory()`

```php
// ❌ SALAH - Relasi 'pengguna' tidak ada
$assessments = MedicalAssessment::where('id_pasien', $patient->id_pasien)
    ->with('pengguna:id,name')  // ← Error!
    ->get()
```

**Model** `MedicalAssessment` menggunakan relasi `user()`, bukan `pengguna()`:

```php
// app/Models/MedicalAssessment.php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class, 'id_pengguna', 'id');
}
```

### 2. **Error Log:**
```
Illuminate\Database\Eloquent\RelationNotFoundException
Call to undefined relationship [pengguna] on model [App\Models\MedicalAssessment]
```

---

## ✅ Solusi yang Diterapkan

### Fix Controller Method

File: `app/Http/Controllers/Api/PatientAuthController.php`

**Perubahan:**
```php
// ✅ BENAR - Menggunakan relasi 'user' yang ada di model
$assessments = \App\Models\MedicalAssessment::where('id_pasien', $patient->id_pasien)
    ->with('user:id,name')  // ← Diubah dari 'pengguna' ke 'user'
    ->orderBy('created_at', 'desc')
    ->get()
    ->map(fn($a) => [
        'id'          => $a->id_assessment,
        'diagnosis'   => $a->diagnosis,
        'icd10_code'  => $a->icd10_code ?? null,
        'catatan_medis' => $a->catatan_medis ?? $a->catatan_tambahan,
        'dokter'      => $a->user ? ['name' => $a->user->name] : null,  // ← user, bukan pengguna
        'created_at'  => $a->created_at,
    ]);
```

---

## 🧪 Verifikasi

### Test dengan User: `ahmadfauzi1@pasien.test`

**Request:**
```http
GET /api/pasien/riwayat-medis
Authorization: Bearer <token>
```

**Response:**
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
            },
            ...
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
            },
            ...
        ]
    }
}
```

### Test Results
```
✅ User: Ahmad Fauzi (ahmadfauzi1@pasien.test)
   Patient ID: 61
   Assessments: 2
   Therapies: 2
   Status: SUCCESS

✅ User: Bunga Citra (bungacitra2@pasien.test)
   Patient ID: 64
   Assessments: 2
   Therapies: 2
   Status: SUCCESS
```

---

## 📊 User Pasien dengan Data Lengkap

**Sample users yang bisa di-test (password semua: `password123`):**

| Email | Assessments | Therapies | Status |
|-------|-------------|-----------|--------|
| ahmadfauzi1@pasien.test | 2 | 2 | ✅ |
| bungacitra2@pasien.test | 2 | 2 | ✅ |
| cahyopratama3@pasien.test | 2 | 2 | ✅ |
| dinamarlina4@pasien.test | 2 | 2 | ✅ |
| ekosaputra5@pasien.test | 2 | 2 | ✅ |
| fitrihandayani6@pasien.test | 2 | 2 | ✅ |
| gilangramadhan7@pasien.test | 2 | 2 | ✅ |
| hanirahmawati8@pasien.test | 2 | 2 | ✅ |
| ivansetiawan9@pasien.test | 2 | 2 | ✅ |
| juliaandriani10@pasien.test | 2 | 2 | ✅ |

**Total:** 50+ users dengan data lengkap

---

## 📝 Catatan Tambahan

### User Tanpa Data (Normal Behavior)

Beberapa user tidak punya data assessment/therapy karena baru dibuat untuk fix relasi user-patient:
- `rizky@test.com` → Assessments: 0, Therapies: 0
- `rama@sitara.test` → Assessments: 0, Therapies: 0

Ini **bukan error**, melainkan data memang belum ada. API akan mengembalikan array kosong:

```json
{
  "success": true,
  "data": {
    "assessments": [],
    "therapies": []
  }
}
```

Frontend akan menampilkan:
```
Belum ada data assessment medis
Belum ada program terapi
```

---

## 🎯 Impact

### Sebelum Fix
- ❌ **500 Internal Server Error** saat akses riwayat medis
- ❌ Semua user pasien tidak bisa melihat riwayat
- ❌ Error di log: `RelationNotFoundException`

### Setelah Fix
- ✅ API mengembalikan data dengan benar
- ✅ Frontend menampilkan riwayat assessment & therapy
- ✅ Empty state ditampilkan untuk user tanpa data
- ✅ No errors in logs

---

## 📂 File yang Dimodifikasi

### Modified
- `app/Http/Controllers/Api/PatientAuthController.php` - Fix relasi `pengguna` → `user`

### Documentation
- `docs/fixes/FIX_RIWAYAT_MEDIS_ERROR.md` - Dokumentasi ini

---

## 🚀 Testing Guide

### Via Browser
```bash
# 1. Login sebagai pasien
URL: http://localhost:5173/pasien/login
Email: ahmadfauzi1@pasien.test
Password: password123

# 2. Klik menu "Riwayat Medis"
# Expected: Data assessment & therapy ditampilkan
```

### Via API
```bash
# 1. Login
curl -X POST http://localhost:8000/api/pasien/login \
  -H "Content-Type: application/json" \
  -d '{"email": "ahmadfauzi1@pasien.test", "password": "password123"}'

# 2. Get riwayat medis (gunakan token dari response login)
curl -X GET http://localhost:8000/api/pasien/riwayat-medis \
  -H "Authorization: Bearer <token>" \
  -H "Accept: application/json"
```

---

## 📚 Related Fixes

- [FIX_PASIEN_DASHBOARD_NOT_FOUND.md](./FIX_PASIEN_DASHBOARD_NOT_FOUND.md) - Fix relasi user ↔ patient

---

**Fixed by:** Kiro AI Agent  
**Verified:** ✅ All API tests passing  
**Ready for:** Production deployment
