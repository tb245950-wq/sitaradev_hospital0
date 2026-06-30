# 📚 Dokumentasi Sitaradev Hospital

Selamat datang di dokumentasi lengkap **Sitaradev Hospital Management System** — sistem manajemen rumah sakit berbasis Laravel 11 + Vue.js 3.

---

## 🎨 [DESIGN.md](./DESIGN.md)
Dokumen desain keseluruhan website — branding, color palette, layout, komponen UI, arsitektur, dan data flow.

---

## 📁 Struktur Dokumentasi per Role

### 🔑 [Super Admin](./super-admin/README.md) — `role: super_admin`
Administrasi sistem (user management, audit logs, backup):

| Dokumen | Deskripsi |
|---------|-----------|
| [README.md](./super-admin/README.md) | Overview, quick start, FAQ |
| [IMPLEMENTATION.md](./super-admin/IMPLEMENTATION.md) | Panduan implementasi teknis lengkap |
| [ROUTES.md](./super-admin/ROUTES.md) | Daftar semua API & frontend routes |
| [SECURITY.md](./super-admin/SECURITY.md) | Panduan keamanan & role isolation |
| [TESTING.md](./super-admin/TESTING.md) | Panduan testing lengkap dengan cURL |

### 🏥 [Admin Klinik](./admin/README.md) — `role: admin`
Operasional klinik: pasien, antrian, assessment, terapi, laporan.

### 👨‍⚕️ [Dokter](./dokter/README.md) — `role: dokter`
Fitur klinis: assessment, terapi, monitoring, laporan medis.

### 🧠 [Terapis](./terapis/README.md) — `role: terapis`
Sesi terapi & monitoring perkembangan pasien.

### 🧑‍🦽 [Pasien](./pasien/README.md) — `role: pasien`
Portal pasien: booking antrian, jadwal terapi, riwayat medis.

---

## 🔐 Matrix Akses Singkat

| Fitur | super_admin | admin | dokter | terapis | pasien |
|-------|:-----------:|:-----:|:------:|:-------:|:------:|
| User Management | ✅ | ❌ | ❌ | ❌ | ❌ |
| Poli CRUD | ✅ | ❌ | ❌ | ❌ | ❌ |
| Audit Logs | ✅ | ❌ | ❌ | ❌ | ❌ |
| Data Pasien | ❌ | ✅ | ✅ | ✅ (read) | ✅ (own) |
| Antrian | ❌ | ✅ | ✅ | ✅ | ✅ (booking) |
| Assessment | ❌ | ✅ | ✅ | ❌ | ❌ |
| Terapi | ❌ | ✅ | ✅ | ✅ | ❌ |
| Monitoring | ❌ | ✅ | ✅ | ✅ | ❌ |
| Laporan Medis | ❌ | ✅ | ✅ | ❌ | ❌ |
| Analytics | ❌ | ✅ | ✅ | ✅ | ❌ |
| Portal Pasien | ❌ | ❌ | ❌ | ❌ | ✅ |

---

## 📁 Dokumentasi Lainnya

### 🎨 [Frontend](./frontend/)
Dokumentasi sistem Alert, komponen UI, dan panduan pengembangan frontend:

| File | Deskripsi |
|------|-----------|
| [ALERT_SYSTEM.md](./frontend/ALERT_SYSTEM.md) | Dokumentasi sistem alert/notifikasi |
| [ALERT_SYSTEM_SUMMARY.md](./frontend/ALERT_SYSTEM_SUMMARY.md) | Ringkasan implementasi alert |
| [IMPLEMENTATION_CHECKLIST.md](./frontend/IMPLEMENTATION_CHECKLIST.md) | Checklist implementasi frontend |
| [QUICK_REFERENCE.md](./frontend/QUICK_REFERENCE.md) | Cheat sheet komponen frontend |
| [EXAMPLE_USAGE.md](./frontend/EXAMPLE_USAGE.md) | Contoh penggunaan komponen |
| [performance-audit.md](./frontend/performance-audit.md) | Catatan audit performa |

### 📄 [SRS & Dokumen Formal](./srs/)
Dokumen spesifikasi kebutuhan sistem (Software Requirements Specification):

| File | Deskripsi |
|------|-----------|
| [SITARA_SRS_FINAL_02.pdf](./srs/SITARA_SRS_FINAL_02.pdf) | SRS Final (PDF) |
| [SRS_SITARA(2) (1).docx](./srs/SRS_SITARA(2)%20(1).docx) | SRS versi Word |

### 📊 Laporan & Analisis
| File | Deskripsi |
|------|-----------|
| [DATABASE_SCHEMA_REPORT.md](./srs/DATABASE_SCHEMA_REPORT.md) | Laporan skema database |
| [TESTING_REPORT_SITARA.md](./srs/TESTING_REPORT_SITARA.md) | Laporan hasil testing |
| [SECURITY_ANALYSIS.md](./srs/SECURITY_ANALYSIS.md) | Analisis keamanan sistem |
| [SECURITY_SETUP.md](./srs/SECURITY_SETUP.md) | Panduan setup keamanan |
| [IMPROVEMENT_REPORT.md](./srs/IMPROVEMENT_REPORT.md) | Laporan improvement |
| [SRS_SITARA_REVIEW.md](./srs/SRS_SITARA_REVIEW.md) | Review SRS |

### 🚀 [Deployment](./deployment/CHECKLIST.md)
Checklist dan panduan deployment ke production.

### 🛠️ [Development](./development/START_HERE.md)
Panduan untuk developer yang baru bergabung.

---

## 🚀 Quick Start

```bash
# 1. Install dependencies
composer install && npm install

# 2. Setup environment
cp .env.example .env
php artisan key:generate

# 3. Setup database
php artisan migrate --seed

# 4. Buat user Super Admin
php artisan tinker
User::create([
    'name' => 'Super Admin', 'email' => 'superadmin@sitara.com',
    'role' => 'super_admin', 'password' => Hash::make('password123'),
    'status' => 'active'
]);

# 5. Jalankan development server
php artisan serve
cd frontend && npm run dev
```

---

## 🛠️ Tech Stack

- **Backend:** Laravel 11, PHP 8.2+, Laravel Sanctum
- **Frontend:** Vue.js 3, Pinia, Vite
- **Database:** MySQL / SQLite (dev)
- **Auth:** Token-based (Sanctum)

---

*Terakhir diupdate: 30 Juni 2026*
