<template>
  <div class="super-admin-dashboard">
    <div class="header">
      <h1>Dashboard Super Admin</h1>
      <p class="date">{{ todayFormatted }}</p>
    </div>

    <div v-if="loading" class="loading">Memuat data sistem...</div>
    <div v-if="error" class="error">{{ error }}</div>

    <div v-if="!loading" class="dashboard-content">
      <!-- Stats Grid -->
      <div class="stats-grid">
        <StatCard 
          title="Total User Aktif" 
          :value="stats.active_users" 
          icon-bg="#dbeafe" 
          icon-color="#1e40af"
        >
          <template #icon>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
          </template>
        </StatCard>

        <StatCard 
          title="Login Gagal Hari Ini" 
          :value="stats.failed_logins_today" 
          icon-bg="#fee2e2" 
          icon-color="#dc2626"
        >
          <template #icon>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <line x1="12" y1="8" x2="12" y2="12"/>
              <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
          </template>
        </StatCard>

        <StatCard 
          title="Storage Terpakai" 
          :value="`${stats.storage_used?.used_percent || 0}%`" 
          icon-bg="#fef3c7" 
          icon-color="#92400e"
        >
          <template #icon>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/>
              <path d="M21 3v5h-5"/>
              <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/>
              <path d="M3 21v-5h5"/>
            </svg>
          </template>
        </StatCard>
      </div>

      <!-- Navigation Menu -->
      <div class="nav-menu">
        <router-link to="/super-admin/users" class="nav-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
          Manajemen User
        </router-link>

        <router-link to="/super-admin/polis" class="nav-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 12a9 9 0 1 0 18 0 9 9 0 0 0-18 0"/>
            <line x1="12" y1="6" x2="12" y2="12"/>
            <line x1="12" y1="12" x2="15" y2="15"/>
          </svg>
          Manajemen Poli
        </router-link>

        <router-link to="/super-admin/audit-logs" class="nav-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="12" y1="11" x2="8" y2="11"/>
            <line x1="16" y1="15" x2="8" y2="15"/>
          </svg>
          Log Aktivitas
        </router-link>

        <router-link to="/super-admin/backup" class="nav-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="1"/>
            <path d="M12 1v6m6-2l-4 4M1 12h6m-2 6l4-4m12 2v-6m2 4l-4-4"/>
          </svg>
          Backup
        </router-link>

        <router-link to="/super-admin/settings" class="nav-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="3"/>
            <path d="M12 1v6m6 0h6M1 12h6m12 0v6M12 19v6"/>
          </svg>
          Pengaturan
        </router-link>
      </div>

      <!-- Recent Audit Logs -->
      <div class="recent-card">
        <div class="card-header">
          <h3>Log Aktivitas Terbaru</h3>
          <router-link to="/super-admin/audit-logs" class="btn-view-all">Lihat Semua →</router-link>
        </div>
        
        <div v-if="auditLogs.length === 0" class="empty-state">
          <p>Tidak ada aktivitas sistem</p>
        </div>

        <table v-else class="logs-table">
          <thead>
            <tr>
              <th>Waktu</th>
              <th>User</th>
              <th>Modul</th>
              <th>Aksi</th>
              <th>Keterangan</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="log in auditLogs" :key="log.id">
              <td class="time">{{ formatTime(log.created_at) }}</td>
              <td class="user">{{ log.user?.name || 'System' }}</td>
              <td class="module">{{ log.module }}</td>
              <td class="action">
                <span :class="`action-badge action-${log.action}`">
                  {{ log.action }}
                </span>
              </td>
              <td class="description">{{ log.description }}</td>
              <td class="status">
                <span :class="`status-badge status-${log.status}`">
                  {{ log.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../auth/stores/authStore'
import StatCard from '../../analytics/components/StatCard.vue'
import api from '../../../core/services/api'

const router = useRouter()
const authStore = useAuthStore()

const loading = ref(true)
const error = ref(null)
const todayFormatted = ref('')
const stats = ref({
  active_users: 0,
  failed_logins_today: 0,
  storage_used: { used_percent: 0 },
})
const auditLogs = ref([])

let refreshInterval = null

onMounted(() => {
  if (authStore.user?.role !== 'super_admin') {
    router.push('/dashboard')
    return
  }
  
  fetchDashboardData()
  refreshInterval = setInterval(fetchDashboardData, 30000)
})

onUnmounted(() => {
  if (refreshInterval) clearInterval(refreshInterval)
})

const fetchDashboardData = async () => {
  try {
    loading.value = true
    error.value = null

    console.log('Fetching super-admin dashboard...')
    const statsRes = await api.get('/super-admin/dashboard')
    console.log('Stats response:', statsRes)

    if (statsRes?.success && statsRes?.data) {
      stats.value = statsRes.data
      todayFormatted.value = statsRes.data.today_formatted || ''
    } else if (statsRes?.error) {
      error.value = statsRes.error
    }

    const logsRes = await api.get('/super-admin/audit-logs?limit=10')
    console.log('Logs response:', logsRes)
    
    if (logsRes?.success && logsRes?.data) {
      auditLogs.value = Array.isArray(logsRes.data) ? logsRes.data : []
    }
  } catch (err) {
    console.error('Dashboard fetch error:', err)
    error.value = err.message || 'Gagal memuat data dashboard'
  } finally {
    loading.value = false
  }
}

const formatTime = (dateStr) => {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return date.toLocaleString('id-ID', {
    month: 'short',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<style scoped>
.super-admin-dashboard { padding: 1rem; }
.header { margin-bottom: 1.5rem; }
.header h1 { font-size: 1.5rem; font-weight: 700; color: #1e293b; }
.date { color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; }
.loading { text-align: center; padding: 3rem; color: #94a3b8; }
.error { background: #fee2e2; color: #dc2626; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; }

.dashboard-content { display: flex; flex-direction: column; gap: 1.5rem; }

.stats-grid { 
  display: grid; 
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
  gap: 1.25rem; 
}

/* Navigation Menu */
.nav-menu { 
  display: grid; 
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); 
  gap: 1rem;
  background: white;
  padding: 1.5rem;
  border-radius: 0.75rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

.nav-btn { 
  display: flex; 
  flex-direction: column; 
  align-items: center; 
  gap: 0.75rem;
  padding: 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  text-decoration: none;
  color: #475569;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.3s ease;
}

.nav-btn:hover { 
  border-color: #1e40af;
  color: #1e40af;
  background: #eff6ff;
}

.nav-btn svg { stroke: currentColor; }

/* Recent Card */
.recent-card { 
  background: white; 
  padding: 1.5rem; 
  border-radius: 0.75rem; 
  box-shadow: 0 1px 3px rgba(0,0,0,0.08); 
}

.card-header { 
  display: flex; 
  justify-content: space-between; 
  align-items: center; 
  margin-bottom: 1rem; 
}

.card-header h3 { 
  font-size: 1rem; 
  font-weight: 600; 
  color: #374151; 
  margin: 0; 
}

.btn-view-all { 
  font-size: 0.8125rem; 
  color: #1e40af; 
  font-weight: 600; 
  text-decoration: none; 
}

.empty-state { 
  text-align: center; 
  padding: 2rem; 
  color: #94a3b8; 
}

.logs-table { 
  width: 100%; 
  border-collapse: collapse; 
  font-size: 0.875rem; 
}

.logs-table thead { 
  background: #f8fafc; 
  border-bottom: 1px solid #e2e8f0; 
}

.logs-table th { 
  padding: 0.75rem; 
  text-align: left; 
  font-weight: 600; 
  color: #475569; 
}

.logs-table td { 
  padding: 0.75rem; 
  border-bottom: 1px solid #e2e8f0; 
  color: #475569; 
}

.logs-table tbody tr:hover { 
  background: #f8fafc; 
}

.time { 
  color: #64748b; 
  font-size: 0.8125rem; 
  min-width: 120px; 
}

.user { 
  font-weight: 500; 
  color: #1e293b; 
}

.module { 
  text-transform: uppercase; 
  font-size: 0.75rem; 
  color: #64748b; 
}

.action-badge { 
  display: inline-block; 
  padding: 0.25rem 0.5rem; 
  border-radius: 0.25rem; 
  font-size: 0.75rem; 
  font-weight: 600; 
  text-transform: uppercase; 
}

.action-create { 
  background: #dcfce7; 
  color: #166534; 
}

.action-update { 
  background: #e0e7ff; 
  color: #3730a3; 
}

.action-delete { 
  background: #fee2e2; 
  color: #991b1b; 
}

.action-reset_password { 
  background: #fef3c7; 
  color: #92400e; 
}

.status-badge { 
  display: inline-block; 
  padding: 0.25rem 0.5rem; 
  border-radius: 0.25rem; 
  font-size: 0.75rem; 
  font-weight: 600; 
  text-transform: uppercase; 
}

.status-success { 
  background: #dcfce7; 
  color: #166534; 
}

.status-failed { 
  background: #fee2e2; 
  color: #991b1b; 
}

.status-warning { 
  background: #fef3c7; 
  color: #92400e; 
}

@media (max-width: 768px) { 
  .nav-menu { grid-template-columns: repeat(2, 1fr); }
  .logs-table { font-size: 0.75rem; }
}
</style>
