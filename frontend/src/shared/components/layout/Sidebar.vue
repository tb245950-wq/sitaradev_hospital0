<template>
  <aside class="sidebar" :class="{ 'is-open': isOpen }">
    <div class="sidebar-header">
      <img src="@/assets/SITARA_RM_BG.png" alt="SITARA" class="logo" />
      <span class="brand-name">SITARA</span>
    </div>

    <nav class="sidebar-nav">
      <!-- Menu Utama (Semua Role) -->
      <router-link to="/dashboard" class="nav-item">
        <span class="label">Dashboard</span>
      </router-link>

      <!-- Section: Super Admin Menu (HANYA SUPER_ADMIN) -->
      <template v-if="authStore.userRole === 'super_admin'">
        <div class="nav-divider">Administrasi Sistem</div>
        
        <router-link to="/super-admin/users" class="nav-item">
          <span class="label">Manajemen User</span>
        </router-link>

        <router-link to="/super-admin/polis" class="nav-item">
          <span class="label">Manajemen Poli</span>
        </router-link>

        <router-link to="/super-admin/audit-logs" class="nav-item">
          <span class="label">Log Aktivitas</span>
        </router-link>

        <router-link to="/super-admin/backup" class="nav-item">
          <span class="label">Backup</span>
        </router-link>

        <router-link to="/super-admin/settings" class="nav-item">
          <span class="label">Pengaturan</span>
        </router-link>
      </template>

      <!-- Section: Clinical Menu (Admin, Dokter, Terapis) -->
      <template v-if="authStore.userRole === 'admin' || authStore.userRole === 'dokter' || authStore.userRole === 'terapis'">
        <div class="nav-divider" v-if="authStore.userRole !== 'super_admin'">Klinis</div>

        <router-link to="/patients" class="nav-item">
          <span class="label">Data Pasien</span>
        </router-link>

        <router-link to="/queues" class="nav-item">
          <span class="label">Antrian</span>
        </router-link>

        <!-- Section: Tindakan (Dokter & Admin) -->
        <template v-if="authStore.userRole === 'dokter' || authStore.userRole === 'admin'">
          <div class="nav-divider">Tindakan</div>
          
          <router-link to="/assessments" class="nav-item">
            <span class="label">Assessment</span>
          </router-link>
        </template>

        <!-- Section: Terapi & Monitoring (Semua Role Medis) -->
        <template v-if="authStore.userRole === 'dokter' || authStore.userRole === 'terapis' || authStore.userRole === 'admin'">
          <router-link to="/therapies" class="nav-item">
            <span class="label">Terapi</span>
          </router-link>

          <router-link to="/monitoring" class="nav-item">
            <span class="label">Monitoring</span>
          </router-link>
        </template>

        <!-- Section: Laporan (Dokter & Admin) -->
        <template v-if="authStore.userRole === 'dokter' || authStore.userRole === 'admin'">
          <div class="nav-divider">Laporan</div>

          <router-link to="/reports" class="nav-item">
            <span class="label">Laporan Medis</span>
          </router-link>
        </template>

        <!-- Section: Administrasi (HAPUS - HANYA SUPER ADMIN) -->
        <!-- Manajemen User, Poli, Backup dipindah ke Super Admin -->
      </template>
    </nav>

    <div class="sidebar-footer">
      <div class="version">v1.0.0</div>
    </div>
  </aside>
</template>

<script setup>
import { useAuthStore } from '../../../modules/auth/stores/authStore'

const authStore = useAuthStore()

defineProps({
  isOpen: Boolean
})
</script>

<style scoped>
.sidebar {
  width: 260px;
  background: #1e293b;
  color: white;
  height: 100vh;
  display: flex;
  flex-direction: column;
  position: fixed;
  left: 0;
  top: 0;
  z-index: 1000;
  transition: transform 0.3s;
  overflow-y: auto;
}

@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%);
  }
  .sidebar.is-open {
    transform: translateX(0);
  }
}

.sidebar-header {
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.logo {
  width: 32px;
  height: 32px;
}

.brand-name {
  font-size: 1.25rem;
  font-weight: 700;
  letter-spacing: 0.05em;
}

.sidebar-nav {
  flex: 1;
  padding: 1rem 0;
  display: flex;
  flex-direction: column;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1.5rem;
  color: #cbd5e1;
  text-decoration: none;
  transition: all 0.2s;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.05);
  color: white;
}

.nav-item.router-link-active {
  background: #3b82f6;
  color: white;
  border-right: 4px solid white;
}

.icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
  color: #94a3b8;
}

.nav-item:hover .icon,
.nav-item.router-link-active .icon {
  color: white;
}

.icon svg {
  width: 100%;
  height: 100%;
}

.nav-divider {
  padding: 1.5rem 1.5rem 0.5rem;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #64748b;
  letter-spacing: 0.05em;
}

.sidebar-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.version {
  font-size: 0.75rem;
  color: #64748b;
}
</style>