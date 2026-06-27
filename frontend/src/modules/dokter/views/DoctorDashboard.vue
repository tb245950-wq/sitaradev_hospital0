<template>
  <div class="doctor-dashboard">
    <header class="header">
      <h1>Dashboard Dokter</h1>
      <p>Selamat datang, Dr. {{ authStore.user?.name }}!</p>
      <p class="date">{{ analyticsStore.todayFormatted }}</p>
    </header>

    <div v-if="analyticsStore.loading" class="loading">Memuat data...</div>

    <div v-else class="dashboard-content">
      <div class="stats-grid">
        <StatCard title="Pasien Ditangani" :value="analyticsStore.stats.my_patients" icon="👥" icon-bg="#dbeafe" icon-color="#1e40af" />
        <StatCard title="Assessment Hari Ini" :value="analyticsStore.stats.assessments_today_me" icon="📋" icon-bg="#dcfce7" icon-color="#166534" />
        <StatCard title="Antrian Menunggu" :value="analyticsStore.stats.waiting_queues_me" icon="⏳" icon-bg="#fef3c7" icon-color="#92400e" />
        <StatCard title="Tingkat Kehadiran" :value="`${analyticsStore.stats.attendance_rate}%`" icon="✅" icon-bg="#f0fdf4" icon-color="#22c55e" />
      </div>

      <div class="charts-section">
        <DiagnosisDistributionChart :data="analyticsStore.diagnosisDistribution" />
      </div>

      <div class="quick-links">
        <h3>Akses Cepat</h3>
        <div class="links-grid">
          <router-link to="/queues" class="link-card">🎫 Antrian Pasien</router-link>
          <router-link to="/assessments" class="link-card">📋 Assessment</router-link>
          <router-link to="/therapies" class="link-card">🧠 Program Terapi</router-link>
          <router-link to="/patients" class="link-card">👥 Data Pasien</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useAnalyticsStore } from '../../analytics/stores/analyticsStore'
import { useAuthStore } from '../../auth/stores/authStore'
import StatCard from '../../analytics/components/StatCard.vue'
import DiagnosisDistributionChart from '../../analytics/components/DiagnosisDistributionChart.vue'

const analyticsStore = useAnalyticsStore()
const authStore = useAuthStore()
onMounted(() => analyticsStore.fetchAnalytics())
</script>

<style scoped>
.doctor-dashboard { padding: 1rem; }
.header { margin-bottom: 2rem; }
.header h1 { font-size: 1.5rem; font-weight: 700; color: #1e293b; }
.header p { color: #64748b; margin-top: 0.25rem; }
.header .date { font-size: 0.875rem; color: #94a3b8; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
.charts-section { background: white; padding: 1.5rem; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem; }
.quick-links { background: white; padding: 1.5rem; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.quick-links h3 { font-size: 1rem; font-weight: 600; color: #374151; margin-bottom: 1rem; }
.links-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; }
.link-card {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 0.75rem;
  text-decoration: none;
  color: #1e293b;
  font-weight: 500;
  font-size: 0.9rem;
  transition: all 0.2s;
  gap: 0.5rem;
}
.link-card:hover { background: #e0f2fe; border-color: #38bdf8; color: #0369a1; }
</style>
