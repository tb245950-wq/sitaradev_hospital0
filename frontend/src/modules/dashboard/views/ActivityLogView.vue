<template>
  <div class="page-container">
    <div class="page-header">
      <button @click="goBack" class="btn-back">← Kembali</button>
      <h1 class="page-title">Semua Aktivitas</h1>
    </div>

    <div v-if="loading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Memuat aktivitas...</p>
    </div>

    <div v-else class="activity-list">
      <div v-for="activity in activities" :key="activity.id" class="activity-item">
        <div class="activity-time">{{ formatTime(activity.created_at) }}</div>
        <div class="activity-content">
          <div class="activity-patient">{{ activity.patient?.nama_lengkap || 'System' }}</div>
          <div class="activity-desc">{{ activity.activity_type }}</div>
        </div>
        <div class="activity-staff">{{ activity.user?.name || 'Unknown' }}</div>
        <div class="activity-status">
          <span :class="['status-badge', getStatusClass(activity.status)]">{{ activity.status }}</span>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="pagination">
        <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="btn-pagination">← Prev</button>
        <span class="pagination-info">Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}</span>
        <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="btn-pagination">Next →</button>
      </div>
      
      <div v-if="activities.length === 0" class="empty-state">
        Tidak ada aktivitas tercatat.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { activityLogService } from '../services/activityLogService'

const router = useRouter()
const activities = ref([])
const loading = ref(false)
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0
})

const goBack = () => router.push('/dashboard')

const fetchActivities = async (page = 1) => {
  loading.value = true
  try {
    const response = await activityLogService.getAllActivities(page, 20)
    // Laravel Resource Collection structure: response.data.data and response.data.meta
    // Or direct pagination object in response.data
    const data = response.data
    activities.value = data.data || []
    
    if (data.current_page) {
      pagination.value = {
        current_page: data.current_page,
        last_page: data.last_page,
        per_page: data.per_page,
        total: data.total
      }
    }
  } catch (error) {
    console.error('Error fetching activities:', error)
  } finally {
    loading.value = false
  }
}

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchActivities(page)
  }
}

const formatTime = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleString('id-ID', { 
    day: '2-digit',
    month: 'short',
    hour: '2-digit', 
    minute: '2-digit' 
  })
}

const getStatusClass = (status) => {
  if (!status) return ''
  const s = status.toLowerCase()
  if (s === 'selesai' || s === 'active') return 'status-selesai'
  if (s === 'berlangsung' || s === 'calling') return 'status-berlangsung'
  if (s === 'baru' || s === 'waiting') return 'status-baru'
  return ''
}

onMounted(() => {
  fetchActivities()
})
</script>

<style scoped>
.page-container { padding: 2rem; max-width: 1200px; margin: 0 auto; }
.page-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem; }
.btn-back { padding: 0.5rem 1rem; background: transparent; border: 1px solid #e2e8f0; border-radius: 0.5rem; cursor: pointer; color: #64748b; font-weight: 500; }
.page-title { flex: 1; font-size: 1.75rem; font-weight: 700; color: #1e293b; }
.loading-container { text-align: center; padding: 4rem; background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.loading-spinner { width: 40px; height: 40px; border: 3px solid #f1f5f9; border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { to { transform: rotate(360deg); } }
.activity-list { display: flex; flex-direction: column; gap: 0.75rem; }
.activity-item { background: white; padding: 1.25rem; border-radius: 0.75rem; display: grid; grid-template-columns: 120px 2fr 1fr auto; gap: 1.5rem; align-items: center; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border: 1px solid #f1f5f9; }
.activity-time { font-size: 0.875rem; font-weight: 600; color: #64748b; }
.activity-patient { font-weight: 700; color: #1e293b; margin-bottom: 0.125rem; }
.activity-desc { font-size: 0.875rem; color: #64748b; }
.activity-staff { font-size: 0.875rem; color: #475569; font-weight: 500; }
.status-badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
.status-selesai { background: #dcfce7; color: #15803d; }
.status-berlangsung { background: #fef3c7; color: #b45309; }
.status-baru { background: #dbeafe; color: #1d4ed8; }
.pagination { display: flex; justify-content: center; align-items: center; gap: 1.5rem; padding: 2rem 0; }
.btn-pagination { padding: 0.5rem 1rem; background: white; border: 1px solid #e2e8f0; border-radius: 0.5rem; cursor: pointer; font-size: 0.875rem; font-weight: 600; color: #475569; }
.btn-pagination:disabled { opacity: 0.5; cursor: not-allowed; }
.pagination-info { font-size: 0.875rem; color: #64748b; }
.empty-state { text-align: center; padding: 4rem; color: #94a3b8; font-style: italic; background: white; border-radius: 1rem; }
</style>
