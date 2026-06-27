<template>
  <div class="dashboard-layout">
    <Sidebar :is-open="isSidebarOpen" />
    
    <div class="main-content">
      <Navbar @toggle-sidebar="isSidebarOpen = !isSidebarOpen" />
      
      <main class="content-body">
        <!-- Header dengan Role Badge -->
        <div class="dashboard-header">
          <div>
            <h1 class="dashboard-title">
              Dashboard <span class="role-badge" :class="roleBadgeClass">{{ roleLabel }}</span>
            </h1>
            <p class="dashboard-subtitle">
              Selamat datang, <strong>{{ authStore.user?.name }}</strong>! 
              <span class="nip-badge" v-if="authStore.user?.nip">NIP: {{ authStore.user.nip }}</span>
            </p>
          </div>
          <div class="header-date">
            {{ currentDate }}
          </div>
        </div>

        <!-- Stats Grid (Berbeda per Role) -->
        <div class="stats-grid">
          <div v-for="stat in statsToShow" :key="stat.title" class="stat-card">
            <div class="stat-icon" :style="stat.iconStyle" v-html="iconSvgs[stat.icon]">
            </div>
            <div class="stat-info">
              <h3>{{ stat.title }}</h3>
              <p class="stat-value">{{ stat.value }}</p>
              <p class="stat-label">{{ stat.label }}</p>
            </div>
          </div>
        </div>

        <!-- Welcome Banner -->
        <div class="welcome-banner">
          <div class="welcome-content">
            <div class="welcome-image-wrapper">
              <img src="@/assets/SITARA_RM_BG.png" alt="SITARA" class="welcome-logo-large" />
            </div>
            <div class="welcome-info">
              <h1>Sistem Informasi Terpadu</h1>
              <h2>Assessment dan Rekam Anak</h2>
              <p>SITARA - Pusat Layanan Medis Terpadu untuk Anak</p>
              <div class="welcome-tag">
                {{ welcomeTag }}
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions (Berbeda per Role) -->
        <div class="quick-actions">
          <h2 class="section-title">Aksi Cepat</h2>
          <div class="actions-grid">
            <router-link 
              v-for="action in quickActions" 
              :key="action.path" 
              :to="action.path" 
              class="action-card"
            >
              <div class="action-icon" v-html="iconSvgs[action.icon]"></div>
              <div class="action-info">
                <h4>{{ action.title }}</h4>
                <p>{{ action.description }}</p>
              </div>
              <div class="action-arrow">→</div>
            </router-link>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../../auth/stores/authStore'
import api from '../../../core/services/api'
import Sidebar from '../../../shared/components/layout/Sidebar.vue'
import Navbar from '../../../shared/components/layout/Navbar.vue'

const authStore = useAuthStore()
const isSidebarOpen = ref(false)

const stats = ref({
  total_pasien: 0,
  antrian_menunggu: 0,
  assessment_hari_ini: 0,
  terapi_aktif: 0,
  monitoring_hari_ini: 0,
  pasien_hari_ini: 0,
  antrian_hari_ini: 0
})

// Computed: Role Label
const roleLabel = computed(() => {
  const labels = {
    admin: 'Administrator',
    dokter: 'Dokter',
    terapis: 'Terapis'
  }
  return labels[authStore.userRole] || authStore.userRole
})

// Computed: Role Badge Class
const roleBadgeClass = computed(() => {
  const classes = {
    admin: 'badge-admin',
    dokter: 'badge-dokter',
    terapis: 'badge-terapis'
  }
  return classes[authStore.userRole] || 'badge-default'
})

// Computed: Current Date
const currentDate = computed(() => {
  return new Date().toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})

// Computed: Welcome Tag per Role
const welcomeTag = computed(() => {
  const tags = {
    admin: 'Panel Administratif - Kelola Sistem & User',
    dokter: 'Panel Medis - Assessment & Diagnosis',
    terapis: 'Panel Terapi - Monitoring & Progress'
  }
  return tags[authStore.userRole] || 'Dashboard SITARA'
})

// Icon Mapping SVGs
const iconSvgs = {
  users: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 24px; height: 24px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>`,
  ticket: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 24px; height: 24px;"><rect x="3" y="4" width="18" height="16" rx="2"></rect><line x1="16" y1="2" x2="16" y2="4"></line><line x1="8" y1="2" x2="8" y2="4"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>`,
  clipboard: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 24px; height: 24px;"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>`,
  activity: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 24px; height: 24px;"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>`,
  stethoscope: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 24px; height: 24px;"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>`,
  calendar: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 24px; height: 24px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>`,
  chart: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 24px; height: 24px;"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>`,
  user: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 24px; height: 24px;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>`,
  cog: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 24px; height: 24px;"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>`,
  document: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 24px; height: 24px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>`
}

// Computed: Stats yang ditampilkan berdasarkan role
const statsToShow = computed(() => {
  if (authStore.isAdmin) {
    return [
      { 
        title: 'Total Pasien', 
        value: stats.value.total_pasien, 
        label: 'Terdaftar di sistem',
        icon: 'users', 
        iconStyle: { background: '#e0f2fe', color: '#0ea5e9' } 
      },
      { 
        title: 'Antrian Hari Ini', 
        value: stats.value.antrian_hari_ini, 
        label: 'Pasien datang',
        icon: 'ticket', 
        iconStyle: { background: '#fef3c7', color: '#f59e0b' } 
      },
      { 
        title: 'Assessment Hari Ini', 
        value: stats.value.assessment_hari_ini, 
        label: 'Dilakukan dokter',
        icon: 'clipboard', 
        iconStyle: { background: '#f0fdf4', color: '#22c55e' } 
      },
      { 
        title: 'Terapi Aktif', 
        value: stats.value.terapi_aktif, 
        label: 'Sedang berjalan',
        icon: 'activity', 
        iconStyle: { background: '#faf5ff', color: '#a855f7' } 
      }
    ]
  } else if (authStore.isDokter) {
    return [
      { 
        title: 'Total Pasien', 
        value: stats.value.total_pasien, 
        label: 'Dalam perawatan',
        icon: 'users', 
        iconStyle: { background: '#e0f2fe', color: '#0ea5e9' } 
      },
      { 
        title: 'Antrian Menunggu', 
        value: stats.value.antrian_menunggu, 
        label: 'Perlu ditangani',
        icon: 'clipboard', 
        iconStyle: { background: '#fef2f2', color: '#ef4444' } 
      },
      { 
        title: 'Assessment Hari Ini', 
        value: stats.value.assessment_hari_ini, 
        label: 'Sudah dilakukan',
        icon: 'stethoscope', 
        iconStyle: { background: '#f0fdf4', color: '#22c55e' } 
      },
      { 
        title: 'Terapi Aktif', 
        value: stats.value.terapi_aktif, 
        label: 'Program berjalan',
        icon: 'activity', 
        iconStyle: { background: '#faf5ff', color: '#a855f7' } 
      }
    ]
  } else if (authStore.isTerapis) {
    return [
      { 
        title: 'Pasien Saya', 
        value: stats.value.total_pasien, 
        label: 'Dalam terapi',
        icon: 'users', 
        iconStyle: { background: '#e0f2fe', color: '#0ea5e9' } 
      },
      { 
        title: 'Sesi Hari Ini', 
        value: stats.value.monitoring_hari_ini, 
        label: 'Terjadwal',
        icon: 'calendar', 
        iconStyle: { background: '#f0fdf4', color: '#22c55e' } 
      },
      { 
        title: 'Terapi Aktif', 
        value: stats.value.terapi_aktif, 
        label: 'Program berjalan',
        icon: 'activity', 
        iconStyle: { background: '#faf5ff', color: '#a855f7' } 
      },
      { 
        title: 'Progress Rata-rata', 
        value: '75%', 
        label: 'Kemajuan pasien',
        icon: 'chart', 
        iconStyle: { background: '#fef3c7', color: '#f59e0b' } 
      }
    ]
  }
  return []
})

// Computed: Quick Actions per Role
const quickActions = computed(() => {
  if (authStore.isAdmin) {
    return [
      { 
        title: 'Kelola User', 
        description: 'Tambah, edit, atau nonaktifkan akun staff',
        path: '/users', 
        icon: 'user' 
      },
      { 
        title: 'Lihat Laporan', 
        description: 'Laporan harian, bulanan, dan statistik',
        path: '/reports', 
        icon: 'chart' 
      },
      { 
        title: 'Data Pasien', 
        description: 'Kelola data pasien terdaftar',
        path: '/patients', 
        icon: 'users' 
      },
      { 
        title: 'Pengaturan', 
        description: 'Konfigurasi sistem',
        path: '/settings', 
        icon: 'cog' 
      }
    ]
  } else if (authStore.isDokter) {
    return [
      { 
        title: 'Antrian Pasien', 
        description: 'Panggil dan tangani pasien',
        path: '/queues', 
        icon: 'ticket' 
      },
      { 
        title: 'Assessment Baru', 
        description: 'Buat assessment medis baru',
        path: '/assessments/create', 
        icon: 'clipboard' 
      },
      { 
        title: 'Program Terapi', 
        description: 'Buat program terapi untuk pasien',
        path: '/therapies', 
        icon: 'activity' 
      },
      { 
        title: 'Laporan Medis', 
        description: 'Lihat laporan medis',
        path: '/reports', 
        icon: 'chart' 
      }
    ]
  } else if (authStore.isTerapis) {
    return [
      { 
        title: 'Monitoring Hari Ini', 
        description: 'Lihat jadwal sesi terapi',
        path: '/monitoring', 
        icon: 'calendar' 
      },
      { 
        title: 'Catat Progress', 
        description: 'Input progress sesi terapi',
        path: '/therapies/create', 
        icon: 'document' 
      },
      { 
        title: 'Data Pasien', 
        description: 'Lihat data pasien (read-only)',
        path: '/patients', 
        icon: 'users' 
      },
      { 
        title: 'Program Terapi', 
        description: 'Lihat program terapi aktif',
        path: '/therapies', 
        icon: 'activity' 
      }
    ]
  }
  return []
})

// Fetch stats dari backend
const fetchStats = async () => {
  try {
    const response = await api.get('/reports/dashboard')
    if (response.data.success) {
      stats.value = { ...stats.value, ...response.data.data }
    }
  } catch (error) {
    console.error('Failed to fetch dashboard stats:', error)
  }
}

onMounted(() => {
  fetchStats()
})
</script>

<style scoped>
.dashboard-layout {
  display: flex;
  min-height: 100vh;
  background: #f8fafc;
}

.main-content {
  flex: 1;
  margin-left: 260px;
  display: flex;
  flex-direction: column;
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 0;
  }
}

.content-body {
  padding: 2rem;
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

/* Dashboard Header */
.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 1.5rem 2rem;
  border-radius: 1rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.dashboard-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.role-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 600;
}

.badge-admin {
  background: #f3e8ff;
  color: #7c3aed;
}

.badge-dokter {
  background: #dbeafe;
  color: #1e40af;
}

.badge-terapis {
  background: #d1fae5;
  color: #065f46;
}

.dashboard-subtitle {
  color: #64748b;
  margin-top: 0.25rem;
  font-size: 0.95rem;
}

.nip-badge {
  background: #f1f5f9;
  padding: 0.15rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.8rem;
  color: #475569;
  font-weight: 500;
}

.header-date {
  color: #64748b;
  font-size: 0.9rem;
  font-weight: 500;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.5rem;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 1rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 1.25rem;
  transition: all 0.3s;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.stat-icon {
  width: 56px;
  height: 56px;
  border-radius: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  flex-shrink: 0;
}

.stat-info h3 {
  font-size: 0.875rem;
  color: #64748b;
  margin-bottom: 0.25rem;
  font-weight: 500;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
  line-height: 1;
}

.stat-label {
  font-size: 0.75rem;
  color: #94a3b8;
  margin-top: 0.25rem;
}

/* Welcome Banner */
.welcome-banner {
  background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
  border-radius: 2rem;
  padding: 3rem 2rem;
  color: white;
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0 10px 30px rgba(30, 64, 175, 0.25);
}

.welcome-content {
  width: 100%;
  max-width: 800px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.welcome-image-wrapper {
  background: white;
  padding: 1.5rem;
  border-radius: 2rem;
  margin-bottom: 2rem;
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}

.welcome-logo-large {
  width: 150px;
  height: auto;
  display: block;
}

.welcome-info h1 {
  font-size: 2rem;
  font-weight: 800;
  margin-bottom: 0.5rem;
  letter-spacing: -0.02em;
}

.welcome-info h2 {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 0.75rem;
  opacity: 0.95;
}

.welcome-info p {
  font-size: 1rem;
  opacity: 0.9;
  margin-bottom: 1.5rem;
  line-height: 1.6;
}

.welcome-tag {
  display: inline-block;
  background: rgba(255, 255, 255, 0.15);
  padding: 0.6rem 1.5rem;
  border-radius: 9999px;
  font-weight: 600;
  font-size: 0.875rem;
  border: 1px solid rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(8px);
}

/* Quick Actions */
.section-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 1rem;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}

.action-card {
  background: white;
  padding: 1.25rem;
  border-radius: 0.75rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  display: flex;
  align-items: center;
  gap: 1rem;
  text-decoration: none;
  color: inherit;
  transition: all 0.3s;
  border: 2px solid transparent;
}

.action-card:hover {
  border-color: #3b82f6;
  transform: translateX(4px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

.action-icon {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: #3b82f6;
}

.action-icon svg {
  width: 100%;
  height: 100%;
}

.action-info {
  flex: 1;
}

.action-info h4 {
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 0.25rem;
}

.action-info p {
  font-size: 0.85rem;
  color: #64748b;
  line-height: 1.4;
}

.action-arrow {
  font-size: 1.5rem;
  color: #94a3b8;
  transition: all 0.3s;
}

.action-card:hover .action-arrow {
  color: #3b82f6;
  transform: translateX(4px);
}

@media (max-width: 768px) {
  .welcome-banner {
    padding: 2rem 1.5rem;
  }
  .welcome-info h1 {
    font-size: 1.5rem;
  }
  .welcome-logo-large {
    width: 120px;
  }
  .dashboard-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
}
</style>