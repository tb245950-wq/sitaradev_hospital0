<template>
  <div class="admin-dashboard">
    <div class="header">
      <h1>Dashboard Admin Klinik</h1>
      <p class="date">{{ analyticsStore.todayFormatted }}</p>
    </div>

    <div v-if="analyticsStore.loading" class="loading">Memuat data...</div>
    <div v-if="analyticsStore.error" class="error">{{ analyticsStore.error }}</div>

    <div v-if="!analyticsStore.loading" class="dashboard-content">
      <!-- Stats Grid -->
      <div class="stats-grid">
        <StatCard title="Total Pasien" :value="analyticsStore.stats.total_patients" icon-bg="#dbeafe" icon-color="#1e40af">
          <template #icon><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></template>
        </StatCard>
        <StatCard title="Pasien Baru Hari Ini" :value="analyticsStore.stats.patients_today" icon-bg="#d1fae5" icon-color="#059669">
          <template #icon><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg></template>
        </StatCard>
        <StatCard title="Antrian Menunggu" :value="analyticsStore.stats.waiting_queues" icon-bg="#fee2e2" icon-color="#dc2626">
          <template #icon><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></template>
        </StatCard>
        <StatCard title="Antrian Selesai" :value="analyticsStore.stats.completed_queues" icon-bg="#d1fae5" icon-color="#059669">
          <template #icon><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></template>
        </StatCard>
        <StatCard title="Assessment Hari Ini" :value="analyticsStore.stats.assessments_today" icon-bg="#ede9fe" icon-color="#7c3aed">
          <template #icon><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg></template>
        </StatCard>
        <StatCard title="Terapi Aktif" :value="analyticsStore.stats.active_therapies" icon-bg="#faf5ff" icon-color="#9333ea">
          <template #icon><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></template>
        </StatCard>
      </div>

      <!-- Charts -->
      <div class="charts-grid">
        <VisitTrendsChart :data="analyticsStore.visitTrends" />
        <DiagnosisDistributionChart :data="analyticsStore.diagnosisDistribution" />
      </div>

      <!-- Antrian Aktif Hari Ini -->
      <div class="queue-section">
        <div class="queue-section-header">
          <h3>Antrian Aktif Hari Ini</h3>
          <router-link to="/waiting-list" class="btn-view-all">Kelola Antrian →</router-link>
        </div>

        <div class="queue-cols">
          <!-- Menunggu -->
          <div class="queue-col">
            <div class="queue-col-title waiting">Menunggu ({{ queueStore.stats.waiting_count || 0 }})</div>
            <div v-if="queueStore.stats.waiting?.length" class="queue-patient-list">
              <div v-for="q in queueStore.stats.waiting" :key="q.id" class="queue-patient-item">
                <span class="q-num">{{ q.nomor }}</span>
                <span class="q-name">{{ q.pasien?.nama || '-' }}</span>
                <span v-if="q.prioritas > 5" class="q-priority">❗</span>
              </div>
            </div>
            <div v-else class="queue-empty">Tidak ada antrian</div>
          </div>

          <!-- Dipanggil -->
          <div class="queue-col">
            <div class="queue-col-title calling">Dipanggil ({{ queueStore.stats.calling_count || 0 }})</div>
            <div v-if="queueStore.stats.calling?.length" class="queue-patient-list">
              <div v-for="q in queueStore.stats.calling" :key="q.id" class="queue-patient-item calling">
                <span class="q-num">{{ q.nomor }}</span>
                <span class="q-name">{{ q.pasien?.nama || '-' }}</span>
              </div>
            </div>
            <div v-else class="queue-empty">Tidak ada</div>
          </div>

          <!-- Selesai -->
          <div class="queue-col">
            <div class="queue-col-title done">Selesai ({{ queueStore.stats.completed_count || 0 }})</div>
            <div v-if="queueStore.stats.completed?.length" class="queue-patient-list">
              <div v-for="q in queueStore.stats.completed" :key="q.id" class="queue-patient-item done">
                <span class="q-num">{{ q.nomor }}</span>
                <span class="q-name">{{ q.pasien?.nama || '-' }}</span>
              </div>
            </div>
            <div v-else class="queue-empty">Tidak ada</div>
          </div>
        </div>
      </div>

      <!-- Aktivitas Terbaru -->
      <div class="recent-card">
        <h3>Aktivitas Terbaru</h3>
        <RecentActivitiesTable :activities="analyticsStore.recentActivities" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'
import { useAnalyticsStore } from '../../analytics/stores/analyticsStore'
import { useQueueStore } from '../../queue/stores/queueStore'
import StatCard from '../../analytics/components/StatCard.vue'
import VisitTrendsChart from '../../analytics/components/VisitTrendsChart.vue'
import DiagnosisDistributionChart from '../../analytics/components/DiagnosisDistributionChart.vue'
import RecentActivitiesTable from '../../analytics/components/RecentActivitiesTable.vue'

const analyticsStore = useAnalyticsStore()
const queueStore = useQueueStore()

let interval = null

onMounted(() => {
  analyticsStore.fetchAnalytics()
  queueStore.getStats()
  interval = setInterval(() => queueStore.getStats(), 30000)
})

onUnmounted(() => { if (interval) clearInterval(interval) })
</script>

<style scoped>
.admin-dashboard { padding: 1rem; }
.header { margin-bottom: 1.5rem; }
.header h1 { font-size: 1.5rem; font-weight: 700; color: #1e293b; }
.date { color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; }
.loading { text-align: center; padding: 3rem; color: #94a3b8; }
.error { background: #fee2e2; color: #dc2626; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem; }
.charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; min-height: 320px; }
.recent-card { background: white; padding: 1.5rem; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.recent-card h3 { font-size: 1rem; font-weight: 600; color: #374151; margin-bottom: 1rem; }

/* Queue Section */
.queue-section { background: white; padding: 1.5rem; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
.queue-section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
.queue-section-header h3 { font-size: 1rem; font-weight: 600; color: #374151; margin: 0; }
.btn-view-all { font-size: 0.8125rem; color: #1e40af; font-weight: 600; text-decoration: none; }
.queue-cols { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
.queue-col { border-radius: 0.5rem; overflow: hidden; border: 1px solid #e2e8f0; }
.queue-col-title { padding: 0.5rem 0.75rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
.queue-col-title.waiting { background: #fef3c7; color: #92400e; }
.queue-col-title.calling { background: #dbeafe; color: #1e40af; }
.queue-col-title.done { background: #dcfce7; color: #166534; }
.queue-patient-list { max-height: 200px; overflow-y: auto; }
.queue-patient-item { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.75rem; border-bottom: 1px solid #f1f5f9; font-size: 0.8125rem; }
.queue-patient-item.calling { background: #eff6ff; }
.queue-patient-item.done { background: #f0fdf4; opacity: 0.7; }
.q-num { font-weight: 700; color: #1e40af; min-width: 40px; font-family: monospace; }
.q-name { flex: 1; color: #374151; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.q-priority { font-size: 0.75rem; }
.queue-empty { padding: 1rem; text-align: center; font-size: 0.8125rem; color: #94a3b8; }

@media (max-width: 768px) { .charts-grid { grid-template-columns: 1fr; } .queue-cols { grid-template-columns: 1fr; } }
</style>
