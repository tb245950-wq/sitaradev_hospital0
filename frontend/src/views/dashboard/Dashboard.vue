<template>
  <div class="dashboard-layout">
    <Sidebar :is-open="isSidebarOpen" />
    
    <div class="main-content">
      <Navbar @toggle-sidebar="isSidebarOpen = !isSidebarOpen" />
      
      <main class="content-body">
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon" style="background: #e0f2fe; color: #0ea5e9;">👥</div>
            <div class="stat-info">
              <h3>Total Pasien</h3>
              <p class="stat-value">{{ stats.total_pasien }}</p>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon" style="background: #f0fdf4; color: #22c55e;">📋</div>
            <div class="stat-info">
              <h3>Antrian Menunggu</h3>
              <p class="stat-value">{{ stats.antrian_menunggu }}</p>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon" style="background: #fef2f2; color: #ef4444;">🩺</div>
            <div class="stat-info">
              <h3>Assessment Hari Ini</h3>
              <p class="stat-value">{{ stats.assessment_hari_ini }}</p>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon" style="background: #faf5ff; color: #a855f7;">🧠</div>
            <div class="stat-info">
              <h3>Terapi Aktif</h3>
              <p class="stat-value">{{ stats.terapi_aktif }}</p>
            </div>
          </div>
        </div>

        <div class="welcome-banner">
          <div class="welcome-text">
            <h1>Selamat Datang, {{ authStore.user?.name }}!</h1>
            <p>Sistem Informasi Terpadu Assessment dan Rekam Anak (SITARA) siap melayani Anda hari ini.</p>
          </div>
          <img src="/logo-sitara.png" alt="SITARA" class="welcome-img" />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import Sidebar from '@/components/layout/Sidebar.vue'
import Navbar from '@/components/layout/Navbar.vue'

const authStore = useAuthStore()
const isSidebarOpen = ref(false)

const stats = ref({
  total_pasien: 0,
  antrian_menunggu: 0,
  assessment_hari_ini: 0,
  terapi_aktif: 0
})

const fetchStats = async () => {
  try {
    const response = await api.get('/reports/dashboard')
    if (response.data.success) {
      stats.value = response.data.data
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
}

.stat-icon {
  width: 56px;
  height: 56px;
  border-radius: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.stat-info h3 {
  font-size: 0.875rem;
  color: #64748b;
  margin-bottom: 0.25rem;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
}

.welcome-banner {
  background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
  border-radius: 1.5rem;
  padding: 3rem;
  color: white;
  display: flex;
  align-items: center;
  justify-content: space-between;
  overflow: hidden;
  position: relative;
}

.welcome-text h1 {
  font-size: 2rem;
  margin-bottom: 1rem;
}

.welcome-text p {
  font-size: 1.125rem;
  opacity: 0.9;
  max-width: 500px;
}

.welcome-img {
  width: 150px;
  opacity: 0.2;
  position: absolute;
  right: -20px;
  bottom: -20px;
}

@media (min-width: 768px) {
  .welcome-img {
    position: static;
    opacity: 1;
    width: 200px;
  }
}
</style>
