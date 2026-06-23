<template>
  <div class="doctor-dashboard">
    <header class="header">
      <h1>Dashboard Dokter</h1>
      <p>{{ analyticsStore.todayFormatted }}</p>
    </header>

    <div v-if="analyticsStore.loading" class="loading">Memuat data...</div>
    
    <div v-else class="dashboard-content">
      <!-- Stats Cards -->
      <div class="stats-grid">
        <StatCard title="Pasien Ditangani" :value="analyticsStore.stats.total_patients" icon="👥" icon-bg="#dbeafe" icon-color="#1e40af" />
        <StatCard title="Assessment Hari Ini" :value="analyticsStore.stats.assessments_today" icon="📋" icon-bg="#dcfce7" icon-color="#166534" />
        <StatCard title="Antrian Menunggu" :value="analyticsStore.stats.waiting_queues" icon="⏳" icon-bg="#fef3c7" icon-color="#92400e" />
        <StatCard title="Tingkat Kehadiran" :value="`${analyticsStore.stats.attendance_rate}%`" icon="✅" icon-bg="#f0fdf4" icon-color="#22c55e" />
      </div>

      <!-- Diagnosis Distribution -->
      <div class="charts-section">
        <DiagnosisDistributionChart :data="analyticsStore.diagnosisDistribution" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, watch } from 'vue'
import { useAnalyticsStore } from '../../analytics/stores/analyticsStore'
import StatCard from '../../analytics/components/StatCard.vue'
import DiagnosisDistributionChart from '../../analytics/components/DiagnosisDistributionChart.vue'

const analyticsStore = useAnalyticsStore()

onMounted(async () => {
  console.log('DoctorDashboard: Mounted, fetching analytics...')
  await analyticsStore.fetchAnalytics()
  console.log('DoctorDashboard: Store stats after fetch:', analyticsStore.stats)
})

watch(() => analyticsStore.stats, (newVal) => {
  console.log('DoctorDashboard: Stats changed:', newVal)
}, { deep: true })
</script>

<style scoped>
.doctor-dashboard { padding: 1rem; }
.header { margin-bottom: 2rem; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
.charts-section { background: white; padding: 1.5rem; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
</style>
