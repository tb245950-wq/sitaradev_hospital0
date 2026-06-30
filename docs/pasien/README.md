# 🧑‍🦽 Pasien — Dokumentasi Role

**Role:** `pasien`  
**Deskripsi:** Pasien menggunakan **Portal Pasien** — area terpisah dari portal staff. Pasien dapat mendaftar sendiri, booking antrian, melihat jadwal terapi, dan mengakses riwayat medisnya.  
**Versi:** 1.0.0

---

## 📋 Daftar Isi

1. [Akses & Fitur](#akses--fitur)
2. [Registrasi & Login](#registrasi--login)
3. [Dashboard Pasien](#dashboard-pasien)
4. [Booking Antrian](#booking-antrian)
5. [Jadwal Terapi](#jadwal-terapi)
6. [Riwayat Medis](#riwayat-medis)
7. [Profil Pasien](#profil-pasien)
8. [API Endpoints](#api-endpoints)
9. [Batasan Akses](#batasan-akses)

---

## Akses & Fitur

| Fitur | Akses | Keterangan |
|-------|:-----:|-----------|
| Registrasi mandiri | ✅ | Daftar via `/pasien/register` |
| Login portal pasien | ✅ | Via `/pasien/login` |
| Dashboard pribadi | ✅ | Ringkasan antrian & jadwal |
| Booking antrian | ✅ | Pilih poli & dokter |
| Lihat antrian saya | ✅ | Status antrian aktif |
| Jadwal terapi | ✅ | Jadwal sesi terapi mendatang |
| Riwayat medis | ✅ | Rekam medis milik sendiri |
| Edit profil | ✅ | Update data pribadi |
| Data pasien lain | ❌ | Hanya data milik sendiri |
| Portal staff | ❌ | Terpisah sepenuhnya |
| Assessment orang lain | ❌ | Privasi medis terjaga |

---

## Registrasi & Login

### Portal Pasien — URL Terpisah dari Staff

| Halaman | URL |
|---------|-----|
| Landing page | `/` |
| Login pasien | `/pasien/login` |
| Registrasi pasien | `/pasien/register` |
| Dashboard pasien | `/pasien/dashboard` |

> Portal pasien menggunakan **prefix `/pasien/`** untuk memisahkan dari portal staff (`/login`, `/dashboard`).

### Registrasi

Pasien mendaftar sendiri:
1. Buka `http://localhost:5173/pasien/register`
2. Isi: Nama lengkap, email, password, NIK (opsional), nomor telepon
3. Submit → akun langsung aktif
4. Login untuk mengakses portal

### Login

```
URL: http://localhost:5173/pasien/login
Email: [email yang didaftarkan]
Password: [password yang dibuat saat registrasi]
```

Setelah login → redirect ke `/pasien/dashboard`

---

## Dashboard Pasien

### Halaman
- **URL:** `/pasien/dashboard`

### Tampilan

```
┌─────────────────────────────────────────┐
│         DASHBOARD PASIEN                │
│         Selamat datang, [Nama]!         │
├─────────────────────────────────────────┤
│  Antrian Aktif Saya   │  Terapi Terdekat│
│  [nomor / status]     │  [jadwal]       │
├─────────────────────────────────────────┤
│  [Booking Antrian]  [Lihat Jadwal]      │
│  [Riwayat Medis]    [Edit Profil]       │
├─────────────────────────────────────────┤
│  Antrian Saya (tabel status terkini)    │
└─────────────────────────────────────────┘
```

### Endpoint Dashboard
```
GET /api/pasien/dashboard
Authorization: Bearer {patient_token}
```

---

## Booking Antrian

### Halaman
- **URL:** `/pasien/booking`

### Alur Booking

```
1. Pasien buka halaman Booking
2. Pilih Poli / Layanan (dari daftar poli aktif)
3. Pilih Dokter yang tersedia
4. Tentukan tanggal kunjungan
5. Submit → nomor antrian diterbitkan
6. Pasien mendapat konfirmasi booking
```

### Informasi Booking

| Field | Keterangan |
|-------|-----------|
| Poli | Dipilih dari daftar poli aktif |
| Dokter | Dipilih dari daftar dokter tersedia |
| Tanggal | Tanggal kunjungan yang diinginkan |
| Keluhan | Deskripsi keluhan (opsional) |

### Endpoints Booking

```
POST /api/pasien/booking
{
  "poli_id": 1,
  "dokter_id": 2,
  "tanggal": "2026-07-01",
  "keluhan": "Nyeri punggung"
}

GET /api/pasien/antrian-saya    ← Lihat antrian aktif
GET /api/pasien/doctors         ← Daftar dokter tersedia
GET /api/pasien/polis           ← Daftar poli aktif
```

---

## Jadwal Terapi

### Halaman
- **URL:** `/pasien/jadwal-terapi` (via portal) atau view di dashboard

Pasien dapat melihat:
- Jadwal sesi terapi yang sudah dijadwalkan oleh dokter/terapis
- Status sesi (terjadwal / selesai / dibatalkan)
- Nama terapis yang menangani
- Lokasi / ruangan sesi

### Endpoint

```
GET /api/pasien/jadwal-terapi
Authorization: Bearer {patient_token}
```

---

## Riwayat Medis

### Halaman
- **URL:** `/pasien/riwayat-medis`

Pasien dapat melihat **hanya rekam medis miliknya sendiri**:
- Daftar assessment yang pernah dibuat dokter
- Diagnosis yang ditegakkan
- Terapi yang pernah dijalani
- Catatan monitoring

> Data pasien lain tidak dapat diakses. Privasi medis sepenuhnya terjaga.

### Endpoint

```
GET /api/pasien/riwayat-medis
Authorization: Bearer {patient_token}
```

---

## Profil Pasien

### Halaman
- **URL:** `/pasien/profile`

Pasien dapat melihat dan mengubah data pribadi:

| Field | Bisa Diubah |
|-------|:-----------:|
| Nama lengkap | ✅ |
| Nomor telepon | ✅ |
| Alamat | ✅ |
| Email | ✅ |
| Password | ✅ |
| NIK | ❌ (kontak admin) |
| Tanggal lahir | ❌ (kontak admin) |

### Endpoints Profil

```
GET  /api/pasien/profile           ← Lihat profil
PUT  /api/pasien/profile           ← Update profil
```

---

## API Endpoints

### Autentikasi Pasien (Terpisah dari Staff)

Pasien menggunakan **endpoint terpisah** dari staff:

```
POST /api/pasien/login       ← Login pasien
POST /api/pasien/register    ← Registrasi pasien
POST /api/pasien/logout      ← Logout pasien
GET  /api/pasien/user        ← Data user yang login
```

### Endpoint Fitur Pasien

```
GET  /api/pasien/dashboard         ← Dashboard info
GET  /api/pasien/profile           ← Lihat profil
PUT  /api/pasien/profile           ← Update profil
POST /api/pasien/booking           ← Booking antrian
GET  /api/pasien/doctors           ← Daftar dokter
GET  /api/pasien/polis             ← Daftar poli aktif
GET  /api/pasien/antrian-saya      ← Antrian aktif saya
GET  /api/pasien/jadwal-terapi     ← Jadwal terapi saya
GET  /api/pasien/riwayat-medis     ← Riwayat medis saya
```

### Contoh Registrasi & Login

```bash
# Registrasi pasien baru
curl -X POST http://localhost:8000/api/pasien/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Budi Santoso",
    "email": "budi@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "081234567890"
  }' | jq .

# Login pasien
TOKEN=$(curl -s -X POST http://localhost:8000/api/pasien/login \
  -H "Content-Type: application/json" \
  -d '{"email":"budi@example.com","password":"password123"}' \
  | jq -r '.data.token')

# Booking antrian
curl -X POST http://localhost:8000/api/pasien/booking \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "poli_id": 1,
    "dokter_id": 2,
    "tanggal": "2026-07-01",
    "keluhan": "Nyeri punggung bawah"
  }' | jq .
```

---

## Token Pasien vs Token Staff

Penting: Pasien dan staff menggunakan **token yang berbeda** dan disimpan di localStorage dengan key berbeda:

| | Staff | Pasien |
|--|-------|--------|
| localStorage key | `token` | `patient_token` |
| localStorage user | `user` | `patient_user` |
| Login endpoint | `/api/login` | `/api/pasien/login` |
| Logout endpoint | `/api/logout` | `/api/pasien/logout` |

---

## Batasan Akses

Pasien **tidak dapat** mengakses area staff manapun:

| Area | Keterangan |
|------|-----------|
| `/dashboard` | Portal staff |
| `/patients/*` | Manajemen data pasien (staff) |
| `/assessments/*` | Assessment medis (staff) |
| `/therapies/*` | Manajemen terapi (staff) |
| `/monitoring/*` | Monitoring (staff) |
| `/queue/*` | Antrian staff |
| `/super-admin/*` | Administrasi sistem |
| Data pasien lain | Privasi medis terjaga |

---

## Setup Akun Pasien via Tinker (Testing)

```bash
php artisan tinker

# Buat akun pasien untuk testing
User::create([
    'name'     => 'Pasien Test',
    'email'    => 'pasien@test.com',
    'role'     => 'pasien',
    'password' => Hash::make('password123'),
    'status'   => 'active',
    'nik'      => '3301010101900001',
    'phone'    => '081234567890',
]);
exit
```

> Di production, pasien mendaftar sendiri melalui halaman `/pasien/register`. Tidak perlu persetujuan admin — akun langsung aktif.

---

*Lihat juga: [Admin](../admin/README.md) | [Dokter](../dokter/README.md) | [Terapis](../terapis/README.md)*
