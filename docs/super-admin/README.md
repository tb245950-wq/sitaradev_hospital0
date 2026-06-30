# 🛡️ Super Admin — Documentation

**Versi:** 1.0.0  
**Status:** ✅ Production Ready  
**Terakhir diupdate:** 30 Juni 2026

---

## 📖 Overview

Role `super_admin` berfungsi sebagai **System Administrator** pada Sitaradev Hospital Management System. Berbeda dengan Admin Klinik yang fokus ke operasional medis, Super Admin fokus pada **administrasi sistem** — user management, audit trail, konfigurasi, dan backup.

---

## 📚 Daftar Dokumentasi

| Dokumen | Deskripsi |
|---------|-----------|
| [IMPLEMENTATION.md](./IMPLEMENTATION.md) | Detail teknis: database, API, frontend, routes, security |
| [ROUTES.md](./ROUTES.md) | Daftar lengkap API & frontend routes + troubleshooting |
| [SECURITY.md](./SECURITY.md) | Multi-layer security, role isolation, audit trail |
| [TESTING.md](./TESTING.md) | cURL commands, browser testing, checklist lengkap |
| [CHECKLIST.md](../deployment/CHECKLIST.md) | Checklist deployment step-by-step |

### Referensi Tambahan (Legacy)
File-file lama disimpan di folder ini sebagai referensi historis:
- `SUPER_ADMIN_QUICK_REFERENCE.md` — Visual diagram & API access map
- `ROLE_AUDIT_REPORT.md` — Audit semua role yang ada di sistem
- `SETUP_SUPER_ADMIN_USER.md` — Quick command setup user

---

## 🎯 Fitur Super Admin

### ✅ Akses Diberikan
| Fitur | Endpoint | Status |
|-------|----------|--------|
| Dashboard Sistem | `/super-admin/dashboard` | ✅ Ready |
| Manajemen User | `/super-admin/users` | ✅ Ready |
| Manajemen Poli | `/super-admin/polis` | ✅ Ready |
| Log Aktivitas | `/super-admin/audit-logs` | ✅ Ready |
| Riwayat Login | `/super-admin/login-history` | ✅ Ready |
| Backup Sistem | `/super-admin/backup` | ⏳ UI Ready |
| Pengaturan | `/super-admin/settings` | ⏳ UI Ready |

### ❌ Diblokir untuk Super Admin
- Data pasien (NIK, nama, alamat, rekam medis)
- Antrian / queue manajemen
- Assessment medis
- Data terapi
- Monitoring pasien
- Analytics operasional klinik

---

## 🔐 Keamanan

Sistem menggunakan 5 layer proteksi:
1. **Authentication** — `auth:sanctum` (token wajib valid)
2. **Role Middleware** — `role:super_admin`
3. **Controller Check** — `if ($user->role !== 'super_admin') abort(403)`
4. **Data Isolation** — Super admin tidak pernah query patient data
5. **Audit Trail** — Semua aksi tercatat di `system_audit_logs`

Detail lengkap → [SECURITY.md](./SECURITY.md)

---

## 🚀 Quick Start

### 1. Buat User Super Admin
```bash
php artisan tinker

User::create([
    'name'     => 'Super Admin',
    'email'    => 'superadmin@sitara.com',
    'role'     => 'super_admin',
    'password' => Hash::make('password123'),
    'status'   => 'active'
]);
exit
```

### 2. Jalankan Migrasi
```bash
php artisan migrate
```

### 3. Login & Test
- Buka `http://localhost:5173/login`
- Login dengan email + password di atas
- Harus redirect ke `/super-admin/dashboard`
- Sidebar menampilkan menu: Manajemen User, Manajemen Poli, Log Aktivitas, Backup, Pengaturan

---

## 📊 Dashboard Sistem

Dashboard Super Admin menampilkan:
- **Total User Aktif** — Jumlah staff dengan status active
- **Login Gagal Hari Ini** — Jumlah failed login attempts hari ini
- **Storage Terpakai** — Persentase storage yang digunakan
- **Log Aktivitas Terbaru** — 10 entri audit log terbaru (auto-refresh 30 detik)

---

## ❓ FAQ

**Q: Super admin bisa lihat data pasien?**  
A: Tidak. Semua endpoint patient data dilindungi `role:admin,dokter,terapis`.

**Q: Bagaimana super admin track aksi user lain?**  
A: Melalui `system_audit_logs` — setiap user management action dicatat dengan old/new values.

**Q: Bisakah super admin sekaligus jadi admin klinik?**  
A: Tidak direkomendasikan. Satu user = satu role. Super admin fokus sistem, admin klinik fokus operasional.

**Q: Role apa saja yang aktif?**  
A: 4 role: `super_admin`, `admin`, `dokter`, `terapis`. Role `resepsionis` telah dihapus.

**Q: Admin klinik bisa akses /super-admin?**  
A: Tidak. Akan mendapat `403 Forbidden`.

---

## 🔗 Link Terkait

- [Dokumentasi Utama](../README.md)
- [Deployment Checklist](../deployment/CHECKLIST.md)
- [Development Guide](../development/START_HERE.md)
