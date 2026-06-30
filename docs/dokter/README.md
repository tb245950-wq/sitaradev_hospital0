# 👨‍⚕️ Dokter — Dokumentasi Role

**Role:** `dokter`  
**Deskripsi:** Dokter memiliki akses ke fitur klinis — assessment pasien, terapi, monitoring, dan laporan medis. Dokter tidak mengelola operasional administratif klinik.  
**Versi:** 1.0.0

---

## 📋 Daftar Isi

1. [Akses & Fitur](#akses--fitur)
2. [Login & Dashboard](#login--dashboard)
3. [Data Pasien](#data-pasien)
4. [Antrian](#antrian)
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
| Dashboard Klinis | ✅ | Statistik pasien & assessment dokter |
| Data Pasien | ✅ | Lihat, tambah, edit (tidak bisa hapus) |
| Antrian | ✅ | Lihat & update status antrian |
| Assessment Medis | ✅ | Buat, lihat, edit, submit assessment |
| Terapi | ✅ | Buat & kelola sesi terapi |
| Monitoring | ✅ | Pantau perkembangan pasien |
| Laporan Medis | ✅ | Lihat laporan pasiennya |
| Analytics Dashboard | ✅ | Data statistik sudut pandang dokter |
| Poli (read) | ✅ | Lihat daftar poli aktif |
| Hapus Pasien | ❌ | Hanya admin yang bisa |
| User Management | ❌ | Khusus Super Admin |
| Poli CRUD | ❌ | Khusus Super Admin |
| Audit Logs | ❌ | Khusus Super Admin |

---

## Login & Dashboard

### Login
- URL: `http://localhost:5173/login`
- Role yang diizinkan: `dokter`
- Setelah login → redirect ke `/dashboard` → tampil **DoctorDashboard**

### Tampilan Dashboard Dokter

```
┌─────────────────────────────────────────┐
│         DASHBOARD DOKTER                │
├──────────┬──────────┬────────┬──────────┤
│  Pasien  │Assessment│ Terapi │Monitoring│
│  Saya    │ Hari Ini │ Aktif  │ Aktif    │
├──────────┴──────────┴────────┴──────────┤
│  Tren Kunjungan (grafik — sudut dokter) │
├─────────────────────────────────────────┤
│  Aktivitas Terbaru (assessment & terapi)│
└─────────────────────────────────────────┘
```

### Menu Sidebar Dokter
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

## Data Pasien

### Halaman
- **URL:** `/patients`

### Operasi yang Tersedia

| Aksi | Dokter | Endpoint |
|------|:------:|----------|
| Lihat daftar pasien | ✅ | GET `/api/patients` |
| Lihat detail pasien | ✅ | GET `/api/patients/{id}` |
| Tambah pasien | ✅ | POST `/api/patients` |
| Edit data pasien | ✅ | PUT `/api/patients/{id}` |
| Hapus pasien | ❌ | — (hanya admin) |

> Dokter dapat melihat semua pasien klinik, bukan hanya pasien yang ditanganinya.

---

## Antrian

### Halaman
- **URL:** `/queue`

### Operasi yang Tersedia

| Aksi | Dokter | Endpoint |
|------|:------:|----------|
| Lihat antrian | ✅ | GET `/api/queues` |
| Statistik antrian | ✅ | GET `/api/queues/stats` |
| Tambah antrian | ✅ | POST `/api/queues` |
| Update status | ✅ | PUT `/api/queues/{id}` |
| Hapus antrian | ✅ | DELETE `/api/queues/{id}` |
| Panggil antrian | ✅ | POST `/api/queues/call-next` |

---

## Assessment Medis

Ini adalah fitur **utama** dokter — mencatat pemeriksaan klinis pasien.

### Halaman
- **URL:** `/assessment` — Daftar assessment
- **URL:** `/assessment/create` — Buat assessment baru

### Operasi yang Tersedia

| Aksi | Dokter | Endpoint |
|------|:------:|----------|
| Daftar assessment | ✅ | GET `/api/assessments` |
| Detail assessment | ✅ | GET `/api/assessments/{id}` |
| Buat assessment | ✅ | POST `/api/assessments` |
| Edit assessment | ✅ | PUT `/api/assessments/{id}` |
| Hapus assessment | ✅ | DELETE `/api/assessments/{id}` |
| Submit assessment | ✅ | POST `/api/assessments/{id}/submit` |

### Data yang Diisi Dokter

```
Pasien        : [pilih pasien]
Keluhan utama : [teks bebas]
Diagnosis     : [teks / kode ICD]
Riwayat penyakit : [teks]
Catatan medis : [teks bebas]
Rekomendasi   : Terapi / Rawat inap / Rujuk
Status        : Draft → Submit
```

### Alur Assessment

```
1. Dokter buka /assessment/create
2. Pilih pasien dari antrian
3. Isi data pemeriksaan
4. Simpan sebagai draft (bisa diedit)
5. Submit → status final, tidak bisa diedit
6. Sistem mencatat di audit log
```

---

## Terapi

### Halaman
- **URL:** `/therapy`

### Operasi yang Tersedia

| Aksi | Dokter | Endpoint |
|------|:------:|----------|
| Daftar terapi | ✅ | GET `/api/therapies` |
| Detail terapi | ✅ | GET `/api/therapies/{id}` |
| Buat terapi | ✅ | POST `/api/therapies` |
| Edit terapi | ✅ | PUT `/api/therapies/{id}` |
| Hapus terapi | ✅ | DELETE `/api/therapies/{id}` |

> Dokter dapat meresepkan terapi untuk pasien. Terapis kemudian menjalankan sesi berdasarkan resep ini.

---

## Monitoring

### Halaman
- **URL:** `/monitoring`

### Operasi yang Tersedia

| Aksi | Dokter | Endpoint |
|------|:------:|----------|
| Daftar monitoring | ✅ | GET `/api/monitoring` |
| Detail monitoring | ✅ | GET `/api/monitoring/{id}` |
| Catat monitoring | ✅ | POST `/api/monitoring` |
| Update monitoring | ✅ | PUT `/api/monitoring/{id}` |
| Hapus monitoring | ✅ | DELETE `/api/monitoring/{id}` |

---

## Laporan Medis

### Halaman
- **URL:** `/reports`

Dokter dapat melihat laporan yang terkait dengan pasien yang ditanganinya, termasuk:
- Riwayat assessment
- Sesi terapi
- Perkembangan monitoring
- Ringkasan statistik klinis

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
✅ GET/POST/PUT     /api/patients/*   (hapus: hanya admin)
✅ GET/POST/PUT/DELETE  /api/queues/*
✅ POST   /api/queues/call-next
✅ GET/POST/PUT/DELETE  /api/assessments/*
✅ POST   /api/assessments/{id}/submit
✅ GET/POST/PUT/DELETE  /api/therapies/*
✅ GET/POST/PUT/DELETE  /api/monitoring/*
❌ GET    /api/super-admin/*  → 403 Forbidden
```

### Contoh Login & Ambil Data Assessment

```bash
# Login
TOKEN=$(curl -s -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"dokter@sitara.com","password":"password123"}' \
  | jq -r '.data.token')

# Lihat daftar assessment
curl -X GET http://localhost:8000/api/assessments \
  -H "Authorization: Bearer $TOKEN" | jq .
```

---

## Batasan Akses

Dokter **tidak dapat** mengakses:

| Area | Keterangan |
|------|-----------|
| Hapus pasien | Hanya admin |
| `/super-admin/*` | Khusus Super Admin |
| User Management | Khusus Super Admin |
| Poli CRUD | Khusus Super Admin |
| Audit Logs | Khusus Super Admin |
| Portal Pasien | Khusus pasien |

---

## Setup Akun Dokter

```bash
php artisan tinker

User::create([
    'name'     => 'Dr. Ahmad Fauzi',
    'email'    => 'dokter@sitara.com',
    'role'     => 'dokter',
    'password' => Hash::make('password123'),
    'status'   => 'active',
    'nip'      => 'DKT001',
]);
exit
```

> Dokter juga bisa mendaftar sendiri via `/register` dengan memilih role `dokter`. Akun akan berstatus `inactive` hingga diaktifkan oleh Super Admin.

---

*Lihat juga: [Admin](../admin/README.md) | [Terapis](../terapis/README.md) | [Super Admin](../super-admin/README.md)*
