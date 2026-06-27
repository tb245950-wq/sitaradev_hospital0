<template>
  <div class="analytics-layout">
    <Sidebar :is-open="isSidebarOpen" />

    <div class="main-content">
      <Navbar @toggle-sidebar="isSidebarOpen = !isSidebarOpen" />

      <main class="content-body">
        <!-- Header -->
        <div class="page-header">
          <div>
            <h1 class="page-title">
              Analitik
              <span class="role-badge" :class="roleBadgeClass">{{ roleLabel }}</span>
            </h1>
            <p class="page-subtitle">{{ todayFormatted || 'Data real-time sistem SITARA' }}</p>
          </div>
          <button class="refresh-btn" @click="refresh" :disabled="loading">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              :class="{ spinning: loading }">
              <polyline points="23 4 23 10 17 10"></polyline>
              <polyline points="1 20 1 14 7 14"></polyline>
              <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
            </svg>
            {{ loading ? 'Memuat...' : 'Refresh' }}
          </button>
        </div>

        <!-- Error State -->
        <div v-if="error && !loading" class="error-box">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
          </svg>
          <span>{{ error }}</span>
          <button @click="refresh" class="retry-btn">Coba Lagi</button>
        </div>

        <!-- Loading Skeleton -->
        <template v-if="loading">
          <div class="stats-grid">
            <div v-for="i in 4" :key="i" class="skeleton-card"></div>
          </div>
          <div class="skeleton-chart"></div>
        </template>

        <!-- ═══════════════════════════ ADMIN ═══════════════════════════ -->
        <template v-else-if="role === 'admin' && !error">
          <!-- Stats Grid Admin -->
          <div class="stats-grid">
            <StatCard
              title="Total Pasien"
              :value="stats.total_patients"
              icon="👥"
              icon-bg="#e0f2fe"
              icon-color="#0ea5e9"
              subtitle="Terdaftar di sistem"
            />
            <StatCard
              title="Pasien Baru Hari Ini"
              :value="stats.patients_today"
              icon="🆕"
              icon-bg="#d1fae5"
              icon-color="#059669"
              subtitle="Registrasi hari ini"
            />
            <StatCard
              title="Antrian Hari Ini"
              :value="stats.total_queues_today"
              icon="🎫"
              icon-bg="#fef3c7"
              icon-color="#d97706"
              subtitle="Total antrian masuk"
            />
            <StatCard
              title="Antrian Menunggu"
              :value="stats.waiting_queues"
              icon="⏳"
              icon-bg="#fee2e2"
              icon-color="#dc2626"
              subtitle="Belum dipanggil"
            />
            <StatCard
              title="Antrian Selesai"
              :value="stats.completed_queues"
              icon="✅"
              icon-bg="#d1fae5"
              icon-color="#059669"
              subtitle="Sudah ditangani"
            />
            <StatCard
              title="Total Assessment"
              :value="stats.total_assessments"
              icon="📋"
              icon-bg="#ede9fe"
              icon-color="#7c3aed"
              subtitle="Seluruh periode"
            />
            <StatCard
              title="Assessment Hari Ini"
              :value="stats.assessments_today"
              icon="🩺"
              icon-bg="#fce7f3"
              icon-color="#db2777"
              subtitle="Dilakukan dokter"
            />
            <StatCard
              title="Terapi Aktif"
              :value="stats.active_therapies"
              icon="🧠"
              icon-bg="#faf5ff"
              icon-color="#9333ea"
              subtitle="Program berjalan"
            />
          </div>

          <!-- Ringkasan Antrian Bar -->
          <div class="queue-summary-card">
            <h3>Ringkasan Antrian Hari Ini</h3>
            <div class="queue-bars">
              <div class="queue-bar-item">
                <span class="queue-bar-label">Menunggu</span>
                <div class="queue-bar-track">
                  <div class="queue-bar-fill waiting"
                    :style="{ width: queuePercent(stats.waiting_queues) }"></div>
                </div>
                <span class="queue-bar-count">{{ stats.waiting_queues }}</span>
              </div>
              <div class="queue-bar-item">
                <span class="queue-bar-label">Dipanggil</span>
                <div class="queue-bar-track">
                  <div class="queue-bar-fill calling"
                    :style="{ width: queuePercent(stats.calling_queues) }"></div>
                </div>
                <span class="queue-bar-count">{{ stats.calling_queues }}</span>
              </div>
              <div class="queue-bar-item">
                <span class="queue-bar-label">Selesai</span>
                <div class="queue-bar-track">
                  <div class="queue-bar-fill completed"
                    :style="{ width: queuePercent(stats.completed_queues) }"></div>
                </div>
                <span class="queue-bar-count">{{ stats.completed_queues }}</span>
              </div>
            </div>
          </div>
        </template>

        <!-- ═══════════════════════════ DOKTER ═══════════════════════════ -->
        <template v-else-if="role === 'dokter' && !error">
          <div class="stats-grid">
            <StatCard
              title="Pasien Saya"
              :value="stats.my_patients"
              icon="👥"
              icon-bg="#e0f2fe"
              icon-color="#0ea5e9"
              subtitle="Pernah assessment"
            />
            <StatCard
              title="Assessment Hari Ini"
              :value="stats.assessments_today_me"
              icon="📋"
              icon-bg="#ede9fe"
              icon-color="#7c3aed"
              subtitle="Sudah dilakukan"
            />
            <StatCard
              title="Antrian Menunggu"
              :value="stats.waiting_queues_me"
              icon="⏳"
              icon-bg="#fee2e2"
              icon-color="#dc2626"
              subtitle="Perlu ditangani"
            />
            <StatCard
              title="Antrian Selesai"
              :value="stats.completed_queues_me"
              icon="✅"
              icon-bg="#d1fae5"
              icon-color="#059669"
              subtitle="Sudah ditangani"
            />
          </div>

          <!-- Tren Kunjungan (7 hari) -->
          <div class="charts-row">
            <div class="chart-full">
              <VisitTrendsChart
                :data="visitTrends"
                period="week"
              />
            </div>
          </div>

          <!-- Aktivitas Terbaru -->
          <RecentActivitiesTable :activities="recentActivities" />
        </template>

        <!-- ═══════════════════════════ TERAPIS ═══════════════════════════ -->
        <template v-else-if="role === 'terapis' && !error">
          <div class="stats-grid">
            <StatCard
              title="Total Sesi Saya"
              :value="stats.my_sessions"
              icon="🧠"
              icon-bg="#faf5ff"
              icon-color="#9333ea"
              subtitle="Semua sesi"
            />
            <StatCard
              title="Sesi Hari Ini"
              :value="stats.sessions_today"
              icon="📅"
              icon-bg="#fef3c7"
              icon-color="#d97706"
              subtitle="Terjadwal"
            />
            <StatCard
              title="Terapi Aktif"
              :value="stats.active_therapies_me"
              icon="✅"
              icon-bg="#d1fae5"
              icon-color="#059669"
              subtitle="Program berjalan"
            />
            <StatCard
              title="Tingkat Kehadiran"
              :value="`${stats.attendance_rate}%`"
              icon="📈"
              icon-bg="#e0f2fe"
              icon-color="#0ea5e9"
              subtitle="Rata-rata pasien"
            />
          </div>

          <!-- Aktivitas Terbaru Terapis -->
          <RecentActivitiesTable :activities="recentActivities" />
        </template>

      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAnalyticsStore } from '../stores/analyticsStore'
import { useAuthStore } from '../../auth/stores/authStore'
import Sidebar from '../../../shared/components/layout/Sidebar.vue'
import Navbar from '../../../shared/components/layout/Navbar.vue'
import StatCard from '../components/StatCard.vue'
import VisitTrendsChart from '../components/VisitTrendsChart.vue'
import RecentActivitiesTable from '../components/RecentActivitiesTable.vue'

const analyticsStore = useAnalyticsStore()
const authStore = useAuthStore()

const isSidebarOpen = ref(false)

const { stats, visitTrends, recentActivities, todayFormatted, loading, error, role } = analyticsStore

const roleLabel = computed(() => {
  const labels = { admin: 'Administrator', dokter: 'Dokter', terapis: 'Terapis' }
  return labels[authStore.userRole] || authStore.userRole
})

const roleBadgeClass = computed(() => ({
  'badge-admin': authStore.userRole === 'admin',
  'badge-dokter': authStore.userRole === 'dokter',
  'badge-terapis': authStore.userRole === 'terapis',
}))

// Hitung persentase antrian relatif terhadap total
const queuePercent = (val) => {
  const total = (analyticsStore.stats.total_queues_today) || 1
  return `${Math.round((val / total) * 100)}%`
}

const refresh = () => analyticsStore.fetchAnalytics()

onMounted(() => {
  analyticsStore.fetchAnalytics()
})
</script>

<style scoped>
.analytics-layout {
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
  .main-content { margin-left: 0; }
}

.content-body {
  padding: 2rem;
  display: flex;
  flex-direction: column;
  gap: 1.75rem;
}

/* ── Page Header ─────────────────────────────────────────────── */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 1.5rem 2rem;
  border-radius: 1rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.page-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.page-subtitle {
  color: #64748b;
  margin-top: 0.25rem;
  font-size: 0.9rem;
}

.role-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 600;
}
.badge-admin  { background: #f3e8ff; color: #7c3aed; }
.badge-dokter { background: #dbeafe; color: #1e40af; }
.badge-terapis{ background: #d1fae5; color: #065f46; }

.refresh-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  background: #1e40af;
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.refresh-btn:hover:not(:disabled) { background: #1e3a8a; }
.refresh-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.refresh-btn svg { width: 16px; height: 16px; }
.spinning { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Error ───────────────────────────────────────────────────── */
.error-box {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #dc2626;
  padding: 1rem 1.5rem;
  border-radius: 0.75rem;
  font-size: 0.875rem;
}
.error-box svg { width: 20px; height: 20px; flex-shrink: 0; }
.retry-btn {
  margin-left: auto;
  padding: 0.375rem 0.875rem;
  background: #dc2626;
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-size: 0.8rem;
  cursor: pointer;
}

/* ── Skeleton ────────────────────────────────────────────────── */
.skeleton-card {
  height: 130px;
  border-radius: 1rem;
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}
.skeleton-chart {
  height: 300px;
  border-radius: 1rem;
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}
@keyframes shimmer {
  0%   { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* ── Stats Grid ──────────────────────────────────────────────── */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1.25rem;
}

/* ── Queue Summary ───────────────────────────────────────────── */
.queue-summary-card {
  background: white;
  padding: 1.75rem;
  border-radius: 1rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  border: 1px solid #f1f5f9;
}
.queue-summary-card h3 {
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 1.25rem 0;
}
.queue-bars { display: flex; flex-direction: column; gap: 1rem; }
.queue-bar-item {
  display: grid;
  grid-template-columns: 90px 1fr 40px;
  align-items: center;
  gap: 0.75rem;
}
.queue-bar-label { font-size: 0.85rem; color: #475569; font-weight: 500; }
.queue-bar-track {
  height: 10px;
  background: #f1f5f9;
  border-radius: 9999px;
  overflow: hidden;
}
.queue-bar-fill {
  height: 100%;
  border-radius: 9999px;
  transition: width 0.8s cubic-bezier(0.16, 1, 0.3, 1);
  min-width: 4px;
}
.queue-bar-fill.waiting  { background: #ef4444; }
.queue-bar-fill.calling  { background: #f59e0b; }
.queue-bar-fill.completed{ background: #10b981; }
.queue-bar-count { font-size: 0.85rem; font-weight: 700; color: #1e293b; text-align: right; }

/* ── Charts ──────────────────────────────────────────────────── */
.charts-row { display: grid; grid-template-columns: 1fr; gap: 1.5rem; }
.chart-full { min-height: 300px; }
</style>
