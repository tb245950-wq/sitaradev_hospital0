# 🗺️ Super Admin — Routes Reference

**Status:** ✅ Semua route terdaftar dan berfungsi  
**Terakhir diupdate:** 30 Juni 2026

---

## Daftar Route Super Admin

### API Routes (Backend)

| Method | Endpoint | Controller Method | Deskripsi |
|--------|----------|-------------------|-----------|
| GET | `/api/super-admin/dashboard` | `getDashboardStats()` | Statistik sistem |
| GET | `/api/super-admin/audit-logs` | `getAuditLogs()` | Log aktivitas (paginated) |
| GET | `/api/super-admin/login-history` | `getLoginHistory()` | Riwayat login (paginated) |
| GET | `/api/super-admin/failed-logins` | `getFailedLogins()` | Login gagal N hari |
| GET | `/api/super-admin/users` | `getUsers()` | Daftar semua staff |
| POST | `/api/super-admin/users` | `createUser()` | Buat user baru |
| PUT | `/api/super-admin/users/{id}` | `updateUser()` | Update user |
| DELETE | `/api/super-admin/users/{id}` | `deleteUser()` | Hapus user |
| POST | `/api/super-admin/users/{id}/reset-password` | `resetUserPassword()` | Reset password |
| GET | `/api/super-admin/polis` | `getPolis()` | Daftar poli (read-only) |
| POST | `/api/polis` | `PoliController@store` | Tambah poli |
| PUT | `/api/polis/{id}` | `PoliController@update` | Edit poli |
| DELETE | `/api/polis/{id}` | `PoliController@destroy` | Hapus poli |

**Semua route dilindungi:** `auth:sanctum` + `role:super_admin`

### Frontend Routes (Vue Router)

| Path | Nama Route | Komponen |
|------|-----------|----------|
| `/super-admin` | — | Redirect ke `/super-admin/dashboard` |
| `/super-admin/dashboard` | `super-admin.dashboard` | `DashboardView.vue` |
| `/super-admin/users` | `super-admin.users` | `UserManagementView.vue` |
| `/super-admin/polis` | `super-admin.polis` | `PoliManagementView.vue` |
| `/super-admin/audit-logs` | `super-admin.audit-logs` | `AuditLogsView.vue` |
| `/super-admin/backup` | `super-admin.backup` | `BackupView.vue` |
| `/super-admin/settings` | `super-admin.settings` | `SettingsView.vue` |

Semua route dengan `meta: { requiresAuth: true, roles: ['super_admin'] }`

---

## Struktur Router (Nested Layout)

```javascript
// frontend/src/modules/super-admin/router/superAdminRoutes.js
{
  path: '/super-admin',
  component: SuperAdminLayout,   // ← Wrapper dengan Sidebar + Navbar
  meta: { requiresAuth: true, roles: ['super_admin'] },
  children: [
    { path: '', redirect: { name: 'super-admin.dashboard' } },
    { path: 'dashboard', name: 'super-admin.dashboard', ... },
    { path: 'users',     name: 'super-admin.users',     ... },
    { path: 'polis',     name: 'super-admin.polis',     ... },
    { path: 'audit-logs',name: 'super-admin.audit-logs',...  },
    { path: 'backup',    name: 'super-admin.backup',    ... },
    { path: 'settings',  name: 'super-admin.settings',  ... },
  ]
}
```

Penting: **Nested routes** digunakan agar semua halaman super admin mendapat Sidebar dan Navbar dari `SuperAdminLayout.vue`.

---

## Navigasi di Sidebar

Sidebar menampilkan menu super admin secara kondisional:

```vue
<template v-if="authStore.userRole === 'super_admin'">
  <router-link to="/super-admin/users">Manajemen User</router-link>
  <router-link to="/super-admin/polis">Manajemen Poli</router-link>
  <router-link to="/super-admin/audit-logs">Log Aktivitas</router-link>
  <router-link to="/super-admin/backup">Backup</router-link>
  <router-link to="/super-admin/settings">Pengaturan</router-link>
</template>
```

---

## Navigation Guard

Router guard menangani super_admin secara khusus:

```javascript
// frontend/src/router/index.js
router.beforeEach((to, from, next) => {
  // Jika sudah login dan akses halaman login → redirect sesuai role
  if ((to.path === '/login') && staffToken) {
    if (user?.role === 'super_admin') return next('/super-admin/dashboard')
    return next('/dashboard')
  }

  // Jika route punya meta.roles dan role tidak cocok
  if (to.meta.roles && !to.meta.roles.includes(userRole)) {
    if (userRole === 'super_admin') return next('/super-admin/dashboard')
    return next('/dashboard')
  }
})
```

---

## Verifikasi Route (Artisan)

```bash
# Cek semua route super-admin terdaftar
php artisan route:list | grep super-admin
```

Output yang diharapkan:
```
GET    api/super-admin/dashboard
GET    api/super-admin/audit-logs
GET    api/super-admin/login-history
GET    api/super-admin/failed-logins
GET    api/super-admin/users
POST   api/super-admin/users
PUT    api/super-admin/users/{userToUpdate}
DELETE api/super-admin/users/{userToDelete}
POST   api/super-admin/users/{userToReset}/reset-password
GET    api/super-admin/polis
```

---

## Troubleshooting Route

### Gejala: Klik menu redirect ke landing page
**Penyebab:** Route belum terdaftar di `router/index.js`  
**Solusi:** Pastikan `superAdminRoutes` sudah di-import dan didaftarkan:
```javascript
import { superAdminRoutes } from '../modules/super-admin/router/superAdminRoutes'
const routes = [ ...superAdminRoutes, ...dashboardRoutes, ... ]
```

### Gejala: Halaman tampil tanpa Sidebar/Navbar
**Penyebab:** Route tidak menggunakan nested layout  
**Solusi:** Gunakan `SuperAdminLayout` sebagai parent route, bukan flat route langsung ke komponen.

### Gejala: Role check gagal (redirect ke /dashboard terus)
**Penyebab:** `meta.roles` tidak include `super_admin`  
**Solusi:** Pastikan setiap child route punya `meta: { roles: ['super_admin'] }`.

### Gejala: Infinite redirect loop
**Penyebab:** Navigation guard redirect ke `/dashboard` saat super_admin tidak punya akses ke `/dashboard`  
**Solusi:** Guard sudah diperbaiki — jika role adalah `super_admin` maka redirect ke `/super-admin/dashboard`, bukan `/dashboard`.

---

## Cek Route di Browser Console

```javascript
// Verifikasi token & role sebelum navigasi
console.log('Token:', localStorage.getItem('token'))
console.log('User:', JSON.parse(localStorage.getItem('user')))
console.log('Role:', JSON.parse(localStorage.getItem('user'))?.role)
// Harus: { ..., role: 'super_admin' }

// Test navigasi manual
router.push('/super-admin/dashboard')
```

---

*Lihat juga: [IMPLEMENTATION.md](./IMPLEMENTATION.md) | [SECURITY.md](./SECURITY.md) | [TESTING.md](./TESTING.md)*
