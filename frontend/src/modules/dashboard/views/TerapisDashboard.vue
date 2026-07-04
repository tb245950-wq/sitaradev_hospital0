<template>
  <div class="terapis-dashboard">
    <div class="header">
      <h1>Dashboard Terapis</h1>
      <p class="date">{{ analyticsStore.todayFormatted }}</p>
    </div>

    <div v-if="analyticsStore.loading" class="loading">Memuat data...</div>
    <div v-if="analyticsStore.error" class="error">{{ analyticsStore.error }}</div>

    <div v-if="!analyticsStore.loading" class="dashboard-content">
      <div class="stats-grid">
        <StatCard title="Total Sesi" :value="analyticsStore.stats.my_sessions" icon-bg="#faf5ff" icon-color="#9333ea">
          <template #icon><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></template>
        </StatCard>
        <StatCard title="Sesi Hari Ini" :value="analyticsStore.stats.sessions_today" icon-bg="#fef3c7" icon-color="#d97706">
          <template #icon><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></template>
        </StatCard>
        <StatCard title="Terapi Aktif" :value="analyticsStore.stats.active_therapies_me" icon-bg="#d1fae5" icon-color="#059669">
          <template #icon><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></template>
        </StatCard>
        <StatCard title="Tingkat Kehadiran" :value="`${analyticsStore.stats.attendance_rate}%`" icon-bg="#e0f2fe" icon-color="#0ea5e9">
          <template #icon><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></template>
        </StatCard>
      </div>

      <div class="quick-links">
        <h3>Akses Cepat</h3>
        <div class="links-grid">
          <router-link to="/queues" class="link-card">🎫 Antrian Pasien</router-link>
          <router-link to="/therapies" class="link-card">🧠 Program Terapi</router-link>
          <router-link to="/monitoring" class="link-card">📈 Monitoring</router-link>
          <router-link to="/patients" class="link-card">👥 Data Pasien</router-link>
        </div>
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
import RecentActivitiesTable from '../../analytics/components/RecentActivitiesTable.vue'

const analyticsStore = useAnalyticsStore()
onMounted(() => analyticsStore.fetchAnalytics())
</script>

<style scoped>
.terapis-dashboard { padding: 1rem; }
.header { margin-bottom: 1.5rem; }
.header h1 { font-size: 1.5rem; font-weight: 700; color: #1e293b; }
.date { color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; }
.loading { text-align: center; padding: 3rem; color: #94a3b8; }
.error { background: #fee2e2; color: #dc2626; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem; }
.quick-links { background: white; padding: 1.5rem; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
.quick-links h3 { font-size: 1rem; font-weight: 600; color: #374151; margin-bottom: 1rem; }
.links-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem; }
.link-card { display: flex; align-items: center; justify-content: center; padding: 0.875rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; text-decoration: none; color: #1e293b; font-weight: 500; font-size: 0.875rem; gap: 0.5rem; transition: all 0.2s; }
.link-card:hover { background: #f0fdf4; border-color: #86efac; }
.recent-card { background: white; padding: 1.5rem; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.recent-card h3 { font-size: 1rem; font-weight: 600; color: #374151; margin-bottom: 1rem; }
</style>
