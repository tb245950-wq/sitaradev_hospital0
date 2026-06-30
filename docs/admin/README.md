# 🏥 Admin Klinik — Dokumentasi Role

**Role:** `admin`  
**Deskripsi:** Admin Klinik bertanggung jawab atas seluruh operasional klinik sehari-hari — mulai dari manajemen data pasien, antrian, assessment, hingga laporan medis.  
**Versi:** 1.0.0

---

## 📋 Daftar Isi

1. [Akses & Fitur](#akses--fitur)
2. [Login & Dashboard](#login--dashboard)
3. [Manajemen Pasien](#manajemen-pasien)
4. [Manajemen Antrian](#manajemen-antrian)
5. [Assessment Medis](#assessment-medis)
6. [Terapi](#terapi)
7. [Monitoring](#monitoring)
8. [Laporan Medis](#laporan-medis)
9. [API Endpoints](#api-endpoints)
10. [Batasan Akses](#batasan-akses)

---

## Akses & Fitur

| Fitur | Akses | Keterangan |
|-------|:-----:|-----------|
| Dashboard Operasional | ✅ | Statistik pasien, antrian, assessment |
| Data Pasien (CRUD) | ✅ | Tambah, lihat, edit, hapus pasien |
| Antrian | ✅ | Kelola & panggil antrian |
| Assessment Medis | ✅ | Buat & kelola assessment |
| Terapi | ✅ | Buat & kelola sesi terapi |
| Monitoring | ✅ | Pantau perkembangan pasien |
| Laporan Medis | ✅ | Generate & lihat laporan |
| Analytics Dashboard | ✅ | Tren kunjungan & diagnosis |
| Poli (read) | ✅ | Lihat daftar poli |
| User Management | ❌ | Dipindah ke Super Admin |
| Poli CRUD | ❌ | Dipindah ke Super Admin |
| Audit Logs | ❌ | Khusus Super Admin |

---

## Login & Dashboard

### Login
- URL: `http://localhost:5173/login`
- Role yang diizinkan: `admin`
- Setelah login → redirect ke `/dashboard` → tampil **AdminDashboard**

### Tampilan Dashboard Admin
Dashboard menampilkan ringkasan operasional:

```
┌─────────────────────────────────────────┐
│          DASHBOARD ADMIN KLINIK         │
├──────────┬──────────┬────────┬──────────┤
│  Total   │  Pasien  │Antrian │Assessment│
│  Pasien  │  Baru    │Menunggu│ Hari Ini │
│  (total) │(hari ini)│        │          │
├──────────┴──────────┴────────┴──────────┤
│  Terapi Aktif   │  Storage Terpakai     │
├─────────────────┴───────────────────────┤
│  Tren Kunjungan Pasien (grafik)         │
├─────────────────────────────────────────┤
│  Distribusi Diagnosis (grafik)          │
├─────────────────────────────────────────┤
│  Antrian Aktif Hari Ini (tabel)         │
├─────────────────────────────────────────┤
│  Aktivitas Terbaru (log)                │
└─────────────────────────────────────────┘
```

### Menu Sidebar Admin
```
📊 Dashboard
👥 Data Pasien
🎫 Antrian
📋 Assessment
🧠 Terapi
📈 Monitoring
📄 Laporan Medis
```

---

## Manajemen Pasien

### Halaman
- **URL:** `/patients`
- **Fitur:** Daftar semua pasien, search, filter

### Operasi yang Tersedia

| Aksi | Method | Endpoint |
|------|--------|----------|
| Lihat daftar pasien | GET | `/api/patients` |
| Lihat detail pasien | GET | `/api/patients/{id}` |
| Tambah pasien baru | POST | `/api/patients` |
| Edit data pasien | PUT | `/api/patients/{id}` |
| Hapus pasien | DELETE | `/api/patients/{id}` |

### Data Pasien yang Dikelola
- Nama lengkap, NIK, tanggal lahir, jenis kelamin
- Alamat, nomor telepon
- Riwayat penyakit
- Dokter / terapis yang menangani

---

## Manajemen Antrian

### Halaman
- **URL:** `/queue`
- **Fitur:** Lihat, kelola, dan panggil antrian pasien

### Operasi yang Tersedia

| Aksi | Method | Endpoint |
|------|--------|----------|
| Lihat antrian | GET | `/api/queues` |
| Statistik antrian | GET | `/api/queues/stats` |
| Tambah antrian | POST | `/api/queues` |
| Update status antrian | PUT | `/api/queues/{id}` |
| Hapus antrian | DELETE | `/api/queues/{id}` |
| Panggil antrian berikutnya | POST | `/api/queues/call-next` |

### Status Antrian
- `menunggu` → Pasien sudah daftar, belum dipanggil
- `dipanggil` → Sedang dipanggil masuk
- `selesai` → Sudah dilayani

---

## Assessment Medis

### Halaman
- **URL:** `/assessment` — Daftar assessment
- **URL:** `/assessment/create` — Buat assessment baru

### Operasi yang Tersedia

| Aksi | Method | Endpoint |
|------|--------|----------|
| Daftar assessment | GET | `/api/assessments` |
| Detail assessment | GET | `/api/assessments/{id}` |
| Buat assessment | POST | `/api/assessments` |
| Edit assessment | PUT | `/api/assessments/{id}` |
| Hapus assessment | DELETE | `/api/assessments/{id}` |
| Submit assessment | POST | `/api/assessments/{id}/submit` |

### Data Assessment
- ID pasien, ID dokter
- Keluhan utama, diagnosis
- Riwayat penyakit, hasil pemeriksaan
- Rekomendasi terapi
- Status (draft / submitted)

---

## Terapi

### Halaman
- **URL:** `/therapy` — Daftar sesi terapi
- **URL:** `/therapy/create` — Buat sesi terapi baru

### Operasi yang Tersedia

| Aksi | Method | Endpoint |
|------|--------|----------|
| Daftar terapi | GET | `/api/therapies` |
| Detail terapi | GET | `/api/therapies/{id}` |
| Buat terapi | POST | `/api/therapies` |
| Edit terapi | PUT | `/api/therapies/{id}` |
| Hapus terapi | DELETE | `/api/therapies/{id}` |

---

## Monitoring

### Halaman
- **URL:** `/monitoring`

### Operasi yang Tersedia

| Aksi | Method | Endpoint |
|------|--------|----------|
| Daftar monitoring | GET | `/api/monitoring` |
| Detail monitoring | GET | `/api/monitoring/{id}` |
| Buat catatan monitoring | POST | `/api/monitoring` |
| Update monitoring | PUT | `/api/monitoring/{id}` |
| Hapus monitoring | DELETE | `/api/monitoring/{id}` |

---

## Laporan Medis

### Halaman
- **URL:** `/reports`

Laporan yang dapat diakses admin:
- Ringkasan kunjungan pasien
- Statistik assessment & diagnosis
- Laporan terapi aktif
- Export laporan (jika tersedia)

---

## API Endpoints

Semua endpoint memerlukan header:
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Ringkasan Akses API

```
✅ GET    /api/analytics/dashboard
✅ GET    /api/polis
✅ GET/POST/PUT/DELETE  /api/patients/*
✅ GET/POST/PUT/DELETE  /api/queues/*
✅ POST   /api/queues/call-next
✅ GET/POST/PUT/DELETE  /api/assessments/*
✅ POST   /api/assessments/{id}/submit
✅ GET/POST/PUT/DELETE  /api/therapies/*
✅ GET/POST/PUT/DELETE  /api/monitoring/*
❌ GET    /api/super-admin/*  → 403 Forbidden
```

### Contoh Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@sitara.com", "password": "password123"}'
```

---

## Batasan Akses

Admin Klinik **tidak dapat** mengakses:

| Area | Keterangan |
|------|-----------|
| `/super-admin/*` | Khusus Super Admin |
| User Management | Dipindah ke Super Admin |
| Poli CRUD | Hanya Super Admin |
| Audit Logs | Khusus Super Admin |
| Login History | Khusus Super Admin |
| Portal Pasien | Khusus pasien terdaftar |

---

## Setup Akun Admin

```bash
php artisan tinker

User::create([
    'name'     => 'Admin Klinik',
    'email'    => 'admin@sitara.com',
    'role'     => 'admin',
    'password' => Hash::make('password123'),
    'status'   => 'active',
    'nip'      => 'ADM001',
]);
exit
```

> Akun baru yang didaftarkan lewat `/register` berstatus `inactive` dan perlu diaktifkan oleh Super Admin.

---

*Lihat juga: [Super Admin](../super-admin/README.md) | [Dokter](../dokter/README.md) | [Terapis](../terapis/README.md)*
