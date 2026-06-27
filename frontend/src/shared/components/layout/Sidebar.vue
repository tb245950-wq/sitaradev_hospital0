<template>
  <aside class="sidebar" :class="{ 'is-open': isOpen }">
    <div class="sidebar-header">
      <img src="@/assets/SITARA_RM_BG.png" alt="SITARA" class="logo" />
      <span class="brand-name">SITARA</span>
    </div>

    <nav class="sidebar-nav">
      <!-- Menu Utama (Semua Role) -->
      <router-link to="/dashboard" class="nav-item">
        <span class="icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="7" height="7"></rect>
            <rect x="14" y="3" width="7" height="7"></rect>
            <rect x="14" y="14" width="7" height="7"></rect>
            <rect x="3" y="14" width="7" height="7"></rect>
          </svg>
        </span>
        <span class="label">Dashboard</span>
      </router-link>


      <router-link to="/patients" class="nav-item">
        <span class="icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
          </svg>
        </span>
        <span class="label">Data Pasien</span>
      </router-link>

      <router-link to="/queues" class="nav-item">
        <span class="icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="8" y1="6" x2="21" y2="6"></line>
            <line x1="8" y1="12" x2="21" y2="12"></line>
            <line x1="8" y1="18" x2="21" y2="18"></line>
            <line x1="3" y1="6" x2="3.01" y2="6"></line>
            <line x1="3" y1="12" x2="3.01" y2="12"></line>
            <line x1="3" y1="18" x2="3.01" y2="18"></line>
          </svg>
        </span>
        <span class="label">Antrian</span>
      </router-link>

      <!-- Section: Tindakan (Dokter & Admin) -->
      <template v-if="authStore.userRole === 'dokter' || authStore.userRole === 'admin'">
        <div class="nav-divider">Tindakan</div>
        
        <router-link to="/assessments" class="nav-item">
          <span class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
            </svg>
          </span>
          <span class="label">Assessment</span>
        </router-link>
      </template>

      <!-- Section: Terapi & Monitoring (Semua Role Medis) -->
      <template v-if="authStore.userRole === 'dokter' || authStore.userRole === 'terapis' || authStore.userRole === 'admin'">
        <router-link to="/therapies" class="nav-item">
          <span class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
            </svg>
          </span>
          <span class="label">Terapi</span>
        </router-link>

        <router-link to="/monitoring" class="nav-item">
          <span class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
              <polyline points="17 6 23 6 23 12"></polyline>
            </svg>
          </span>
          <span class="label">Monitoring</span>
        </router-link>
      </template>

      <!-- Section: Laporan (Dokter & Admin) -->
      <template v-if="authStore.userRole === 'dokter' || authStore.userRole === 'admin'">
        <div class="nav-divider">Laporan</div>

        <router-link to="/reports" class="nav-item">
          <span class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
              <line x1="16" y1="13" x2="8" y2="13"></line>
              <line x1="16" y1="17" x2="8" y2="17"></line>
            </svg>
          </span>
          <span class="label">Laporan Medis</span>
        </router-link>
      </template>

      <!-- Section: Administrasi (HANYA ADMIN) -->
      <template v-if="authStore.userRole === 'admin'">
        <div class="nav-divider">Administrasi</div>

        <router-link to="/users" class="nav-item">
          <span class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
              <circle cx="9" cy="7" r="4"></circle>
              <path d="M16 11h6"></path>
              <path d="M19 8v6"></path>
            </svg>
          </span>
          <span class="label">Manajemen User</span>
        </router-link>

        <router-link to="/settings" class="nav-item">
          <span class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="3"></circle>
              <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
            </svg>
          </span>
          <span class="label">Pengaturan</span>
        </router-link>
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