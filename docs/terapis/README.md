# 🧠 Terapis — Dokumentasi Role

**Role:** `terapis`  
**Deskripsi:** Terapis bertugas menjalankan sesi terapi dan memantau perkembangan pasien. Terapis tidak membuat assessment medis — itu tugas dokter.  
**Versi:** 1.0.0

---

## 📋 Daftar Isi

1. [Akses & Fitur](#akses--fitur)
2. [Login & Dashboard](#login--dashboard)
3. [Data Pasien](#data-pasien)
4. [Antrian](#antrian)
5. [Terapi](#terapi)
6. [Monitoring](#monitoring)
7. [API Endpoints](#api-endpoints)
8. [Batasan Akses](#batasan-akses)

---

## Akses & Fitur

| Fitur | Akses | Keterangan |
|-------|:-----:|-----------|
| Dashboard Terapis | ✅ | Sesi terapi aktif & jadwal |
| Data Pasien (read) | ✅ | Lihat data pasien yang ditangani |
| Antrian | ✅ | Lihat & update status antrian |
| Terapi | ✅ | Jalankan & catat sesi terapi |
| Monitoring | ✅ | Catat perkembangan pasien |
| Analytics Dashboard | ✅ | Data statistik sudut pandang terapis |
| Poli (read) | ✅ | Lihat daftar poli aktif |
| Assessment Medis | ❌ | Khusus dokter & admin |
| Hapus Pasien | ❌ | Hanya admin |
| Laporan Medis | ❌ | Khusus admin & dokter |
| User Management | ❌ | Khusus Super Admin |
| Poli CRUD | ❌ | Khusus Super Admin |

---

## Login & Dashboard

### Login
- URL: `http://localhost:5173/login`
- Role yang diizinkan: `terapis`
- Setelah login → redirect ke `/dashboard` → tampil **TerapisDashboard**

### Tampilan Dashboard Terapis

```
┌─────────────────────────────────────────┐
│         DASHBOARD TERAPIS               │
├──────────┬──────────┬────────┬──────────┤
│  Sesi    │ Terapi   │Tingkat │Monitoring│
│  Saya    │  Aktif   │Kehadiran│ Aktif   │
├──────────┴──────────┴────────┴──────────┤
│  Jadwal Sesi Terapi Hari Ini (tabel)    │
├─────────────────────────────────────────┤
│  Aktivitas Terbaru                      │
└─────────────────────────────────────────┘
```

### Menu Sidebar Terapis
```
📊 Dashboard
👥 Data Pasien
🎫 Antrian
🧠 Terapi
📈 Monitoring
```

> Terapis tidak melihat menu Assessment dan Laporan Medis karena tidak memiliki akses ke fitur tersebut.

---

## Data Pasien

### Halaman
- **URL:** `/patients`

### Operasi yang Tersedia

| Aksi | Terapis | Endpoint |
|------|:-------:|----------|
| Lihat daftar pasien | ✅ | GET `/api/patients` |
| Lihat detail pasien | ✅ | GET `/api/patients/{id}` |
| Tambah pasien | ✅ | POST `/api/patients` |
| Edit data pasien | ✅ | PUT `/api/patients/{id}` |
| Hapus pasien | ❌ | — (hanya admin) |

> Terapis hanya memerlukan akses **baca** untuk mengetahui data pasien sebelum sesi terapi. Penambahan/edit pasien umumnya dilakukan oleh admin atau dokter.

---

## Antrian

### Halaman
- **URL:** `/queue`

### Operasi yang Tersedia

| Aksi | Terapis | Endpoint |
|------|:-------:|----------|
| Lihat antrian | ✅ | GET `/api/queues` |
| Statistik antrian | ✅ | GET `/api/queues/stats` |
| Tambah antrian | ✅ | POST `/api/queues` |
| Update status | ✅ | PUT `/api/queues/{id}` |
| Hapus antrian | ✅ | DELETE `/api/queues/{id}` |
| Panggil antrian | ✅ | POST `/api/queues/call-next` |

---

## Terapi

Ini adalah fitur **utama** terapis — menjalankan dan mencatat sesi terapi berdasarkan rekomendasi dokter.

### Halaman
- **URL:** `/therapy` — Daftar sesi terapi
- **URL:** `/therapy/create` — Buat sesi terapi baru

### Operasi yang Tersedia

| Aksi | Terapis | Endpoint |
|------|:-------:|----------|
| Daftar terapi | ✅ | GET `/api/therapies` |
| Detail terapi | ✅ | GET `/api/therapies/{id}` |
| Buat terapi | ✅ | POST `/api/therapies` |
| Edit terapi | ✅ | PUT `/api/therapies/{id}` |
| Hapus terapi | ✅ | DELETE `/api/therapies/{id}` |

### Data Sesi Terapi yang Dicatat Terapis

```
Pasien           : [nama pasien]
Tanggal sesi     : [tanggal & waktu]
Jenis terapi     : [pilih jenis]
Durasi           : [menit]
Catatan sesi     : [observasi terapis]
Kondisi pasien   : [sebelum & sesudah sesi]
Rekomendasi      : [tindak lanjut]
```

### Alur Sesi Terapi

```
1. Terapis login → lihat jadwal di dashboard
2. Buka /therapy → filter sesi hari ini
3. Pilih pasien → mulai sesi
4. Catat observasi selama sesi
5. Simpan catatan sesi
6. Update status terapi (selesai/lanjut)
7. Buat catatan monitoring jika diperlukan
```

---

## Monitoring

Terapis mencatat perkembangan pasien setelah setiap sesi terapi.

### Halaman
- **URL:** `/monitoring`

### Operasi yang Tersedia

| Aksi | Terapis | Endpoint |
|------|:-------:|----------|
| Daftar monitoring | ✅ | GET `/api/monitoring` |
| Detail monitoring | ✅ | GET `/api/monitoring/{id}` |
| Catat monitoring | ✅ | POST `/api/monitoring` |
| Update monitoring | ✅ | PUT `/api/monitoring/{id}` |
| Hapus monitoring | ✅ | DELETE `/api/monitoring/{id}` |

### Data Monitoring yang Dicatat

```
Pasien           : [nama pasien]
Tanggal          : [tanggal pencatatan]
Kondisi umum     : [baik / cukup / perlu perhatian]
Perkembangan     : [deskripsi perkembangan]
Catatan khusus   : [hal penting yang perlu dokter tahu]
Tindak lanjut    : [rekomendasi sesi berikutnya]
```

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
✅ GET/POST/PUT     /api/patients/*   (hapus: tidak bisa)
✅ GET/POST/PUT/DELETE  /api/queues/*
✅ POST   /api/queues/call-next
✅ GET/POST/PUT/DELETE  /api/therapies/*
✅ GET/POST/PUT/DELETE  /api/monitoring/*
❌ GET    /api/assessments/*   → 403 Forbidden
❌ GET    /api/super-admin/*   → 403 Forbidden
```

### Contoh Login & Ambil Jadwal Terapi

```bash
# Login
TOKEN=$(curl -s -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"terapis@sitara.com","password":"password123"}' \
  | jq -r '.data.token')

# Lihat daftar terapi
curl -X GET http://localhost:8000/api/therapies \
  -H "Authorization: Bearer $TOKEN" | jq .

# Catat monitoring baru
curl -X POST http://localhost:8000/api/monitoring \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "patient_id": 1,
    "catatan": "Pasien menunjukkan perkembangan positif",
    "kondisi": "baik"
  }' | jq .
```

---

## Batasan Akses

Terapis **tidak dapat** mengakses:

| Area | Keterangan |
|------|-----------|
| Assessment medis | Khusus dokter & admin |
| Hapus pasien | Hanya admin |
| Laporan Medis | Khusus admin & dokter |
| `/super-admin/*` | Khusus Super Admin |
| User Management | Khusus Super Admin |
| Poli CRUD | Khusus Super Admin |
| Audit Logs | Khusus Super Admin |
| Portal Pasien | Khusus pasien terdaftar |

---

## Setup Akun Terapis

```bash
php artisan tinker

User::create([
    'name'     => 'Siti Rahayu, S.Tr.Keb',
    'email'    => 'terapis@sitara.com',
    'role'     => 'terapis',
    'password' => Hash::make('password123'),
    'status'   => 'active',
    'nip'      => 'TRP001',
]);
exit
```

> Terapis juga bisa mendaftar sendiri via `/register` dengan memilih role `terapis`. Akun akan berstatus `inactive` hingga diaktifkan oleh Super Admin.

---

*Lihat juga: [Admin](../admin/README.md) | [Dokter](../dokter/README.md) | [Super Admin](../super-admin/README.md)*
