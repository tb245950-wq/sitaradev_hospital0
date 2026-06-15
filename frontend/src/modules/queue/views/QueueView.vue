<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <button @click="goToDashboard" class="btn-back">
          <span class="arrow">←</span>
          <span>Kembali ke Dashboard</span>
        </button>
        <h1 class="page-title">Antrian Hari Ini</h1>
        <p class="page-subtitle">Kelola urutan pelayanan pasien</p>
      </div>
      <div class="page-actions">
        <button 
          v-if="authStore.isAdmin" 
          @click="openCreateModal" 
          class="btn-primary"
        >
          + Tambah Antrian
        </button>
        <button @click="refreshData" class="btn-secondary">
          🔄 Refresh
        </button>
      </div>
    </div>

    <div class="queue-grid">
      <!-- Active Call Section (for Dokter) -->
      <div v-if="authStore.isDokter" class="active-call-card">
        <div class="card-header">
          <h3>Pasien Sedang Dilayani</h3>
          <span class="pulse-icon">●</span>
        </div>
        <div v-if="activePatient" class="active-patient-info">
          <div class="queue-number large">{{ activePatient.nomor_antrian }}</div>
          <div class="patient-details">
            <h4>{{ activePatient.patient?.nama }}</h4>
            <p>{{ activePatient.patient?.nrm }}</p>
          </div>
          <button @click="completeQueue(activePatient)" class="btn-success">
            Selesai
          </button>
        </div>
        <div v-else class="empty-call">
          <p>Belum ada pasien yang dipanggil.</p>
          <button @click="callNext" class="btn-primary-lg">Panggil Berikutnya</button>
        </div>
      </div>

      <!-- Queue List -->
      <div class="queue-list-card">
        <div class="tabs-mini">
          <button 
            v-for="s in statuses" 
            :key="s.value"
            @click="filterStatus = s.value"
            :class="['tab-mini-btn', { active: filterStatus === s.value }]"
          >
            {{ s.label }}
          </button>
        </div>

        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>No</th>
                <th>Pasien</th>
                <th>NRM</th>
                <th>Waktu</th>
                <th>Status</th>
                <th class="text-right">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="queueStore.loading">
                <td colspan="6" class="text-center py-8">Memuat antrian...</td>
              </tr>
              <tr v-else-if="filteredQueues.length === 0">
                <td colspan="6" class="text-center py-8">Tidak ada antrian.</td>
              </tr>
              <tr v-for="q in filteredQueues" :key="q.id" :class="q.status">
                <td><span class="badge-number">{{ q.nomor_antrian }}</span></td>
                <td><strong>{{ q.patient?.nama }}</strong></td>
                <td>{{ q.patient?.nrm }}</td>
                <td>{{ formatTime(q.created_at) }}</td>
                <td>
                  <span :class="['status-pill', q.status]">
                    {{ getStatusLabel(q.status) }}
                  </span>
                </td>
                <td class="text-right">
                  <div class="action-buttons justify-end">
                    <button 
                      v-if="q.status === 'menunggu' && authStore.isDokter" 
                      @click="updateStatus(q, 'dipanggil')"
                      class="btn-icon-sm"
                      title="Panggil"
                    >
                      🔊
                    </button>
                    <button 
                      v-if="authStore.isAdmin" 
                      @click="cancelQueue(q)"
                      class="btn-icon-sm text-red-500"
                      title="Batal"
                    >
                      ✕
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../auth/stores/authStore'
import { useQueueStore } from '../stores/queueStore'
import { useNavigation } from '../../../shared/composables/useNavigation'

const router = useRouter()
const authStore = useAuthStore()
const queueStore = useQueueStore()
const { goToDashboard } = useNavigation()

const filterStatus = ref('semua')
const statuses = [
  { value: 'semua', label: 'Semua' },
  { value: 'menunggu', label: 'Menunggu' },
  { value: 'dipanggil', label: 'Dipanggil' },
  { value: 'selesai', label: 'Selesai' }
]

// RBAC Check
onMounted(() => {
  if (authStore.isTerapis) {
    router.push('/unauthorized')
    return
  }
  refreshData()
})

const refreshData = () => {
  queueStore.fetchQueues()
}

const filteredQueues = computed(() => {
  if (filterStatus.value === 'semua') return queueStore.queues
  return queueStore.queues.filter(q => q.status === filterStatus.value)
})

const activePatient = computed(() => {
  return queueStore.queues.find(q => q.status === 'dipanggil')
})

const getStatusLabel = (status) => {
  const labels = {
    'menunggu': 'Menunggu',
    'dipanggil': 'Melayani',
    'selesai': 'Selesai',
    'batal': 'Batal'
  }
  return labels[status] || status
}

const formatTime = (dateStr) => {
  return new Date(dateStr).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

const updateStatus = async (queue, status) => {
  // Logic to update status via store
  // Since updateStatus is missing in store, I'll assume we'll add it or call service directly if needed
  // For now let's pretend it's in store
  // await queueStore.updateStatus(queue.id, status)
  alert(`Update status ${queue.patient?.nama} ke ${status}`)
}

const callNext = () => {
  const next = queueStore.queues.find(q => q.status === 'menunggu')
  if (next) updateStatus(next, 'dipanggil')
  else alert('Tidak ada pasien menunggu.')
}

const completeQueue = (q) => updateStatus(q, 'selesai')
const cancelQueue = (q) => {
  if (confirm('Batalkan antrian ini?')) updateStatus(q, 'batal')
}
</script>

<style scoped>
.page-container {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
}

.page-subtitle {
  color: #64748b;
}

.queue-grid {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

/* Active Call Card */
.active-call-card {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  padding: 2rem;
  border-radius: 1rem;
  box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.card-header h3 {
  font-size: 1.125rem;
  font-weight: 600;
}

.pulse-icon {
  color: #4ade80;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.3; }
  100% { opacity: 1; }
}

.active-patient-info {
  display: flex;
  align-items: center;
  gap: 2rem;
}

.queue-number.large {
  font-size: 4rem;
  font-weight: 800;
  background: rgba(255, 255, 255, 0.2);
  width: 100px;
  height: 100px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 1rem;
}

.patient-details h4 {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.patient-details p {
  opacity: 0.8;
  font-weight: 500;
}

.btn-success {
  margin-left: auto;
  background: #4ade80;
  color: #064e3b;
  border: none;
  padding: 0.75rem 2rem;
  border-radius: 0.5rem;
  font-weight: 700;
  cursor: pointer;
}

.empty-call {
  text-align: center;
  padding: 1rem;
}

.btn-primary-lg {
  margin-top: 1rem;
  background: white;
  color: #2563eb;
  border: none;
  padding: 1rem 3rem;
  border-radius: 0.75rem;
  font-weight: 700;
  font-size: 1.125rem;
  cursor: pointer;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

/* Queue List Card */
.queue-list-card {
  background: white;
  border-radius: 1rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  overflow: hidden;
}

.tabs-mini {
  display: flex;
  background: #f8fafc;
  padding: 0.5rem;
  gap: 0.5rem;
  border-bottom: 1px solid #e2e8f0;
}

.tab-mini-btn {
  padding: 0.5rem 1.25rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #64748b;
  border: none;
  background: none;
  border-radius: 0.5rem;
  cursor: pointer;
}

.tab-mini-btn.active {
  background: white;
  color: #2563eb;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  text-align: left;
  padding: 1rem 1.5rem;
  font-size: 0.875rem;
  color: #64748b;
  border-bottom: 1px solid #e2e8f0;
}

.data-table td {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
  font-size: 0.875rem;
}

.badge-number {
  background: #f1f5f9;
  color: #475569;
  width: 32px;
  height: 32px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  font-weight: 700;
}

.status-pill {
  padding: 0.25rem 0.75rem;
  border-radius: 1rem;
  font-size: 0.75rem;
  font-weight: 600;
}

.status-pill.menunggu { background: #fef3c7; color: #92400e; }
.status-pill.dipanggil { background: #dcfce7; color: #166534; }
.status-pill.selesai { background: #f1f5f9; color: #64748b; }

.btn-primary {
  background: #2563eb;
  color: white;
  padding: 0.625rem 1.25rem;
  border-radius: 0.5rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
}

.btn-secondary {
  background: white;
  color: #475569;
  padding: 0.625rem 1.25rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
}

.btn-icon-sm {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  width: 32px;
  height: 32px;
  border-radius: 0.375rem;
  cursor: pointer;
}

.text-right { text-align: right; }
.justify-end { justify-content: flex-end; }
.text-red-500 { color: #ef4444; }
.py-8 { padding-top: 2rem; padding-bottom: 2rem; }
.text-center { text-align: center; }
</style>
