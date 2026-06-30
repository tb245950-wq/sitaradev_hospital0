<template>
  <div class="audit-logs">
    <div class="page-header">
      <h1>Log Aktivitas</h1>
      <button @click="fetchLogs" class="btn-secondary">Refresh</button>
    </div>

    <div v-if="loading" class="loading">Memuat...</div>
    <div v-if="error" class="error">{{ error }}</div>

    <table v-if="!loading && logs.length" class="logs-table">
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
        <tr v-for="log in logs" :key="log.id">
          <td class="time">{{ formatTime(log.created_at) }}</td>
          <td>{{ log.user?.name || 'System' }}</td>
          <td>{{ log.module }}</td>
          <td><span class="badge" :class="`action-${log.action}`">{{ log.action }}</span></td>
          <td>{{ log.description }}</td>
          <td><span class="badge" :class="`status-${log.status}`">{{ log.status }}</span></td>
        </tr>
      </tbody>
    </table>

    <div v-if="!loading && !logs.length" class="empty">Tidak ada aktivitas</div>

    <!-- Pagination -->
    <div v-if="pagination.total > 0" class="pagination">
      <button @click="prevPage" :disabled="pagination.current_page === 1">← Sebelumnya</button>
      <span>{{ pagination.current_page }} / {{ Math.ceil(pagination.total / 10) }}</span>
      <button @click="nextPage" :disabled="pagination.current_page * 10 >= pagination.total">Selanjutnya →</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { superAdminService } from '../services/superAdminService'

const logs = ref([])
const loading = ref(false)
const error = ref(null)
const currentPage = ref(1)
const pagination = ref({ total: 0, current_page: 1, per_page: 10 })

onMounted(() => {
  fetchLogs()
})

const fetchLogs = async () => {
  try {
    loading.value = true
    const res = await superAdminService.getAuditLogs(10, currentPage.value)
    logs.value = res.data || []
    pagination.value = res.pagination || { total: 0, current_page: 1, per_page: 10 }
  } catch (err) {
    error.value = 'Gagal memuat audit logs'
  } finally {
    loading.value = false
  }
}

const formatTime = (dateStr) => {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return date.toLocaleString('id-ID', { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' })
}

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
    fetchLogs()
  }
}

const nextPage = () => {
  if (currentPage.value * 10 < pagination.value.total) {
    currentPage.value++
    fetchLogs()
  }
}
</script>

<style scoped>
.audit-logs { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.loading { text-align: center; padding: 2rem; }
.error { background: #fee2e2; color: #dc2626; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; }
.logs-table { width: 100%; border-collapse: collapse; background: white; border-radius: 0.5rem; overflow: hidden; }
.logs-table th { background: #f8fafc; padding: 1rem; text-align: left; font-weight: 600; font-size: 0.9rem; }
.logs-table td { padding: 0.75rem; border-bottom: 1px solid #e2e8f0; font-size: 0.9rem; }
.time { color: #64748b; min-width: 150px; }
.badge { display: inline-block; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; }
.action-create { background: #dcfce7; color: #166534; }
.action-update { background: #e0e7ff; color: #3730a3; }
.action-delete { background: #fee2e2; color: #991b1b; }
.action-reset_password { background: #fef3c7; color: #92400e; }
.status-success { background: #dcfce7; color: #166534; }
.status-failed { background: #fee2e2; color: #991b1b; }
.pagination { display: flex; justify-content: center; align-items: center; gap: 1rem; margin-top: 1.5rem; }
.pagination button { padding: 0.5rem 1rem; border: 1px solid #e2e8f0; border-radius: 0.25rem; cursor: pointer; }
.pagination button:disabled { opacity: 0.5; cursor: not-allowed; }
.empty { text-align: center; padding: 2rem; color: #94a3b8; }
.btn-secondary { background: #e2e8f0; padding: 0.75rem 1.5rem; border: none; border-radius: 0.25rem; cursor: pointer; }
</style>
