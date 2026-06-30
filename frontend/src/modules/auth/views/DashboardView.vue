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

// Computed: Stats yang ditampilkan berdasarkan role
const statsToShow = computed(() => {
  if (authStore.isAdmin) {
    return [
      { title: 'Total Pasien', value: stats.value.total_pasien, label: 'Terdaftar di sistem' },
      { title: 'Antrian Hari Ini', value: stats.value.antrian_hari_ini, label: 'Pasien datang' },
      { title: 'Assessment Hari Ini', value: stats.value.assessment_hari_ini, label: 'Dilakukan dokter' },
      { title: 'Terapi Aktif', value: stats.value.terapi_aktif, label: 'Sedang berjalan' }
    ]
  } else if (authStore.isDokter) {
    return [
      { title: 'Total Pasien', value: stats.value.total_pasien, label: 'Dalam perawatan' },
      { title: 'Antrian Menunggu', value: stats.value.antrian_menunggu, label: 'Perlu ditangani' },
      { title: 'Assessment Hari Ini', value: stats.value.assessment_hari_ini, label: 'Sudah dilakukan' },
      { title: 'Terapi Aktif', value: stats.value.terapi_aktif, label: 'Program berjalan' }
    ]
  } else if (authStore.isTerapis) {
    return [
      { title: 'Pasien Saya', value: stats.value.total_pasien, label: 'Dalam terapi' },
      { title: 'Sesi Hari Ini', value: stats.value.monitoring_hari_ini, label: 'Terjadwal' },
      { title: 'Terapi Aktif', value: stats.value.terapi_aktif, label: 'Program berjalan' },
      { title: 'Progress Rata-rata', value: '75%', label: 'Kemajuan pasien' }
    ]
  }
  return []
})

// Computed: Quick Actions per Role
const quickActions = computed(() => {
  if (authStore.isAdmin) {
    return [
      { title: 'Kelola User',       description: 'Tambah, edit, atau nonaktifkan akun staff', path: '/users' },
      { title: 'Manajemen Poli',    description: 'Kelola daftar poli/klinik yang tersedia',   path: '/settings/poli' },
      { title: 'Lihat Laporan',     description: 'Laporan harian, bulanan, dan statistik',    path: '/reports' },
      { title: 'Data Pasien',       description: 'Kelola data pasien terdaftar',               path: '/patients' },
      { title: 'Antrian Hari Ini',  description: 'Pantau dan kelola antrian pasien',           path: '/queues' },
      { title: 'Pengaturan',        description: 'Konfigurasi sistem',                         path: '/settings' },
    ]
  } else if (authStore.isDokter) {
    return [
      { title: 'Antrian Pasien', description: 'Panggil dan tangani pasien', path: '/queues' },
      { title: 'Assessment Baru', description: 'Buat assessment medis baru', path: '/assessments/create' },
      { title: 'Program Terapi', description: 'Buat program terapi untuk pasien', path: '/therapies' },
      { title: 'Laporan Medis', description: 'Lihat laporan medis', path: '/reports' }
    ]
  } else if (authStore.isTerapis) {
    return [
      { title: 'Monitoring Hari Ini', description: 'Lihat jadwal sesi terapi', path: '/monitoring' },
      { title: 'Catat Progress', description: 'Input progress sesi terapi', path: '/therapies/create' },
      { title: 'Data Pasien', description: 'Lihat data pasien (read-only)', path: '/patients' },
      { title: 'Program Terapi', description: 'Lihat program terapi aktif', path: '/therapies' }
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
