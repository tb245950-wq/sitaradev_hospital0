# 🎨 SITARA — Design Document

**Sistem Informasi Terpadu Assessment dan Rekam Anak**  
**Versi:** 1.0.0 | **Tanggal:** 30 Juni 2026

---

## 📋 Daftar Isi

1. [Identitas & Branding](#identitas--branding)
2. [Arsitektur Aplikasi](#arsitektur-aplikasi)
3. [Struktur Halaman & Navigasi](#struktur-halaman--navigasi)
4. [Design System](#design-system)
5. [Layout & Komponen UI](#layout--komponen-ui)
6. [Desain per Portal](#desain-per-portal)
7. [Responsivitas](#responsivitas)
8. [State Management & Data Flow](#state-management--data-flow)
9. [Keamanan Frontend](#keamanan-frontend)

---

## Identitas & Branding

### Nama & Kepanjangan
**SITARA** — *Sistem Informasi Terpadu Assessment dan Rekam Anak*

### Deskripsi
Aplikasi manajemen klinik tumbuh kembang anak berbasis web. Mengelola seluruh alur layanan: booking antrian, assessment medis, program terapi, hingga monitoring perkembangan pasien.

### Logo & Visual Identity
- **Logo:** `SITARA_RM_BG.png` — digunakan di semua navbar dan halaman login
- **Brand color utama:** `#1e40af` (biru tua) untuk portal staff
- **Brand color pasien:** `#10b981` / `#0f766e` (hijau teal) untuk portal pasien
- **Font:** Inter, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto (system font stack)
- **Tagline:** *"Pusat layanan terpadu untuk assessment dan tumbuh kembang anak"*

---

## Arsitektur Aplikasi

### Stack Teknologi

```
Frontend                    Backend
─────────────────────       ──────────────────────
Vue.js 3 (SPA)              Laravel 11
Vue Router 4                Laravel Sanctum (auth)
Pinia (state)               Posgres SQL
Vite 8 (build)              PHP 8.2+
Tailwind CSS 4
Axios (HTTP)
Chart.js + vue-chartjs
```

### Pola Arsitektur

```
Browser
  └── Vue.js SPA (http://localhost:5173)
        ├── Vue Router — client-side routing
        ├── Pinia Stores — global state
        └── Axios — HTTP ke backend API

Backend API
  └── Laravel (http://localhost:8000)
        ├── routes/api.php — semua endpoint
        ├── Sanctum — token auth
        └── MySQL — data persistence
```

### Pemisahan Portal

Aplikasi memiliki **dua portal terpisah** dalam satu SPA:

| Portal | URL Prefix | Pengguna | Token Storage |
|--------|-----------|---------|---------------|
| Staff | `/login`, `/dashboard`, `/super-admin/*` | super_admin, admin, dokter, terapis | `localStorage['token']` |
| Pasien | `/pasien/*` | pasien | `localStorage['patient_token']` |

---

## Struktur Halaman & Navigasi

### Peta Halaman Lengkap

```
/ (Landing Page)
├── /login                      ← Login staff
├── /register                   ← Registrasi staff (dokter/terapis)
│
├── /dashboard                  ← Dashboard (conditional per role)
│   ├── AdminDashboard          ← Jika role = admin
│   ├── DoctorDashboard         ← Jika role = dokter
│   ├── TerapisDashboard        ← Jika role = terapis
│   └── SuperAdminDashboard     ← (legacy, redirect ke /super-admin)
│
├── /patients                   ← Manajemen pasien
├── /queue                      ← Manajemen antrian
├── /assessment                 ← Assessment medis
├── /assessment/create          ← Buat assessment
├── /therapy                    ← Manajemen terapi
├── /therapy/create             ← Buat terapi
├── /monitoring                 ← Monitoring pasien
├── /reports                    ← Laporan medis
├── /users                      ← User management (admin)
├── /settings                   ← Pengaturan (admin)
│
├── /super-admin/               ← Area Super Admin
│   ├── /dashboard
│   ├── /users
│   ├── /polis
│   ├── /audit-logs
│   ├── /backup
│   └── /settings
│
└── /pasien/                    ← Portal Pasien
    ├── /login
    ├── /register
    ├── /dashboard
    ├── /antrian (booking)
    ├── /jadwal
    ├── /riwayat
    └── /profil
```

### Navigation Guard (Route Protection)

```
Request ke route
    │
    ├── Route /pasien/* ?
    │     ├── Tidak ada patient_token → /pasien/login
    │     └── Ada token → lanjut
    │
    ├── Route memerlukan auth?
    │     └── Tidak ada staff token → /login
    │
    └── Route punya meta.roles?
          ├── Role cocok → lanjut
          ├── Role super_admin → /super-admin/dashboard
          └── Role lain tidak cocok → /dashboard
```

---

## Design System

### Color Palette

```css
/* Primary — Portal Staff (Biru) */
--color-primary:        #1e40af;   /* Blue-800 — CTA utama, brand */
--color-primary-light:  #3b82f6;   /* Blue-500 — hover, aksen */
--color-primary-dark:   #1e3a8a;   /* Blue-900 — hover dark */

/* Secondary — Portal Pasien (Teal/Hijau) */
--color-secondary:      #0f766e;   /* Teal-700 */
--color-secondary-light:#10b981;   /* Emerald-500 */
--color-secondary-dark: #059669;   /* Emerald-600 */

/* Neutrals */
--color-bg:             #f8fafc;   /* Slate-50  — background utama */
--color-bg-white:       #ffffff;   /* Putih — card background */
--color-border:         #e2e8f0;   /* Slate-200 — border card */
--color-border-light:   #f1f5f9;   /* Slate-100 — border halus */

/* Text */
--color-text-dark:      #1e293b;   /* Slate-800 — heading */
--color-text-body:      #374151;   /* Gray-700  — body text */
--color-text-muted:     #64748b;   /* Slate-500 — subtitle, caption */
--color-text-faint:     #94a3b8;   /* Slate-400 — placeholder */

/* Sidebar */
--sidebar-bg:           #1e293b;   /* Slate-800 */
--sidebar-active:       #3b82f6;   /* Blue-500 */

/* Status Colors */
--color-success:        #059669;   /* Emerald-600 */
--color-success-light:  #d1fae5;   /* Emerald-100 */
--color-warning:        #d97706;   /* Amber-600 */
--color-warning-light:  #fef3c7;   /* Amber-100 */
--color-danger:         #dc2626;   /* Red-600 */
--color-danger-light:   #fee2e2;   /* Red-100 */
--color-info:           #7c3aed;   /* Violet-700 */
--color-info-light:     #ede9fe;   /* Violet-100 */
```

### Typography

```css
/* Font Stack */
font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;

/* Scale */
Heading H1 : 1.75rem  / 700  — Page title
Heading H2 : 1.5rem   / 700  — Section title
Heading H3 : 1.25rem  / 600  — Card title
Body       : 1rem     / 400  — Default text
Small      : 0.875rem / 400  — Caption, label
XSmall     : 0.75rem  / 500  — Badge, tag
```

### Spacing & Sizing

```
Padding card  : 1.5rem
Gap grid      : 1.25rem – 1.5rem
Border radius : 0.5rem (small), 0.75rem (card), 1rem (large), 1.25rem (hero card)
Sidebar width : 260px
Navbar height : 64px
Max content   : 1200px
```

### Shadow System

```css
/* Halus — card default */
box-shadow: 0 1px 3px rgba(0,0,0,0.08);

/* Medium — card hover */
box-shadow: 0 4px 12px rgba(0,0,0,0.10);

/* Hero card hover */
box-shadow: 0 20px 25px -5px rgba(0,0,0,0.10), 0 10px 10px -5px rgba(0,0,0,0.04);
```

---

## Layout & Komponen UI

### Layout Staff (Dashboard)

```
┌──────────────────────────────────────────────────┐
│  SIDEBAR (260px, fixed)  │  MAIN CONTENT (flex:1) │
│  ┌──────────────────┐   │  ┌───────────────────┐  │
│  │  Logo + Brand    │   │  │ NAVBAR (64px)     │  │
│  ├──────────────────┤   │  │ Toggle │ Title | User│  │
│  │  Nav Menu        │   │  ├───────────────────┤  │
│  │  - Dashboard     │   │  │                   │  │
│  │  - Data Pasien   │   │  │  CONTENT AREA     │  │
│  │  - Antrian       │   │  │  (router-view)    │  │
│  │  - ...           │   │  │                   │  │
│  ├──────────────────┤   │  └───────────────────┘  │
│  │  v1.0.0          │   │                         │
│  └──────────────────┘   │                         │
└──────────────────────────────────────────────────┘
```

**Sidebar** (`Sidebar.vue`):
- Background `#1e293b`, text `#cbd5e1`
- Active link: `background #3b82f6`, border kanan putih 4px
- Menu conditional per role (super_admin, admin, dokter, terapis)
- Fixed position, z-index 1000

**Navbar** (`Navbar.vue`):
- Height 64px, background white, sticky top
- Kiri: toggle sidebar (mobile) + judul halaman
- Kanan: nama user, role badge, tombol Logout
- pageTitle dinamis berdasarkan `route.name`

### Layout Portal Pasien

```
┌──────────────────────────────────────────────────┐
│ SIDEBAR PASIEN (260px, #1e293b, accent: #10b981) │
│  Logo + "Portal Pasien"                          │
│  Nav: Dashboard|Antrian|Jadwal|Riwayat|Profil    │
│  Footer: Avatar + Nama + Logout                  │
├──────────────────────────────────────────────────┤
│ MAIN CONTENT (margin-left: 260px, pad: 2rem)     │
│  Header: Selamat datang + Subtitle               │
│  Stats Grid (4 card)                             │
│  Quick Actions (3 card)                          │
│  Info Banner (green gradient)                    │
└──────────────────────────────────────────────────┘
```

Sidebar pasien menggunakan aksen **hijau (`#10b981`)** sebagai pembeda visual dari portal staff.

### Komponen Shared

| Komponen | File | Fungsi |
|----------|------|--------|
| `AlertNotification` | `shared/components/AlertNotification.vue` | Toast notification global |
| `NotificationManager` | `shared/components/NotificationManager.vue` | Mengelola antrian notifikasi |
| `MaskedNIK` | `shared/components/MaskedNIK.vue` | Tampilkan NIK dengan masking |
| `StatCard` | `analytics/components/StatCard.vue` | Kartu statistik dashboard |
| `VisitTrendsChart` | `analytics/components/` | Grafik tren kunjungan (Chart.js) |
| `DiagnosisDistributionChart` | `analytics/components/` | Grafik distribusi diagnosis |
| `RecentActivitiesTable` | `analytics/components/` | Tabel aktivitas terbaru |
| `Unauthorized` | `shared/components/common/` | Halaman 403 |
| `NotFound` | `shared/components/common/` | Halaman 404 |

### Stat Card Pattern

```
┌──────────────────────────────┐
│  [Icon Bg]  Title            │
│  [  Icon ]  Value (bold)     │
└──────────────────────────────┘
```
Digunakan di semua dashboard. Warna icon-bg & icon-color disesuaikan per metric.

---

## Desain per Portal

### 1. Landing Page (`/`)

**Tujuan:** Entry point — pilih portal staff atau pasien

```
┌─────────────────────────────────────────────────┐
│ NAVBAR (sticky, blur backdrop)                  │
│ [Logo] SITARA  Sistem Informasi Terpadu...      │
├─────────────────────────────────────────────────┤
│ HERO (gradient #eef2ff → #e0e7ff)               │
│         Selamat Datang di Klinik SITARA         │
│    Pusat layanan terpadu tumbuh kembang anak    │
│                                                 │
│  ┌─────────────┐    ┌─────────────┐            │
│  │ Portal Staff │    │Portal Pasien│            │
│  │  (biru)     │    │  (teal)    │            │
│  └─────────────┘    └─────────────┘            │
├─────────────────────────────────────────────────┤
│ SERVICES (4 card: Terapi Wicara, Okupasi, ...)  │
├─────────────────────────────────────────────────┤
│ CONTACT (3 card: Alamat, Telepon, Email)        │
├─────────────────────────────────────────────────┤
│ FOOTER (#0f172a)                                │
└─────────────────────────────────────────────────┘
```

**Detail visual:**
- Hero: gradient biru muda, dua radial gradient accent di sudut
- Portal cards: white, border-radius 1.25rem, hover lift -8px + shadow besar
- Section title: underline 4px `#1e40af`, centered
- Service cards: `#f8fafc` → white on hover

---

### 2. Login Staff (`/login`)

```
┌─────────────────────────────────────────────────┐
│ ← Kembali                                       │
│                                                 │
│  ┌────────────────────────────────────────┐     │
│  │ [Logo SITARA]      │  Form Login       │     │
│  │ Gradient biru      │  Email + Password │     │
│  │ SITARA             │  Ingat saya       │     │
│  │ Sistem Informasi   │  [Masuk]          │     │
│  │ Terpadu            │  Error message    │     │
│  │                    │  Info staff only  │     │
│  └────────────────────────────────────────┘     │
│                                                 │
│  (Lingkaran dekorasi di pojok)                  │
└─────────────────────────────────────────────────┘
```

- Layout dua kolom: kiri logo (gradient biru), kanan form
- Background: gradient `#f5f7fa → #ffffff`
- Form input: border 2px `#e2e8f0`, focus `#3b82f6`
- Button: `#1e40af` → hover `#1e3a8a` + lift
- Info box biru muda: "Halaman ini hanya untuk staff SITARA"

---

### 3. Dashboard Admin

```
┌──── Header ──────────────────────────────────┐
│ Dashboard Admin Klinik      [tanggal]        │
├──── Stats Grid (6 card, auto-fill) ──────────┤
│ Total Pasien │ Pasien Baru │ Antrian Tunggu  │
│ Antrian Done │ Assessment  │ Terapi Aktif    │
├──── Charts (2 kolom) ────────────────────────┤
│ Tren Kunjungan (line chart) │ Diagnosis Dist  │
├──── Antrian Aktif (3 kolom) ─────────────────┤
│ Menunggu (kuning) │ Dipanggil (biru) │ Selesai│
├──── Aktivitas Terbaru ───────────────────────┤
│ Tabel log aktivitas sistem                   │
└──────────────────────────────────────────────┘
```

**Chart:** `chart.js` + `vue-chartjs` — line chart tren kunjungan, pie/doughnut distribusi diagnosis

---

### 4. Dashboard Dokter

```
┌──── Header ──────────────────────────────────┐
│ Dashboard Dokter                             │
├──── Stats Grid (4 card) ─────────────────────┤
│ Pasien Saya │ Assessment Hari Ini │ ...      │
├──── Tren Kunjungan (sudut dokter) ───────────┤
│ Line chart — data pasien dokter ini          │
├──── Aktivitas Terbaru ───────────────────────┤
│ Assessment & terapi terkini                  │
└──────────────────────────────────────────────┘
```

---

### 5. Dashboard Terapis

```
┌──── Header ──────────────────────────────────┐
│ Dashboard Terapis                            │
├──── Stats Grid (4 card) ─────────────────────┤
│ Sesi Saya │ Terapi Aktif │ Kehadiran │ ...   │
├──── Jadwal Sesi Hari Ini ────────────────────┤
│ Tabel sesi terapi hari ini                   │
├──── Aktivitas Terbaru ───────────────────────┤
└──────────────────────────────────────────────┘
```

---

### 6. Super Admin Dashboard (`/super-admin/dashboard`)

```
┌──── Header ──────────────────────────────────┐
│ Dashboard Super Admin   [nama user]          │
├──── Stats Grid (4 card) ─────────────────────┤
│ Total User │ User Aktif │ Menunggu │ Poli     │
├──── Quick Actions (4 card) ──────────────────┤
│ Manajemen User │ Poli │ Log Aktivitas │ Setting│
├──── Informasi Sistem ───────────────────────┤
│ Role │ Status │ Versi │ Terakhir Login       │
└──────────────────────────────────────────────┘
```

---

### 7. Portal Pasien

**Warna aksen hijau `#10b981`** membedakan secara visual dari portal staff.

```
┌─ Login Pasien (/pasien/login) ───────────────┐
│ Form sederhana, tombol hijau teal            │
│ Link ke register                             │
├─ Dashboard Pasien ───────────────────────────┤
│ Stats: Antrian | Jadwal | Assessment | Terapi │
│ Quick Actions: Booking | Jadwal | Riwayat    │
│ Info Banner: gradien hijau                   │
├─ Booking Antrian ────────────────────────────┤
│ Pilih Poli → Pilih Dokter → Tanggal → Submit │
└──────────────────────────────────────────────┘
```

---

## Responsivitas

### Breakpoints

```css
/* Mobile */
@media (max-width: 768px) {
  .sidebar { transform: translateX(-100%); }   /* Hidden, toggle dengan burger */
  .main-content { margin-left: 0; }
  .stats-grid { grid-template-columns: 1fr 1fr; }
  .charts-grid { grid-template-columns: 1fr; }
  .queue-cols { grid-template-columns: 1fr; }
  .portal-selection { grid-template-columns: 1fr; }
}

/* Tablet */
@media (max-width: 1024px) {
  .hero-title { font-size: 3rem; }
}
```

### Behavior

| Elemen | Desktop | Mobile |
|--------|---------|--------|
| Sidebar | Fixed 260px, selalu tampil | Hidden, buka via toggle (☰) |
| Stats Grid | 3–6 kolom | 1–2 kolom |
| Charts Grid | 2 kolom | 1 kolom |
| Landing cards | 2 kolom | 1 kolom |
| Navbar toggle | Tersembunyi | Tampil |

---

## State Management & Data Flow

### Stores (Pinia)

| Store | File | Data yang dikelola |
|-------|------|--------------------|
| `useAuthStore` | `auth/stores/authStore.js` | User, token, role, login/logout |
| `useAnalyticsStore` | `analytics/stores/analyticsStore.js` | Stats dashboard, charts, activities |
| `useQueueStore` | `queue/stores/queueStore.js` | Data antrian, stats real-time |
| `usePatientStore` | `patient-portal/stores/patientStore.js` | Data user pasien, token pasien |

### Alur Data Login Staff

```
LoginPage → authStore.login()
  → authService.login(email, pw)
    → POST /api/login
    → Simpan token + user ke localStorage
  → Cek role user
    ├── super_admin → router.push('/super-admin/dashboard')
    └── lainnya    → router.push('/dashboard')
```

### Alur Data Auto-refresh Dashboard

```
AdminDashboard onMounted()
  → analyticsStore.fetchAnalytics()  ← fetch sekali saat load
  → setInterval(queueStore.getStats, 30000)  ← poll antrian tiap 30 detik
```

### API Client (`core/services/api.js`)

```
Request interceptor:
  → Baca token dari localStorage (staff atau patient sesuai URL)
  → Attach Authorization: Bearer {token}
  → Tambah cache-busting ?_t= untuk GET

Response interceptor:
  → 401 → hapus token + redirect ke login yang sesuai
```

---

## Keamanan Frontend

### Token Management
- Staff token: `localStorage['token']`
- Patient token: `localStorage['patient_token']`
- Keduanya dihapus otomatis saat 401 response
- Validasi format token saat dibaca (cegah simpan HTML error)

### Route Protection
- Navigation guard di `router/index.js` — cek token + role sebelum setiap navigasi
- `meta.roles` pada setiap route — spesifikasi role yang diizinkan
- Super admin tidak bisa akses `/dashboard` staff biasa dan sebaliknya

### Data Privacy
- NIK pasien ditampilkan dalam format masked via `MaskedNIK.vue`
- Super admin tidak pernah menerima data pasien dari API (diblokir di backend)
- Token pasien dan staff disimpan terpisah — tidak bisa cross-portal

### Error Boundary
- `onErrorCaptured` di `App.vue` — tangkap Vue error global
- API interceptor — handle 401, network error
- Setiap store punya `error` state untuk menampilkan pesan ke user

---

## Ringkasan Visual Identity

```
Landing Page : Gradient biru muda, portal cards putih modern
Staff Portal : Dark sidebar (#1e293b) + aksen biru (#3b82f6)
Pasien Portal: Dark sidebar (#1e293b) + aksen hijau (#10b981)
Typography  : Inter, clean, weight 400–800
Animasi     : fade transition antar halaman (0.2s), hover lift card
Tone        : Profesional, bersih, medis — bukan minimalis ekstrem
```

---

*File ini mendeskripsikan desain aktual yang diimplementasikan. Update dokumen ini setiap kali ada perubahan signifikan pada desain atau arsitektur.*

*Terakhir diupdate: 30 Juni 2026*
