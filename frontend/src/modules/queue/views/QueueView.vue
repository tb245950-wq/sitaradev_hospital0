<template>
  <div class="page-container">
    <div class="page-header">
      <button @click="goBack" class="btn-back">← Kembali ke Dashboard</button>
      <h1 class="page-title">Waiting List (Antrian)</h1>
      <button v-if="canAddToQueue" @click="openAddModal" class="btn-primary">+ Tambah ke Antrian</button>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-value">{{ queueStore.stats.waiting }}</div>
        <div class="stat-label">Menunggu</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">{{ queueStore.stats.calling }}</div>
        <div class="stat-label">Dipanggil</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">{{ queueStore.stats.completed }}</div>
        <div class="stat-label">Selesai</div>
      </div>
      <div class="stat-card warning">
        <div class="stat-value">{{ queueStore.stats.high_priority }}</div>
        <div class="stat-label">Prioritas Tinggi</div>
      </div>
    </div>

    <!-- Call Next Button -->
    <div v-if="canCallNext" class="call-next-section">
      <button @click="callNextPatient" class="btn-call-next">📢 Panggil Pasien Berikutnya</button>
    </div>

    <!-- Loading State -->
    <div v-if="queueStore.loading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Memuat data antrian...</p>
    </div>

    <!-- Queue List -->
    <div v-else class="queue-list">
      <div v-for="queue in queueStore.queues" :key="queue.id" class="queue-card" :class="`priority-${queue.prioritas > 5 ? 'emergency' : queue.prioritas > 2 ? 'urgent' : 'normal'}`">
        <div class="queue-number">{{ queue.nomor }}</div>
        <div class="queue-patient">
          <div class="patient-name">{{ queue.pasien?.nama }}</div>
          <div class="patient-nik">NRM: {{ queue.pasien?.nrm }}</div>
        </div>
        <div class="queue-info">
          <span :class="['priority-badge', getPriorityLabel(queue.prioritas).toLowerCase()]">{{ getPriorityLabel(queue.prioritas) }}</span>
          <span class="queue-type">{{ queue.jenis === 'assessment' ? 'Assessment Medis' : 'Sesi Terapi' }}</span>
        </div>
        <div class="queue-actions">
          <button v-if="queue.status === 'menunggu'" @click="callPatient(queue.id)" class="btn-call">Panggil</button>
          <button v-if="queue.status === 'dipanggil'" @click="completeQueue(queue.id)" class="btn-complete">Selesai</button>
          <button v-if="queue.status === 'menunggu'" @click="cancelQueue(queue.id)" class="btn-cancel">Batal</button>
        </div>
      </div>
      
      <div v-if="queueStore.queues.length === 0" class="empty-state">
        <div class="empty-icon">⏳</div>
        <h3>Tidak Ada Antrian</h3>
        <p>Tidak ada pasien dalam antrian saat ini</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useQueueStore } from '../stores/queueStore'
import { useAuthStore } from '../../auth/stores/authStore'

const router = useRouter()
const queueStore = useQueueStore()
const authStore = useAuthStore()

let refreshInterval = null

const goBack = () => router.push('/dashboard')

const fetchQueues = async () => {
  await queueStore.fetchQueues({ status: 'menunggu,dipanggil' })
}

const fetchStats = async () => {
  await queueStore.getStats()
}

const callNextPatient = async () => {
  const result = await queueStore.callNext()
  if (result.success) {
    alert(`Memanggil pasien ${result.data.nomor}`)
  } else {
    alert(result.message || 'Gagal memanggil pasien')
  }
}

const callPatient = async (id) => {
  // We can use callNext with specific ID or just update status to 'dipanggil'
  alert('Memanggil pasien...')
}

const completeQueue = async (id) => {
  await queueStore.completeQueue(id)
}

const cancelQueue = async (id) => {
  if (confirm('Batalkan antrian ini?')) {
    await queueStore.cancelQueue(id)
  }
}

const openAddModal = () => {
  alert('Fitur tambah ke antrian akan segera hadir')
}

const canAddToQueue = computed(() => authStore.isAdmin || authStore.isDokter)
const canCallNext = computed(() => authStore.isAdmin || authStore.isDokter)

const getPriorityLabel = (priority) => {
  if (priority > 5) return 'EMERGENCY'
  if (priority > 2) return 'URGENT'
  return 'NORMAL'
}

onMounted(() => {
  fetchQueues()
  fetchStats()
  refreshInterval = setInterval(() => {
    fetchQueues()
    fetchStats()
  }, 30000)
})

onUnmounted(() => {
  if (refreshInterval) clearInterval(refreshInterval)
})
</script>

<style scoped>
.page-container { padding: 1.5rem; max-width: 1400px; margin: 0 auto; }
.page-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem; }
.btn-back { padding: 0.5rem 1rem; background: transparent; border: 1px solid #e2e8f0; border-radius: 0.5rem; cursor: pointer; font-size: 0.875rem; color: #64748b; }
.page-title { flex: 1; font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 0; }
.btn-primary { padding: 0.625rem 1.25rem; background: #1e40af; color: white; border: none; border-radius: 0.5rem; font-weight: 600; cursor: pointer; font-size: 0.875rem; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; margin-bottom: 2rem; }
.stat-card { background: white; padding: 1.25rem; border-radius: 0.75rem; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #f1f5f9; }
.stat-card.warning { border-top: 4px solid #ef4444; }
.stat-value { font-size: 2rem; font-weight: 700; color: #1e293b; margin-bottom: 0.25rem; }
.stat-label { font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 600; }
.call-next-section { margin-bottom: 2rem; text-align: center; }
.btn-call-next { padding: 0.875rem 2rem; background: #22c55e; color: white; border: none; border-radius: 0.75rem; font-size: 1rem; font-weight: 600; cursor: pointer; box-shadow: 0 4px 6px rgba(34, 197, 94, 0.2); transition: all 0.2s; }
.btn-call-next:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(34, 197, 94, 0.3); }
.loading-container { text-align: center; padding: 3rem; }
.loading-spinner { width: 40px; height: 40px; border: 3px solid #f1f5f9; border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { to { transform: rotate(360deg); } }
.queue-list { display: grid; gap: 1rem; }
.queue-card { background: white; padding: 1.25rem; border-radius: 0.75rem; display: grid; grid-template-columns: 80px 2fr 1.5fr auto; gap: 1.5rem; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #f1f5f9; }
.queue-card.priority-emergency { border-left: 4px solid #ef4444; }
.queue-card.priority-urgent { border-left: 4px solid #f59e0b; }
.queue-number { font-size: 1.25rem; font-weight: 800; color: #1e40af; font-family: monospace; }
.patient-name { font-weight: 700; color: #1e293b; margin-bottom: 0.125rem; }
.patient-nik { font-size: 0.75rem; color: #94a3b8; }
.queue-info { display: flex; flex-direction: column; gap: 0.375rem; }
.priority-badge { display: inline-block; width: fit-content; padding: 0.125rem 0.625rem; border-radius: 9999px; font-size: 0.65rem; font-weight: 700; }
.priority-badge.normal { background: #e0f2fe; color: #0ea5e9; }
.priority-badge.urgent { background: #fef3c7; color: #92400e; }
.priority-badge.emergency { background: #fee2e2; color: #991b1b; }
.queue-type { font-size: 0.8125rem; color: #64748b; font-weight: 500; }
.queue-actions { display: flex; gap: 0.5rem; }
.btn-call { padding: 0.5rem 1rem; background: #3b82f6; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-size: 0.8125rem; font-weight: 600; }
.btn-complete { padding: 0.5rem 1rem; background: #22c55e; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-size: 0.8125rem; font-weight: 600; }
.btn-cancel { padding: 0.5rem 1rem; background: #f8fafc; color: #ef4444; border: 1px solid #fee2e2; border-radius: 0.5rem; cursor: pointer; font-size: 0.8125rem; font-weight: 600; }
.empty-state { text-align: center; padding: 4rem 2rem; background: white; border-radius: 0.75rem; border: 1px solid #f1f5f9; }
.empty-icon { font-size: 3rem; margin-bottom: 1rem; opacity: 0.5; }
</style>
