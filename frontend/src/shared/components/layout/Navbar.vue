<template>
  <nav class="navbar">
    <div class="navbar-left">
      <button @click="$emit('toggle-sidebar')" class="toggle-btn">
        <span class="menu-icon">☰</span>
      </button>
      <h2 class="page-title">{{ pageTitle }}</h2>
    </div>

    <div class="navbar-right">
      <div class="user-profile">
        <div class="user-info">
          <span class="user-name">{{ authStore.user?.name }}</span>
          <span class="user-role">{{ roleLabel }}</span>
        </div>
        <div class="user-avatar">
          {{ authStore.user?.name?.charAt(0) }}
        </div>
      </div>
      <button @click="showLogoutModal = true" class="logout-btn" title="Keluar">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <polyline points="16 17 21 12 16 7"/>
          <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        <span class="logout-text">Logout</span>
      </button>
    </div>
  </nav>

  <!-- Modal Konfirmasi Logout -->
  <LogoutConfirmModal
    :show="showLogoutModal"
    :loading="logoutLoading"
    :user-name="authStore.user?.name"
    :user-role="roleLabel"
    @confirm="doLogout"
    @cancel="showLogoutModal = false"
  />
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../../modules/auth/stores/authStore'
import { useNotificationStore } from '../../stores/notificationStore'
import LogoutConfirmModal from '../LogoutConfirmModal.vue'

defineEmits(['toggle-sidebar'])

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const notify = useNotificationStore()

const showLogoutModal = ref(false)
const logoutLoading   = ref(false)

const pageTitle = computed(() => {
  switch (route.name) {
    case 'dashboard': return 'Dashboard'
    case 'patients': return 'Data Pasien'
    case 'queues': return 'Antrian'
    case 'super-admin.dashboard': return 'Dashboard Super Admin'
    case 'super-admin.users': return 'Manajemen User'
    case 'super-admin.polis': return 'Manajemen Poli'
    case 'super-admin.audit-logs': return 'Log Aktivitas'
    case 'super-admin.backup': return 'Backup Sistem'
    case 'super-admin.settings': return 'Pengaturan Sistem'
    default: return 'SITARA'
  }
})

const roleLabel = computed(() => {
  const role = authStore.user?.role
  const labels = {
    'super_admin': 'Super Admin',
    'admin': 'Admin Klinik',
    'dokter': 'Dokter',
    'terapis': 'Terapis'
  }
  return labels[role] || role
})

const doLogout = async () => {
  logoutLoading.value = true
  try {
    await authStore.logout()
    notify.success('Anda berhasil keluar. Sampai jumpa!', 'Logout Berhasil')
    setTimeout(() => { window.location.href = '/login' }, 800)
  } catch (error) {
    console.error('Logout error:', error)
    window.location.href = '/login'
  } finally {
    logoutLoading.value = false
    showLogoutModal.value = false
  }
}
</script>

<style scoped>
.navbar {
  height: 64px;
  background: white;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  position: sticky;
  top: 0;
  z-index: 100;
}

.navbar-left {
  display: flex;
  align-items: center;
  gap: 1rem;
  min-width: 0;
}

.toggle-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #64748b;
  display: none;
  flex-shrink: 0;
  padding: 0.25rem;
  line-height: 1;
}

.page-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.navbar-right {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-shrink: 0;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-info {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.user-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1e293b;
}

.user-role {
  font-size: 0.75rem;
  color: #64748b;
  text-transform: capitalize;
}

.user-avatar {
  width: 36px;
  height: 36px;
  background: #3b82f6;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  flex-shrink: 0;
}

.logout-btn {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.5rem 1rem;
  background: #f1f5f9;
  color: #ef4444;
  border: none;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}
.logout-btn svg { width: 16px; height: 16px; flex-shrink: 0; }

.logout-btn:hover {
  background: #fee2e2;
}

/* ===== MOBILE ===== */
@media (max-width: 768px) {
  .toggle-btn {
    display: block;
  }

  .navbar {
    padding: 0 1rem;
  }

  .page-title {
    font-size: 1rem;
  }

  /* Sembunyikan nama & role di mobile, tampilkan hanya avatar */
  .user-info {
    display: none;
  }

  .logout-btn {
    padding: 0.4rem 0.75rem;
    font-size: 0.8rem;
  }

  .navbar-right {
    gap: 0.5rem;
  }
}

@media (max-width: 400px) {
  /* Di layar sangat kecil, sembunyikan teks logout, tampilkan ikon */
  .logout-btn {
    padding: 0.4rem 0.6rem;
    font-size: 0.75rem;
  }
}
</style>
