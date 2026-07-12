<template>
  <div class="page-container">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Audit Log Sistem</h1>
        <p class="page-subtitle">Catatan seluruh aktivitas CRUD dan anomali semua user</p>
      </div>
      <button @click="fetchLogs" class="btn-refresh" :class="{ spinning: loading }">
        ↻ Refresh
      </button>
    </div>

    <!-- Anomaly Alert Banner -->
    <div v-if="anomalyToday > 0" class="anomaly-banner">
      <span class="anomaly-icon">⚠️</span>
      <strong>{{ anomalyToday }} anomali terdeteksi hari ini</strong>
      <span>(hapus data, reset password, export, atau aksi gagal)</span>
      <button @click="toggleAnomalyOnly" class="btn-anomaly-filter" :class="{ active: filters.anomaly_only }">
        {{ filters.anomaly_only ? 'Tampilkan Semua' : 'Lihat Anomali Saja' }}
      </button>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
      <input
        v-model="filters.search"
        @input="debouncedFetch"
        type="text"
        placeholder="Cari user, deskripsi..."
        class="filter-input filter-search"
      />
      <select v-model="filters.module" @change="applyFilters" class="filter-input">
        <option value="">Semua Modul</option>
        <option value="user">User</option>
        <option value="backup">Backup</option>
        <option value="settings">Settings</option>
        <option value="poli">Poli</option>
      </select>
      <select v-model="filters.action" @change="applyFilters" class="filter-input">
        <option value="">Semua Aksi</option>
        <option value="create">Create</option>
        <option value="update">Update</option>
        <option value="delete">Delete</option>
        <option value="reset_password">Reset Password</option>
        <option value="export">Export</option>
        <option value="download">Download</option>
      </select>
      <select v-model="filters.status" @change="applyFilters" class="filter-input">
        <option value="">Semua Status</option>
        <option value="success">Success</option>
        <option value="failed">Failed</option>
      </select>
      <button v-if="hasActiveFilters" @click="clearFilters" class="btn-clear-filter">✕ Reset</button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Memuat audit log...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="error-box">
      {{ error }}
      <button @click="fetchLogs" class="btn-retry">Coba Lagi</button>
    </div>

    <!-- Log List -->
    <div v-else>
      <div v-if="logs.length === 0" class="empty-state">
        Tidak ada log yang sesuai filter.
      </div>

      <div v-else class="log-list">
        <div
          v-for="log in logs"
          :key="log.id"
          class="log-item"
          :class="{ 'log-anomaly': log.is_anomaly, 'log-failed': log.status === 'failed' }"
        >
          <!-- Baris utama -->
          <div class="log-main">
            <!-- Waktu -->
            <div class="log-time">
              <span class="time-full">{{ formatTime(log.created_at) }}</span>
            </div>

            <!-- Info User -->
            <div class="log-user">
              <div class="user-name">{{ log.user?.name || 'System' }}</div>
              <div class="user-meta">
                <span class="user-email">{{ log.user?.email || '-' }}</span>
                <span v-if="log.user?.role" class="user-role-badge" :class="`role-${log.user.role}`">
                  {{ log.user.role }}
                </span>
              </div>
            </div>

            <!-- Modul + Aksi -->
            <div class="log-action-group">
              <span class="module-badge">{{ log.module }}</span>
              <span class="action-badge" :class="`action-${log.action}`">
                {{ actionLabel(log.action) }}
              </span>
            </div>

            <!-- Deskripsi -->
            <div class="log-desc">
              <span v-if="log.is_anomaly" class="anomaly-tag">⚠ Anomali</span>
              {{ log.description }}
            </div>

            <!-- Status + IP -->
            <div class="log-right">
              <span class="status-badge" :class="`status-${log.status}`">{{ log.status }}</span>
              <span class="ip-address">{{ log.ip_address || '-' }}</span>
            </div>

            <!-- Expand Toggle -->
            <button
              v-if="log.old_values || log.new_values || log.error_message"
              @click="toggleExpand(log.id)"
              class="btn-expand"
            >
              {{ expanded.has(log.id) ? '▲' : '▼' }}
            </button>
          </div>

          <!-- Detail Expand -->
          <div v-if="expanded.has(log.id)" class="log-detail">
            <div v-if="log.error_message" class="detail-block detail-error">
              <div class="detail-label">Error</div>
              <div class="detail-value error-text">{{ log.error_message }}</div>
            </div>
            <div v-if="log.old_values && Object.keys(log.old_values).length" class="detail-block">
              <div class="detail-label">Sebelum (Old Values)</div>
              <pre class="detail-json">{{ JSON.stringify(log.old_values, null, 2) }}</pre>
            </div>
            <div v-if="log.new_values && Object.keys(log.new_values).length" class="detail-block">
              <div class="detail-label">Sesudah (New Values)</div>
              <pre class="detail-json">{{ JSON.stringify(log.new_values, null, 2) }}</pre>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="pagination">
        <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="btn-page">← Prev</button>
        <span class="page-info">
          Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}
          <span class="total-info">({{ pagination.total }} total log)</span>
        </span>
        <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="btn-page">Next →</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { superAdminService } from '../services/superAdminService'

const logs = ref([])
const loading = ref(false)
const error = ref(null)
const anomalyToday = ref(0)
const expanded = ref(new Set())

const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 15,
})

const filters = ref({
  search: '',
  module: '',
  action: '',
  status: '',
  anomaly_only: false,
})

const hasActiveFilters = computed(() =>
  filters.value.search || filters.value.module || filters.value.action ||
  filters.value.status || filters.value.anomaly_only
)

// Debounce untuk search
let searchTimer = null
const debouncedFetch = () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => applyFilters(), 400)
}

const applyFilters = () => {
  pagination.value.current_page = 1
  fetchLogs()
}

const clearFilters = () => {
  filters.value = { search: '', module: '', action: '', status: '', anomaly_only: false }
  applyFilters()
}

const toggleAnomalyOnly = () => {
  filters.value.anomaly_only = !filters.value.anomaly_only
  applyFilters()
}

const fetchLogs = async () => {
  loading.value = true
  error.value = null
  try {
    const res = await superAdminService.getAuditLogs(
      pagination.value.per_page,
      pagination.value.current_page,
      filters.value
    )
    const data = res.data
    logs.value = data?.data ?? []
    anomalyToday.value = data?.anomaly_today ?? 0
    pagination.value = {
      current_page: data?.pagination?.current_page ?? 1,
      last_page: data?.pagination?.last_page ?? 1,
      total: data?.pagination?.total ?? 0,
      per_page: data?.pagination?.per_page ?? 15,
    }
  } catch (err) {
    error.value = 'Gagal memuat audit logs'
    console.error(err)
  } finally {
    loading.value = false
  }
}

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    pagination.value.current_page = page
    fetchLogs()
  }
}

const toggleExpand = (id) => {
  const set = new Set(expanded.value)
  set.has(id) ? set.delete(id) : set.add(id)
  expanded.value = set
}

const actionLabel = (action) => {
  const map = {
    create: 'CREATE',
    update: 'UPDATE',
    delete: 'DELETE',
    reset_password: 'RESET PWD',
    export: 'EXPORT',
    download: 'DOWNLOAD',
    read: 'READ',
  }
  return map[action] || action?.toUpperCase()
}

const formatTime = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleString('id-ID', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit', second: '2-digit'
  })
}

onMounted(() => fetchLogs())
</script>

<style scoped>
.page-container { padding: 1.5rem; max-width: 1400px; margin: 0 auto; }

.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; }
.page-title { font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 0 0 0.25rem; }
.page-subtitle { font-size: 0.875rem; color: #64748b; margin: 0; }

.btn-refresh {
  padding: 0.5rem 1.25rem; background: #f1f5f9; border: 1px solid #e2e8f0;
  border-radius: 0.5rem; cursor: pointer; font-weight: 600; color: #475569;
  transition: background 0.2s;
}
.btn-refresh:hover { background: #e2e8f0; }
.btn-refresh.spinning { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Anomaly Banner */
.anomaly-banner {
  display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;
  background: #fef3c7; border: 1px solid #fcd34d; border-radius: 0.75rem;
  padding: 0.75rem 1rem; margin-bottom: 1rem; font-size: 0.875rem; color: #92400e;
}
.anomaly-icon { font-size: 1rem; }
.btn-anomaly-filter {
  margin-left: auto; padding: 0.35rem 0.9rem; background: white;
  border: 1px solid #f59e0b; border-radius: 9999px; cursor: pointer;
  font-weight: 600; color: #92400e; font-size: 0.8rem;
}
.btn-anomaly-filter.active { background: #f59e0b; color: white; }

/* Filter Bar */
.filter-bar { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem; }
.filter-input {
  padding: 0.5rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.5rem;
  font-size: 0.875rem; background: white; outline: none; color: #334155;
}
.filter-input:focus { border-color: #3b82f6; }
.filter-search { flex: 1; min-width: 200px; }
.btn-clear-filter {
  padding: 0.5rem 0.9rem; background: #fee2e2; border: 1px solid #fca5a5;
  border-radius: 0.5rem; cursor: pointer; color: #dc2626; font-weight: 600; font-size: 0.875rem;
}

/* Loading / Error */
.loading-container { text-align: center; padding: 3rem; background: white; border-radius: 1rem; }
.loading-spinner { width: 36px; height: 36px; border: 3px solid #f1f5f9; border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1rem; }
.error-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 0.75rem; padding: 1.5rem; color: #dc2626; text-align: center; }
.btn-retry { margin-top: 0.75rem; padding: 0.5rem 1.25rem; background: #3b82f6; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 600; }
.empty-state { text-align: center; padding: 3rem; color: #94a3b8; background: white; border-radius: 1rem; border: 1px solid #f1f5f9; }

/* Log List */
.log-list { display: flex; flex-direction: column; gap: 0.5rem; }

.log-item {
  background: white; border-radius: 0.75rem;
  border: 1px solid #e2e8f0;
  overflow: hidden;
  transition: box-shadow 0.15s;
}
.log-item:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.07); }
.log-item.log-anomaly { border-left: 4px solid #f59e0b; }
.log-item.log-failed { border-left: 4px solid #ef4444; }

.log-main {
  display: grid;
  grid-template-columns: 160px 200px 160px 1fr auto auto;
  gap: 1rem;
  align-items: center;
  padding: 0.875rem 1rem;
}

.log-time .time-full { font-size: 0.8rem; font-weight: 600; color: #64748b; white-space: nowrap; }

.log-user { display: flex; flex-direction: column; gap: 0.2rem; min-width: 0; }
.user-name { font-weight: 700; color: #1e293b; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-meta { display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap; }
.user-email { font-size: 0.72rem; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px; }
.user-role-badge {
  font-size: 0.65rem; font-weight: 700; padding: 0.1rem 0.4rem;
  border-radius: 9999px; white-space: nowrap;
}
.role-super_admin { background: #ede9fe; color: #6d28d9; }
.role-admin { background: #dbeafe; color: #1d4ed8; }
.role-dokter { background: #dcfce7; color: #166534; }
.role-terapis { background: #fef3c7; color: #92400e; }

.log-action-group { display: flex; flex-direction: column; gap: 0.3rem; align-items: flex-start; }

.module-badge {
  font-size: 0.7rem; font-weight: 700; padding: 0.15rem 0.5rem;
  border-radius: 0.25rem; background: #f1f5f9; color: #64748b;
  text-transform: uppercase; letter-spacing: 0.05em;
}

.action-badge {
  font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.6rem;
  border-radius: 0.375rem; white-space: nowrap;
}
.action-create   { background: #dcfce7; color: #166534; }
.action-update   { background: #e0e7ff; color: #3730a3; }
.action-delete   { background: #fee2e2; color: #991b1b; }
.action-reset_password { background: #fef3c7; color: #92400e; }
.action-export   { background: #f0fdf4; color: #15803d; border: 1px solid #86efac; }
.action-download { background: #eff6ff; color: #1d4ed8; }
.action-read     { background: #f8fafc; color: #64748b; }

.log-desc {
  font-size: 0.875rem; color: #334155; line-height: 1.4;
  overflow: hidden; text-overflow: ellipsis;
}
.anomaly-tag {
  display: inline-block; background: #fef3c7; color: #b45309;
  font-size: 0.7rem; font-weight: 700; padding: 0.1rem 0.4rem;
  border-radius: 0.25rem; margin-right: 0.4rem;
}

.log-right { display: flex; flex-direction: column; align-items: flex-end; gap: 0.25rem; }
.status-badge {
  font-size: 0.7rem; font-weight: 700; padding: 0.2rem 0.6rem;
  border-radius: 9999px; white-space: nowrap;
}
.status-success { background: #dcfce7; color: #15803d; }
.status-failed  { background: #fee2e2; color: #dc2626; }
.ip-address { font-size: 0.7rem; color: #94a3b8; font-family: monospace; }

.btn-expand {
  background: none; border: 1px solid #e2e8f0; border-radius: 0.375rem;
  padding: 0.25rem 0.5rem; cursor: pointer; color: #94a3b8; font-size: 0.7rem;
  white-space: nowrap;
}
.btn-expand:hover { background: #f1f5f9; color: #475569; }

/* Detail Expand */
.log-detail {
  border-top: 1px solid #f1f5f9; padding: 0.875rem 1rem;
  background: #f8fafc; display: flex; flex-direction: column; gap: 0.75rem;
}
.detail-block { display: flex; flex-direction: column; gap: 0.3rem; }
.detail-label { font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
.detail-json {
  background: white; border: 1px solid #e2e8f0; border-radius: 0.375rem;
  padding: 0.75rem; font-size: 0.8rem; color: #334155;
  overflow-x: auto; margin: 0; white-space: pre-wrap; word-break: break-all;
}
.detail-block.detail-error .detail-json,
.error-text { color: #dc2626; }

/* Pagination */
.pagination { display: flex; justify-content: center; align-items: center; gap: 1.5rem; padding: 1.5rem 0; }
.btn-page {
  padding: 0.5rem 1.25rem; background: white; border: 1px solid #e2e8f0;
  border-radius: 0.5rem; cursor: pointer; font-weight: 600; color: #475569; font-size: 0.875rem;
}
.btn-page:disabled { opacity: 0.45; cursor: not-allowed; }
.page-info { font-size: 0.875rem; color: #64748b; }
.total-info { color: #94a3b8; font-size: 0.8rem; margin-left: 0.25rem; }

@media (max-width: 900px) {
  .log-main {
    grid-template-columns: 1fr;
    gap: 0.5rem;
  }
  .log-right { align-items: flex-start; flex-direction: row; flex-wrap: wrap; gap: 0.5rem; }
}
</style>
