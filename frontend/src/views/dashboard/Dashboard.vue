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
          <div class="welcome-content">
            <div class="welcome-image-container">
              <img src="@/assets/SITARA_RM_BG.png" alt="SITARA" class="welcome-img" />
            </div>
            <div class="welcome-text">
              <h1>Selamat Datang, {{ authStore.user?.name }}!</h1>
              <p>Sistem Informasi Terpadu Assessment dan Rekam Anak (SITARA)</p>
              <div class="welcome-badge">Pusat Layanan Terpadu</div>
            </div>
          </div>
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
  padding: 4rem 2rem;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  box-shadow: 0 10px 25px rgba(30, 64, 175, 0.2);
  overflow: hidden;
}

.welcome-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2rem;
  max-width: 800px;
}

.welcome-image-container {
  background: white;
  padding: 1.5rem;
  border-radius: 2rem;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.welcome-img {
  width: 180px;
  height: auto;
  display: block;
}

.welcome-text h1 {
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
  font-weight: 800;
  letter-spacing: -0.025em;
}

.welcome-text p {
  font-size: 1.25rem;
  opacity: 0.9;
  font-weight: 500;
  margin-bottom: 1.5rem;
}

.welcome-badge {
  display: inline-block;
  background: rgba(255, 255, 255, 0.2);
  padding: 0.5rem 1.25rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 600;
  backdrop-filter: blur(4px);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

@media (max-width: 768px) {
  .welcome-text h1 {
    font-size: 1.75rem;
  }
  .welcome-img {
    width: 120px;
  }
}
</style>
