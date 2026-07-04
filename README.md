# 🏥 Sitaradev Hospital Management System

Sistem informasi manajemen rumah sakit berbasis **Laravel 11** + **Vue.js 3**.

[![Build Status](https://github.com/laravel/framework/workflows/tests/badge.svg)](https://github.com/laravel/framework/actions)
[![Version](https://img.shields.io/badge/version-1.0.0-blue)](.)
[![License](https://img.shields.io/badge/license-MIT-green)](.)

---

## 🚀 Quick Start

```bash
# 1. Clone & install dependencies
git clone https://github.com/tb245950-wq/sitaradev_hospital0
composer install
cd frontend && npm install && cd ..

# 2. Setup environment
cp .env.example .env
php artisan key:generate

# 3. Setup database
php artisan migrate --seed

# 4. Jalankan server
php artisan serve          # Backend: http://localhost:8000
cd frontend && npm run dev # Frontend: http://localhost:5173
```

---

## 📚 Dokumentasi

Semua dokumentasi tersedia di folder **[docs/](./docs/README.md)**:

| Dokumen | Deskripsi |
|---------|-----------|
| [docs/super-admin/](./docs/super-admin/README.md) | Panduan lengkap role Super Admin |
| [docs/deployment/CHECKLIST.md](./docs/deployment/CHECKLIST.md) | Checklist deployment production |
| [docs/development/START_HERE.md](./docs/development/START_HERE.md) | Panduan untuk developer baru |
| [docs/CRITICAL_BUGS_FIX_REPORT.md](./docs/CRITICAL_BUGS_FIX_REPORT.md) | ✅ **NEW** - Report perbaikan 4 critical bugs |

---

## 🔐 Role & Permission

| Role | Deskripsi | Akses |
|------|-----------|-------|
| `super_admin` | System Administrator | User management, audit logs, poli, backup |
| `admin` | Admin Klinik | Pasien, antrian, assessment, terapi, laporan |
| `dokter` | Dokter | Assessment, terapi, monitoring pasien |
| `terapis` | Terapis | Terapi, monitoring |

---

## 🛠️ Tech Stack

**Backend**
- Framework: Laravel 11
- Authentication: Laravel Sanctum (token-based)
- Database: MySQL / SQLite
- PHP: 8.2+

**Frontend**
- Framework: Vue.js 3 (Composition API)
- State Management: Pinia
- Build Tool: Vite
- HTTP Client: Axios

---

## 📂 Struktur Proyek

```
sitaradev_hospital0/
├── app/                    # Laravel application
│   ├── Http/Controllers/Api/   # API Controllers
│   ├── Models/                 # Eloquent Models
│   └── Http/Middleware/        # Custom Middlewares
├── database/               # Migrations & Seeders
├── routes/
│   ├── api.php             # API routes
│   └── web.php             # Web routes (SPA entry)
├── frontend/               # Vue.js SPA
│   └── src/
│       ├── modules/        # Feature modules (auth, dashboard, super-admin, ...)
│       ├── router/         # Vue Router
│       ├── shared/         # Shared components & layouts
│       └── core/           # Core services (API client, middleware)
└── docs/                   # 📖 Semua dokumentasi
```

---

## 🧪 Testing

```bash
# PHP Unit Tests
php artisan test

# Frontend
cd frontend && npm run build
```

---

## 👥 Kontributor

- **muhammadmughni-lab** — Lead Developer
- **faisalajax** — Co-Developer

---

## 📝 Lisensi

MIT License — lihat [LICENSE](./LICENSE) untuk detail.

---

*Dibuat dengan ❤️ untuk kesehatan Indonesia*
