<template>
  <div class="admin-dashboard">
    <header class="header">
      <h1>Dashboard Admin</h1>
      <p>{{ analyticsStore.todayFormatted }}</p>
    </header>

    <div v-if="analyticsStore.loading" class="loading">Memuat data analytics...</div>
    
    <div v-else class="dashboard-content">
      <div class="stats-grid">
        <StatCard title="Total Pasien" :value="analyticsStore.stats.total_patients" icon="👥" icon-bg="#dbeafe" icon-color="#1e40af" />
        <StatCard title="Assessment Hari Ini" :value="analyticsStore.stats.assessments_today" icon="📋" icon-bg="#dcfce7" icon-color="#166534" />
        <StatCard title="Antrian Menunggu" :value="analyticsStore.stats.waiting_queues" icon="⏳" icon-bg="#fef3c7" icon-color="#92400e" />
      </div>

      <div class="charts-grid">
        <VisitTrendsChart :data="analyticsStore.visitTrends" />
        <DiagnosisDistributionChart :data="analyticsStore.diagnosisDistribution" />
      </div>

      <div class="recent-activities">
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
import DiagnosisDistributionChart from '../../analytics/components/DiagnosisDistributionChart.vue'
import RecentActivitiesTable from '../../analytics/components/RecentActivitiesTable.vue'

const analyticsStore = useAnalyticsStore()
onMounted(() => analyticsStore.fetchAnalytics())
</script>

<style scoped>
.admin-dashboard { padding: 1rem; }
.header { margin-bottom: 2rem; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
.charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem; }
.recent-activities { background: white; padding: 1.5rem; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
</style>
