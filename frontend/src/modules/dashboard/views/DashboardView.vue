<template>
  <div class="dashboard-layout">
    <Sidebar :is-open="isSidebarOpen" />
    
    <div class="main-content">
      <Navbar @toggle-sidebar="isSidebarOpen = !isSidebarOpen" />
      
      <main class="content-body">
        <!-- Loading State -->
        <div v-if="analyticsStore.loading" class="loading-container">
          <div class="loading-spinner"></div>
          <p>Memuat data analytics...</p>
        </div>
        
        <!-- Error State -->
        <div v-else-if="analyticsStore.error" class="error-container">
          <p>{{ analyticsStore.error }}</p>
          <button @click="analyticsStore.fetchAnalytics" class="btn-retry">Coba Lagi</button>
        </div>
        
        <!-- Dashboard Content -->
        <div v-else>
          <!-- Stat Cards -->
          <div class="stats-grid">
            <StatCard
              title="Total Pasien"
              :value="analyticsStore.stats.total_patients.value"
              icon="👥"
              icon-bg="#e0f2fe"
              icon-color="#0ea5e9"
              :trend="analyticsStore.stats.total_patients.trend"
              :trend-label="analyticsStore.stats.total_patients.trend_label"
            />
            
            <StatCard
              title="Sesi Terapi Hari Ini"
              :value="analyticsStore.stats.today_sessions.value"
              icon="📋"
              icon-bg="#f0fdf4"
              icon-color="#22c55e"
              :subtitle="`${analyticsStore.stats.today_sessions.completed} Selesai, ${analyticsStore.stats.today_sessions.remaining} Tersisa`"
            />
            
            <StatCard
              title="Waiting List"
              :value="analyticsStore.stats.waiting_list.value"
              icon="⏳"
              icon-bg="#fef3c7"
              icon-color="#f59e0b"
              :subtitle="`${analyticsStore.stats.waiting_list.high_priority} Prioritas Tinggi`"
            />
            
            <StatCard
              title="Tingkat Kehadiran"
              :value="`${analyticsStore.stats.attendance_rate.value}%`"
              icon="✅"
              icon-bg="#f0fdf4"
              icon-color="#22c55e"
              :trend-label="analyticsStore.stats.attendance_rate.period"
            />
          </div>
          
          <!-- Charts Row -->
          <div class="charts-grid">
            <div class="chart-item-main">
              <VisitTrendsChart
                :data="analyticsStore.visitTrends"
                :period="analyticsStore.selectedPeriod"
                @period-change="handlePeriodChange"
              />
            </div>
            
            <div class="chart-item-side">
              <DiagnosisDistributionChart
                :data="analyticsStore.diagnosisDistribution"
              />
            </div>
          </div>
          
          <!-- Recent Activities -->
          <div class="activities-section">
            <RecentActivitiesTable
              :activities="analyticsStore.recentActivities"
            />
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAnalyticsStore } from '../../analytics/stores/analyticsStore'
import { useAuthStore } from '../../auth/stores/authStore'
import Sidebar from '../../../shared/components/layout/Sidebar.vue'
import Navbar from '../../../shared/components/layout/Navbar.vue'
import StatCard from '../../analytics/components/StatCard.vue'
import VisitTrendsChart from '../../analytics/components/VisitTrendsChart.vue'
import DiagnosisDistributionChart from '../../analytics/components/DiagnosisDistributionChart.vue'
import RecentActivitiesTable from '../../analytics/components/RecentActivitiesTable.vue'

const analyticsStore = useAnalyticsStore()
const authStore = useAuthStore()
const isSidebarOpen = ref(false)

const handlePeriodChange = (period) => {
  analyticsStore.updatePeriod(period)
}

onMounted(() => {
  analyticsStore.fetchAnalytics()
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
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* Loading & Error States */
.loading-container,
.error-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 400px;
  background: white;
  border-radius: 1rem;
  padding: 3rem;
  text-align: center;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #f1f5f9;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.btn-retry {
  margin-top: 1rem;
  padding: 0.5rem 1.25rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  font-size: 0.875rem;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.25rem;
}

/* Charts Grid */
.charts-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 1.25rem;
}

@media (max-width: 1024px) {
  .charts-grid {
    grid-template-columns: 1fr;
  }
}

.activities-section {
  margin-bottom: 1rem;
}
</style>
