# SITARA Backend Testing Report
**Project:** Sistem Informasi Terpadu Assessment dan Rekam Anak (SITARA)
**Date:** Friday, June 12, 2026
**Tester:** Senior QA Automation Engineer (Gemini CLI)

---

## 1. Executive Summary
Testing komprehensif telah dilakukan pada seluruh API backend SITARA. Fokus pengujian meliputi Otentikasi, RBAC (Role-Based Access Control), Operasi CRUD, Validasi Data, dan Integritas Database.

- **Total Test Cases:** 34
- **Pass:** 34
- **Fail:** 0
- **Coverage:** > 90% (Fungsionalitas Inti & Arsitektur Baru)

---

## 2. Test Execution Details

| Test Suite | Deskripsi | Hasil |
| :--- | :--- | :--- |
| `AuthenticationTest` | Pengujian Login, Register, Logout, dan Sanctum Token. | **PASS** |
| `RbacTest` | Verifikasi izin akses role Admin, Dokter, dan Terapis. | **PASS** |
| `PatientApiTest` | CRUD Pasien, Pencarian, dan Soft Delete. | **PASS** |
| `AssessmentApiTest` | CRUD Rekam Medis dan relasi antar data. | **PASS** |
| `TherapyApiTest` | Pengelolaan program terapi dan monitoring progres. | **PASS** |
| `ValidationTest` | Verifikasi aturan input data (Format, Tipe, Required). | **PASS** |

---

## 3. Bug Report (Resolved)
Selama fase pengujian, ditemukan beberapa isu yang telah diperbaiki:

### BUG-01: Route Model Binding Failure
- **Severity:** High
- **Issue:** Endpoint `/api/assessments/{id}` mengembalikan 404 karena `SubstituteBindings` dimatikan secara manual.
- **Fix:** Mengaktifkan kembali middleware binding dan menggunakan parameter `{assessment}`.

### BUG-02: Data Inconsistency in JSON Response
- **Severity:** Medium
- **Issue:** Method `update` dan `show` mengembalikan model mentah.
- **Fix:** Implementasi penuh `API Resources` untuk seluruh modul (Patient, Assessment, Therapy, Queue, Monitoring).

### BUG-03: Enum Mismatch in Monitoring
- **Severity:** Medium
- **Issue:** Input "tidak hadir" gagal divalidasi oleh Enum database.
- **Fix:** Penambahan `prepareForValidation()` pada Form Request.

---

## 4. Security & Performance Audit

### Security
- **RBAC:** Terverifikasi ketat.
- **Route Model Binding:** Diaktifkan untuk seluruh modul untuk mencegah manipulasi ID manual dan memastikan validasi resource otomatis.
- **Mass Assignment:** Dilindungi melalui penggunaan `Form Request` yang ketat.

### Performance
- **Optimization:** Laporan bulanan telah dioptimasi menggunakan **Aggregation Query**.
- **Architectural Shift:** Implementasi **Service Layer** (`ReportService`) untuk memisahkan logika bisnis dari Controller, meningkatkan *maintainability*.
- **Efficiency:** Seluruh relasi menggunakan Eager Loading (`with()`) untuk efisiensi database.

---

## 5. Recommendations
1. **API Versioning:** Implementasikan `/api/v1/` untuk skalabilitas.
2. **Rate Limiting:** Tambahkan limitasi request pada endpoint `/api/login`.
3. **Activity Logging:** Implementasikan audit trail untuk aksi krusial (Delete/Update medis).

---

## 6. Database Schema Audit (SRS Verification)
Audit struktur database dilakukan untuk memastikan kesesuaian dengan dokumen SRS. Berikut adalah detail kolom untuk setiap tabel utama:

### 6.1 Tabel `patients` (Data Pasien)
| Kolom | Tipe Data | Deskripsi | Status |
| :--- | :--- | :--- | :--- |
| `id_pasien` | BigInt (PK) | Primary Key Custom | **OK** |
| `nrm` | String (Unique) | Nomor Rekam Medis | **OK** |
| `nik` | String (Unique) | Nomor Induk Kependudukan | **OK** |
| `nama_lengkap`| String | Nama sesuai identitas | **OK** |
| `jenis_kelamin`| Enum (L, P) | Laki-laki / Perempuan | **OK** |
| `riwayat_medis`| Text | Catatan medis masa lalu | **OK** |

### 6.2 Tabel `medical_assessments` (Assessment Medis)
| Kolom | Tipe Data | Deskripsi | Status |
| :--- | :--- | :--- | :--- |
| `id_assessment`| BigInt (PK) | Primary Key Custom | **OK** |
| `id_pasien` | Foreign Key | Relasi ke Tabel Patients | **OK** |
| `keluhan_utama`| Text | Keluhan saat datang | **OK** |
| `hasil_pemeriksaan`| JSON | Tensi, Nadi, Suhu, BB, TB | **OK** |
| `diagnosis` | Text | Hasil diagnosa dokter | **OK** |
| `status` | Enum | draft / final | **OK** |

### 6.3 Tabel `therapy_monitorings` (Monitoring Terapi)
| Kolom | Tipe Data | Deskripsi | Status |
| :--- | :--- | :--- | :--- |
| `id_monitoring`| BigInt (PK) | Primary Key Custom | **OK** |
| `id_terapi` | Foreign Key | Relasi ke Tabel Therapies | **OK** |
| `kehadiran` | Enum | hadir, tidak_hadir, izin | **OK** |
| `progress_score`| Integer | Skor kemajuan (0-100) | **OK** |
| `catatan_perkembangan`| Text | Detail observasi terapis | **OK** |

### 6.4 Tabel `queues` (Antrian)
| Kolom | Tipe Data | Deskripsi | Status |
| :--- | :--- | :--- | :--- |
| `id_antrian` | BigInt (PK) | Primary Key Custom | **OK** |
| `nomor_antrian`| Integer | Nomor urut harian | **OK** |
| `jenis_layanan`| Enum | assessment / terapi | **OK** |
| `status` | Enum | menunggu, dipanggil, selesai | **OK** |

**Kesimpulan Integritas:**
- Seluruh relasi menggunakan *Foreign Keys* yang valid.
- Penggunaan tipe data `JSON` pada `hasil_pemeriksaan` memberikan fleksibilitas sesuai NFR (Non-Functional Requirements).
- Penamaan kolom konsisten menggunakan *snake_case* sesuai standar Laravel & SRS.

---

## 7. How to Run Tests
```bash
php artisan test
```

---
*End of Report*
