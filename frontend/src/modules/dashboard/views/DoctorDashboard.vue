<template>
  <div class="doctor-dashboard">
    <div class="header">
      <h1>Dashboard Dokter</h1>
      <p class="date">{{ analyticsStore.todayFormatted }}</p>
    </div>

    <div v-if="analyticsStore.loading" class="loading">Memuat data...</div>
    <div v-if="analyticsStore.error" class="error">{{ analyticsStore.error }}</div>

    <div v-if="!analyticsStore.loading" class="dashboard-content">
      <div class="stats-grid">
        <StatCard title="Pasien Saya" :value="analyticsStore.stats.my_patients" icon-bg="#dbeafe" icon-color="#1e40af">
          <template #icon><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></template>
        </StatCard>
        <StatCard title="Assessment Hari Ini" :value="analyticsStore.stats.assessments_today_me" icon-bg="#dcfce7" icon-color="#166534">
          <template #icon><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></template>
        </StatCard>
        <StatCard title="Antrian Menunggu" :value="analyticsStore.stats.waiting_queues_me" icon-bg="#fef3c7" icon-color="#92400e">
          <template #icon><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></template>
        </StatCard>
        <StatCard title="Antrian Selesai" :value="analyticsStore.stats.completed_queues_me" icon-bg="#f0fdf4" icon-color="#22c55e">
          <template #icon><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></template>
        </StatCard>
      </div>

      <div class="chart-card">
        <VisitTrendsChart :data="analyticsStore.visitTrends" />
      </div>

      <div class="recent-card">
        <h3>Aktivitas Terbaru</h3>
        <RecentActivitiesTable :activities="analyticsStore.recentActivities" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useAnalyticsStore } from '../../analytics/stores/analyticsStore'
import StatCard from '../../analytics/components/StatCard.vue'
import VisitTrendsChart from '../../analytics/components/VisitTrendsChart.vue'
import RecentActivitiesTable from '../../analytics/components/RecentActivitiesTable.vue'

const analyticsStore = useAnalyticsStore()
onMounted(() => analyticsStore.fetchAnalytics())
</script>

<style scoped>
.doctor-dashboard { padding: 1rem; }
.header { margin-bottom: 1.5rem; }
.header h1 { font-size: 1.5rem; font-weight: 700; color: #1e293b; }
.date { color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; }
.loading { text-align: center; padding: 3rem; color: #94a3b8; }
.error { background: #fee2e2; color: #dc2626; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem; }
.chart-card { background: white; padding: 1.5rem; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08); margin-bottom: 1.5rem; min-height: 320px; }
.recent-card { background: white; padding: 1.5rem; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.recent-card h3 { font-size: 1rem; font-weight: 600; color: #374151; margin-bottom: 1rem; }
</style>
