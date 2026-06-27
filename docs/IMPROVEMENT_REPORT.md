# SITARA - Laporan Perbaikan Kode
> **Tanggal:** 28 Juni 2026  
> **Referensi:** SRS SITARA (Docs/SITARA_SRS_FINAL_02.pdf)  
> **Scope:** Audit Authentication, Authorization, dan Frontend Views

---

## Ringkasan Eksekutif

Audit dilakukan terhadap seluruh codebase SITARA dengan merujuk ke SRS SITARA. Ditemukan **9 masalah utama** yang menyebabkan ketidaksesuaian antara implementasi dan spesifikasi — mencakup autentikasi backend, otorisasi route, dan halaman frontend yang kosong/placeholder. Semua masalah telah diperbaiki.

---

## 1. Masalah & Perbaikan Authentication

### 1.1 Endpoint `/register` Tidak Ada di Backend
| | Detail |
|---|---|
| **Status** | ❌ Bug (Kritis) |
| **File** | `routes/api.php`, `app/Http/Controllers/Api/AuthController.php` |
| **Masalah** | `authService.js` frontend memanggil `POST /register` untuk pendaftaran staff, tetapi endpoint ini tidak pernah didefinisikan di backend. Semua request register akan mengembalikan 404. |
| **Perbaikan** | Menambahkan `Route::post('/register', [AuthController::class, 'register'])` ke `routes/api.php` dan membuat method `register()` di `AuthController`. |

**Perilaku baru:** Staff (dokter/terapis) dapat mendaftar mandiri. Akun baru otomatis berstatus `inactive` — menunggu aktivasi oleh Admin. Ini sesuai SRS: *"Admin dapat mengaktifkan/menonaktifkan user."*

```php
// AuthController::register() — Logika Utama
$user = User::create([
    'role'   => $request->role,   // hanya 'dokter' atau 'terapis'
    'status' => 'inactive',       // wajib aktivasi Admin
    ...
]);
```

---

### 1.2 `patientService.js` — Double Prefix `/api/`
| | Detail |
|---|---|
| **Status** | ❌ Bug (Kritis) |
| **File** | `frontend/src/modules/patient-portal/services/patientService.js` |
| **Masalah** | Service memanggil `/api/pasien/login`, `/api/pasien/register`, dll. Karena `axios baseURL` sudah berisi `/api`, request dikirim ke `/api/api/pasien/login` → 404 selalu. |
| **Perbaikan** | Hapus prefix `/api/` — ubah semua URL menjadi `/pasien/login`, `/pasien/register`, dll. |

```js
// Sebelum (SALAH)
apiClient.post('/api/pasien/login', ...)

// Sesudah (BENAR)
apiClient.post('/pasien/login', ...)
```

---

### 1.3 `patientService.js` — Import Path Salah
| | Detail |
|---|---|
| **Status** | ❌ Bug (Kritis) |
| **File** | `frontend/src/modules/patient-portal/services/patientService.js` |
| **Masalah** | Import dari `../../../services/api` (path tidak ada) bukan dari `../../../core/services/api` (path yang benar). |
| **Perbaikan** | Diubah ke `import apiClient from '../../../core/services/api'` |

---

### 1.4 `patientStore.js` — Import Service dari Path Salah
| | Detail |
|---|---|
| **Status** | ❌ Bug (Kritis) |
| **File** | `frontend/src/modules/patient-portal/stores/patientStore.js` |
| **Masalah** | Import `patientService` dari `'../../../services/patientService'` (direktori tidak ada). Seharusnya dari `'../services/patientService'` (relative ke module). |
| **Perbaikan** | Import path diperbaiki. |

```js
// Sebelum (SALAH)
import { patientService } from '../../../services/patientService'

// Sesudah (BENAR)
import { patientService } from '../services/patientService'
```

---

## 2. Masalah & Perbaikan Authorization

### 2.1 Route '/' Duplikat di `patientPortalRoutes.js`
| | Detail |
|---|---|
| **Status** | ❌ Bug (Sedang) |
| **File** | `frontend/src/modules/patient-portal/router/patientPortalRoutes.js` |
| **Masalah** | File ini mendefinisikan `path: '/'` → `LandingPage.vue`, yang identik dengan definisi yang sudah ada di `router/index.js`. Vue Router akan melempar warning dan hanya satu yang efektif (tergantung urutan import), menyebabkan perilaku tidak terduga. |
| **Perbaikan** | Hapus definisi `'/'` dari `patientPortalRoutes.js`. Route `'/'` hanya ada di `router/index.js`. |

---

### 2.2 Role Authorization — DashboardView.vue Tidak Handle Role `terapis`
| | Detail |
|---|---|
| **Status** | ❌ Bug (Sedang) |
| **File** | `frontend/src/modules/dashboard/views/DashboardView.vue` |
| **Masalah** | Dashboard hanya render `AdminDashboard` atau `DoctorDashboard`. Role `terapis` (valid sesuai SRS) mendapatkan pesan fallback *"Dashboard belum tersedia untuk role Anda"* alih-alih dashboard yang sesuai. |
| **Perbaikan** | Tambahkan `TerapisDashboard` component dan kondisi `v-else-if="role === 'terapis'"`. |

```html
<!-- Sebelum -->
<DoctorDashboard v-if="role === 'dokter'" />
<AdminDashboard v-else-if="role === 'admin'" />
<div v-else>Dashboard belum tersedia</div>  ← terapis kena ini

<!-- Sesudah -->
<AdminDashboard v-if="role === 'admin'" />
<DoctorDashboard v-else-if="role === 'dokter'" />
<TerapisDashboard v-else-if="role === 'terapis'" />
```

---

### 2.3 Pengecekan Role di `patientPortalRoutes.js` — `meta.requiresAuth` Tidak Konsisten
| | Detail |
|---|---|
| **Status** | ⚠️ Warning |
| **File** | `frontend/src/modules/patient-portal/router/patientPortalRoutes.js` |
| **Masalah** | Route `/pasien/login` dan `/pasien/register` tidak memiliki `meta.requiresAuth: false` secara eksplisit. Navigation guard di `router/index.js` sudah handle ini dengan benar (cek `to.meta.requiresAuth`), tapi deklarasi eksplisit lebih aman. |
| **Perbaikan** | Ditambahkan `meta: { requiresAuth: false }` pada kedua route. |

---

## 3. Masalah & Perbaikan Frontend Views

### 3.1 `RegisterPage.vue` — Kosong (0 bytes)
| | Detail |
|---|---|
| **Status** | ❌ Bug (Kritis) |
| **File** | `frontend/src/modules/auth/views/RegisterPage.vue` |
| **Masalah** | File benar-benar kosong. Route `/register` ada tapi tidak ada tampilan. Staff tidak dapat mendaftar. |
| **Perbaikan** | Dibuat form registrasi lengkap. |

**Fitur form yang dibuat:**
- Input: Nama Lengkap, Email, NIP, No. Telepon
- Dropdown Role: Dokter / Terapis (Admin tidak bisa self-register)
- Password + Konfirmasi Password
- Validasi client-side (password match)
- Feedback: pesan sukses (redirect ke login) & pesan error
- Styling konsisten dengan LandingPage

---

### 3.2 `DoctorDashboard.vue` (modules/dokter) — Nama Dokter Hardcoded
| | Detail |
|---|---|
| **Status** | ❌ Bug (Sedang) |
| **File** | `frontend/src/modules/dokter/views/DoctorDashboard.vue` |
| **Masalah** | Komponen menampilkan `doctorName: 'John Doe'` yang di-hardcode, tidak mengambil dari autentikasi yang sedang aktif. |
| **Perbaikan** | Gunakan `authStore.user?.name` dari Pinia auth store. |

```js
// Sebelum (SALAH)
data() { return { doctorName: 'John Doe' } }

// Sesudah (BENAR)
const authStore = useAuthStore()
// Template: Dr. {{ authStore.user?.name }}
```

---

### 3.3 `TerapisDashboard` — Tidak Ada (Role terapis tidak punya dashboard)
| | Detail |
|---|---|
| **Status** | ❌ Fitur Hilang |
| **File** | `frontend/src/modules/dashboard/views/TerapisDashboard.vue` (dibuat baru) |
| **Masalah** | Tidak ada komponen dashboard untuk role `terapis`. SRS mendefinisikan terapis sebagai role yang valid dengan akses ke terapi, monitoring, dan data pasien. |
| **Perbaikan** | Membuat `TerapisDashboard.vue` dengan stats cards dan quick links yang relevan (Program Terapi, Monitoring, Data Pasien, Analitik). |

---

### 3.4 Portal Pasien — 4 Views Masih Placeholder
| | Detail |
|---|---|
| **Status** | ❌ Fitur Hilang |
| **Files** | `PatientHistoryView.vue`, `PatientQueueView.vue`, `PatientProfileView.vue`, `PatientScheduleView.vue` |
| **Masalah** | Semua 4 halaman hanya menampilkan teks *"dalam pengembangan"* / placeholder. Pasien tidak dapat melihat riwayat, antrian, jadwal, atau profil. |

**Perbaikan — Detail per halaman:**

| Halaman | Fitur yang Ditambahkan |
|---|---|
| `PatientHistoryView.vue` | Daftar assessment medis (diagnosis, ICD-10, dokter, tanggal) + daftar program terapi (jenis, terapis, jumlah sesi, status) |
| `PatientQueueView.vue` | Nomor antrian aktif dengan tampilan besar berwarna + info (poli, dokter, antrian saat ini) + riwayat antrian |
| `PatientProfileView.vue` | Tampilan profil + form edit (nama, telepon, NIK, tanggal lahir, alamat) dengan mode read-only dan edit |
| `PatientScheduleView.vue` | Sesi mendatang dengan tanggal kalender + sesi lampau, grouped by upcoming/past |

---

## 4. Ringkasan Perbaikan

| # | File | Kategori | Severity |
|---|---|---|---|
| 1 | `routes/api.php` | Auth Backend | 🔴 Kritis |
| 2 | `AuthController.php` | Auth Backend | 🔴 Kritis |
| 3 | `patientService.js` (double /api prefix) | Auth Frontend | 🔴 Kritis |
| 4 | `patientService.js` (import path) | Auth Frontend | 🔴 Kritis |
| 5 | `patientStore.js` (import path) | Auth Frontend | 🔴 Kritis |
| 6 | `RegisterPage.vue` (kosong) | Frontend View | 🔴 Kritis |
| 7 | `patientPortalRoutes.js` (duplikat route /) | Authorization | 🟠 Sedang |
| 8 | `DashboardView.vue` (terapis fallback) | Authorization | 🟠 Sedang |
| 9 | `DoctorDashboard.vue` (hardcoded name) | Frontend View | 🟠 Sedang |

**Ditambahkan:**
- `TerapisDashboard.vue` — dashboard baru untuk role terapis
- `patientService.updateProfile()` — method yang digunakan `PatientProfileView`
- Semua 4 patient portal views dengan UI fungsional

---

## 5. Status Kesesuaian dengan SRS

### Authentication (FR-001)
| Requirement | Status Sebelum | Status Sesudah |
|---|---|---|
| Login multi-role (Admin, Dokter, Terapis) | ✅ Ada | ✅ Ada |
| Login Pasien | ✅ Ada | ✅ Ada (fixed double prefix) |
| Registrasi Staff | ❌ 404 error | ✅ Berfungsi |
| Token-based (Sanctum) | ✅ Ada | ✅ Ada |
| Status akun wajib `active` untuk login | ✅ Ada | ✅ Ada |

### Authorization (Role-Based Access)
| Role | Dashboard | Akses Menu |
|---|---|---|
| Admin | ✅ `AdminDashboard` | Users, Settings, semua fitur |
| Dokter | ✅ `DoctorDashboard` | Queue, Assessment, Therapy, Monitoring, Reports |
| Terapis | ✅ `TerapisDashboard` (baru) | Therapy, Monitoring, Patients |
| Pasien | ✅ `PatientDashboard` | Portal Pasien (isolated) |

### Frontend Views
| View | Status Sebelum | Status Sesudah |
|---|---|---|
| RegisterPage | ❌ Kosong | ✅ Lengkap |
| DoctorDashboard | ❌ Hardcoded | ✅ Dynamic |
| TerapisDashboard | ❌ Tidak ada | ✅ Dibuat |
| PatientHistoryView | ❌ Placeholder | ✅ Fungsional |
| PatientQueueView | ❌ Placeholder | ✅ Fungsional |
| PatientProfileView | ❌ Placeholder | ✅ Fungsional |
| PatientScheduleView | ❌ Placeholder | ✅ Fungsional |

---

## 6. File yang Dimodifikasi

```
app/Http/Controllers/Api/AuthController.php       ← tambah method register()
routes/api.php                                     ← tambah route POST /register

frontend/src/modules/auth/views/RegisterPage.vue  ← dibuat dari awal
frontend/src/modules/dashboard/views/
  DashboardView.vue                               ← tambah TerapisDashboard
  TerapisDashboard.vue                            ← dibuat baru
frontend/src/modules/dokter/views/
  DoctorDashboard.vue                             ← ganti hardcoded name
frontend/src/modules/patient-portal/
  router/patientPortalRoutes.js                   ← hapus duplikat route '/'
  services/patientService.js                      ← fix prefix & import, tambah updateProfile
  stores/patientStore.js                          ← fix import path
  views/PatientHistoryView.vue                    ← dibuat dari awal
  views/PatientQueueView.vue                      ← dibuat dari awal
  views/PatientProfileView.vue                    ← dibuat dari awal
  views/PatientScheduleView.vue                   ← dibuat dari awal
```

---

*Laporan ini dihasilkan dari audit manual terhadap source code vs SRS SITARA — 28 Juni 2026.*
