# Deploy Sitara Hospital ke Render.com

Panduan lengkap deploy **Sitara Hospital Management System** (Laravel 11 + Vue.js 3 + PostgreSQL) ke [Render.com](https://render.com).

---

## Arsitektur di Render

```
Internet
   │
   ├─► sitara-frontend.onrender.com  (Static Site — Vue.js build)
   │         │ VITE_API_BASE_URL
   │         ▼
   └─► sitara-backend.onrender.com   (Web Service — Laravel Docker)
              │ DB_URL
              ▼
         sitara-db  (PostgreSQL — Render managed)
```

---

## Prasyarat

- [ ] Akun [Render.com](https://dashboard.render.com) (gratis untuk free tier)
- [ ] Repository di GitHub / GitLab (public atau private)
- [ ] `APP_KEY` Laravel — generate lokal dulu: `php artisan key:generate --show`

---

## Cara Deploy (Blueprint — Otomatis)

Cara tercepat: gunakan `render.yaml` yang sudah ada di root project.

1. Buka [dashboard.render.com](https://dashboard.render.com)
2. Klik **New → Blueprint**
3. Connect repository GitHub kamu
4. Render akan membaca `render.yaml` dan membuat 3 resource sekaligus:
   - `sitara-db` (PostgreSQL)
   - `sitara-backend` (Web Service)
   - `sitara-frontend` (Static Site)
5. Klik **Apply** dan tunggu deploy selesai (±5–10 menit pertama kali)

---

## Cara Deploy Manual (Step by Step)

Kalau mau kontrol lebih atau Blueprint tidak cocok, deploy manual satu per satu.

### Langkah 1 — Buat PostgreSQL Database

1. Dashboard → **New → PostgreSQL**
2. Isi:
   - **Name**: `sitara-db`
   - **Database**: `hospital`
   - **User**: `hospital`
   - **Region**: Singapore (paling dekat dari Indonesia)
   - **Plan**: Free (atau Starter untuk production)
3. Klik **Create Database**
4. Catat **Internal Database URL** (format: `postgresql://hospital:xxx@xxx.oregon-postgres.render.com/hospital`)

---

### Langkah 2 — Deploy Backend (Laravel)

1. Dashboard → **New → Web Service**
2. Connect repository, pilih repo `sitaradev_hospital0`
3. Konfigurasi:

   | Field | Value |
   |-------|-------|
   | **Name** | `sitara-backend` |
   | **Region** | Singapore |
   | **Runtime** | Docker |
   | **Dockerfile Path** | `./Dockerfile` |
   | **Docker Context** | `.` (root) |
   | **Plan** | Free |

4. Scroll ke **Environment Variables**, tambahkan:

   | Key | Value |
   |-----|-------|
   | `APP_NAME` | `Sitara Hospital` |
   | `APP_ENV` | `production` |
   | `APP_DEBUG` | `false` |
   | `APP_KEY` | `base64:xxxx` (hasil `php artisan key:generate --show`) |
   | `APP_URL` | `https://sitara-backend.onrender.com` |
   | `DB_CONNECTION` | `pgsql` |
   | `DB_URL` | *(pilih dari database `sitara-db` → Connection String)* |
   | `DB_SSLMODE` | `require` |
   | `SESSION_DRIVER` | `database` |
   | `CACHE_STORE` | `database` |
   | `QUEUE_CONNECTION` | `database` |
   | `LOG_CHANNEL` | `stderr` |
   | `LOG_LEVEL` | `error` |
   | `FRONTEND_URL` | `https://sitara-frontend.onrender.com` |

5. Klik **Create Web Service**
6. Tunggu build selesai. Cek log untuk memastikan migrasi berhasil.

> **Tip:** Untuk set `DB_URL`, di bagian Environment Variables klik **Add from Database** → pilih `sitara-db` → pilih property `connectionString`.

---

### Langkah 3 — Deploy Frontend (Vue.js)

1. Dashboard → **New → Static Site**
2. Connect repository yang sama
3. Konfigurasi:

   | Field | Value |
   |-------|-------|
   | **Name** | `sitara-frontend` |
   | **Region** | Singapore |
   | **Root Directory** | `frontend` |
   | **Build Command** | `npm ci && npm run build` |
   | **Publish Directory** | `dist` |

4. Environment Variables:

   | Key | Value |
   |-----|-------|
   | `VITE_APP_NAME` | `SITARA` |
   | `VITE_API_BASE_URL` | `https://sitara-backend.onrender.com/api` |

5. Klik **Create Static Site**

---

## Verifikasi Setelah Deploy

### Cek Backend

```bash
# Health check
curl https://sitara-backend.onrender.com/api/health

# Respons yang diharapkan:
# {"status":"ok","timestamp":"..."}
```

### Cek Frontend

Buka `https://sitara-frontend.onrender.com` di browser — halaman login harus muncul.

### Cek Koneksi Frontend → Backend

1. Buka DevTools → Network
2. Login dengan salah satu akun (lihat `docs/KRENDESIAL_LOGIN_SEMUA_USERS.txt`)
3. Request ke `sitara-backend.onrender.com/api/login` harus `200 OK`

---

## Masalah Umum & Solusi

### Build Docker gagal: `composer install` error

Pastikan `backend/composer.lock` ada di Git dan tidak di-ignore.

### Migrasi gagal: `DB_URL` tidak terbaca

Pastikan `DB_URL` diset dari **Connection String** database Render, bukan di-hardcode. Format:
```
postgresql://hospital:PASSWORD@HOST/hospital?sslmode=require
```

Jika menggunakan `DB_URL`, variabel `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` **tidak perlu** diset — Laravel akan parsing dari URL.

### CORS error di frontend

1. Cek `backend/config/cors.php` — URL frontend harus ada di `allowed_origins` atau cocok dengan `allowed_origins_patterns`
2. Jalankan `php artisan config:clear` (via Render Shell atau redeploy)

### Frontend loading tapi API 404

Cek `VITE_API_BASE_URL` di environment variables frontend — harus diakhiri `/api` tanpa trailing slash:
```
https://sitara-backend.onrender.com/api  ✅
https://sitara-backend.onrender.com/api/ ❌
```

### Service sleep di free tier

Render free tier akan sleep setelah 15 menit tidak ada traffic. Request pertama setelah sleep butuh ~30 detik. Untuk production, gunakan plan **Starter** ($7/bulan).

### Storage file hilang setelah redeploy

Render free tier tidak punya persistent disk. File yang diupload (foto pasien, dll) akan hilang saat redeploy. Solusi:
- Gunakan **Render Disk** (berbayar), atau
- Migrasi storage ke **S3 / Cloudflare R2** dan set `FILESYSTEM_DISK=s3`

---

## Environment Variables Lengkap — Backend

| Key | Contoh | Keterangan |
|-----|--------|------------|
| `APP_NAME` | `Sitara Hospital` | Nama aplikasi |
| `APP_ENV` | `production` | Wajib `production` |
| `APP_KEY` | `base64:xxx` | Generate: `php artisan key:generate --show` |
| `APP_DEBUG` | `false` | Wajib `false` di production |
| `APP_URL` | `https://sitara-backend.onrender.com` | URL backend |
| `DB_CONNECTION` | `pgsql` | Jenis database |
| `DB_URL` | *(dari Render dashboard)* | Connection string PostgreSQL |
| `DB_SSLMODE` | `require` | Wajib untuk Render Postgres |
| `SESSION_DRIVER` | `database` | Simpan session di DB |
| `CACHE_STORE` | `database` | Simpan cache di DB |
| `QUEUE_CONNECTION` | `database` | Queue di DB |
| `LOG_CHANNEL` | `stderr` | Log ke Render dashboard |
| `LOG_LEVEL` | `error` | Hanya log error di production |
| `FRONTEND_URL` | `https://sitara-frontend.onrender.com` | Untuk CORS |

---

## Environment Variables — Frontend

| Key | Contoh | Keterangan |
|-----|--------|------------|
| `VITE_APP_NAME` | `SITARA` | Nama app di UI |
| `VITE_API_BASE_URL` | `https://sitara-backend.onrender.com/api` | URL API backend |

---

## Auto-Deploy

Setiap `git push` ke branch `main` akan otomatis trigger redeploy di Render. Tidak perlu langkah manual.

Untuk disable auto-deploy: Dashboard → Service → Settings → Auto-Deploy → Off.

---

## Struktur File Deploy

```
sitaradev_hospital0/
├── render.yaml              ← Blueprint Render (buat semua service sekaligus)
├── Dockerfile               ← Backend Laravel (Apache, untuk Render)
├── Dockerfile.backend       ← Backend Laravel (php-fpm, untuk Docker Compose VPS)
├── docker-compose.yml       ← Untuk deploy VPS / lokal
├── backend/
│   └── config/
│       └── cors.php         ← CORS sudah include *.onrender.com
└── frontend/
    ├── Dockerfile           ← Frontend Vue (multi-stage, untuk Docker)
    ├── .env.render          ← Template env vars untuk Render
    └── .env.production      ← Template env vars untuk Vercel / Railway
```

---

*Dokumentasi dibuat: Agustus 2026*
