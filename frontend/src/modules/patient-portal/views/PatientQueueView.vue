<template>
  <div class="patient-page">
    <div class="page-header">
      <h1>🎫 Antrian Saya</h1>
      <p>Status antrian dan nomor antrean Anda hari ini.</p>
    </div>

    <div v-if="loading" class="loading-state">Memuat data antrian...</div>

    <div v-else-if="error" class="error-state">
      <p>{{ error }}</p>
      <button @click="loadQueue" class="btn-retry">Coba Lagi</button>
    </div>

    <div v-else>
      <!-- Active Queue -->
      <div v-if="activeQueue" class="active-queue-card">
        <div class="queue-number">{{ activeQueue.nomor_antrian }}</div>
        <p class="queue-label">Nomor Antrian Anda</p>
        <div :class="['status-chip', `status-${activeQueue.status}`]">
          {{ statusLabel(activeQueue.status) }}
        </div>
        <div class="queue-info">
          <div class="info-item">
            <span class="info-label">Poli</span>
            <span class="info-value">{{ activeQueue.poli || '-' }}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Dokter</span>
            <span class="info-value">{{ activeQueue.dokter?.name || '-' }}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Antrian Saat Ini</span>
            <span class="info-value">{{ activeQueue.current_number ?? '-' }}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Tanggal</span>
            <span class="info-value">{{ formatDate(activeQueue.tanggal) }}</span>
          </div>
        </div>
      </div>

      <div v-else class="no-queue-card">
        <p class="no-queue-icon">🎫</p>
        <h3>Tidak Ada Antrian Aktif</h3>
        <p>Anda belum memiliki antrian hari ini.</p>
        <router-link to="/pasien/booking" class="btn-booking">Buat Antrian Baru</router-link>
      </div>

      <!-- Queue History -->
      <section v-if="queueHistory.length > 0" class="history-section">
        <h2>Riwayat Antrian</h2>
        <div class="history-list">
          <div v-for="item in queueHistory" :key="item.id" class="history-card">
            <div class="history-left">
              <span class="history-number">{{ item.nomor_antrian }}</span>
              <div>
                <p class="history-poli">{{ item.poli || '-' }}</p>
                <p class="history-date">{{ formatDate(item.tanggal) }}</p>
              </div>
            </div>
            <span :class="['status-chip', `status-${item.status}`]">{{ statusLabel(item.status) }}</span>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { patientService } from '../services/patientService'

const loading = ref(false)
const error = ref(null)
const activeQueue = ref(null)
const queueHistory = ref([])

function formatDate(dateStr) {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
}

function statusLabel(status) {
  const labels = { waiting: 'Menunggu', called: 'Dipanggil', serving: 'Dilayani', done: 'Selesai', cancelled: 'Dibatalkan', menunggu: 'Menunggu', selesai: 'Selesai' }
  return labels[status] || status
}

async function loadQueue() {
  loading.value = true
  error.value = null
  try {
    const result = await patientService.getMyQueue()
    if (result.success) {
      activeQueue.value = result.data?.active_queue || null
      queueHistory.value = result.data?.history || []
    } else {
      error.value = result.error || 'Gagal memuat antrian.'
    }
  } catch (e) {
    error.value = 'Terjadi kesalahan saat memuat data.'
  } finally {
    loading.value = false
  }
}

onMounted(loadQueue)
</script>

<style scoped>
.patient-page { padding: 2rem; max-width: 700px; }
.page-header { margin-bottom: 2rem; }
.page-header h1 { font-size: 1.5rem; font-weight: 700; color: #1e293b; }
.page-header p { color: #64748b; margin-top: 0.25rem; }
.loading-state, .empty-state { color: #94a3b8; text-align: center; padding: 2rem; }
.error-state { text-align: center; padding: 2rem; color: #ef4444; }
.btn-retry { margin-top: 1rem; padding: 0.5rem 1.5rem; background: #1a73e8; color: white; border: none; border-radius: 0.5rem; cursor: pointer; }
.active-queue-card { background: linear-gradient(135deg, #1a73e8 0%, #0f4c81 100%); color: white; border-radius: 1.25rem; padding: 2rem; text-align: center; margin-bottom: 2rem; }
.queue-number { font-size: 5rem; font-weight: 900; line-height: 1; }
.queue-label { font-size: 1rem; opacity: 0.85; margin-bottom: 1rem; }
.queue-info { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-top: 1.5rem; background: rgba(255,255,255,0.1); border-radius: 0.75rem; padding: 1rem; }
.info-item { display: flex; flex-direction: column; gap: 0.2rem; }
.info-label { font-size: 0.7rem; opacity: 0.7; text-transform: uppercase; }
.info-value { font-size: 0.95rem; font-weight: 600; }
.no-queue-card { background: white; border-radius: 1rem; padding: 2.5rem; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.08); margin-bottom: 2rem; }
.no-queue-icon { font-size: 3rem; margin-bottom: 0.75rem; }
.no-queue-card h3 { font-size: 1.2rem; font-weight: 600; color: #1e293b; margin-bottom: 0.5rem; }
.no-queue-card p { color: #64748b; margin-bottom: 1.25rem; }
.btn-booking { display: inline-block; background: #1a73e8; color: white; padding: 0.6rem 1.5rem; border-radius: 0.5rem; text-decoration: none; font-weight: 600; }
.status-chip { display: inline-block; font-size: 0.75rem; padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 600; }
.status-waiting, .status-menunggu { background: #fef3c7; color: #92400e; }
.status-called, .status-serving { background: #dbeafe; color: #1e40af; }
.status-done, .status-selesai { background: #dcfce7; color: #166534; }
.status-cancelled { background: #fee2e2; color: #dc2626; }
.history-section { margin-top: 1rem; }
.history-section h2 { font-size: 1rem; font-weight: 600; color: #374151; margin-bottom: 1rem; }
.history-list { display: flex; flex-direction: column; gap: 0.75rem; }
.history-card { background: white; border-radius: 0.75rem; padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); display: flex; align-items: center; justify-content: space-between; }
.history-left { display: flex; align-items: center; gap: 1rem; }
.history-number { font-size: 1.5rem; font-weight: 800; color: #1a73e8; min-width: 3rem; }
.history-poli { font-weight: 600; color: #1e293b; font-size: 0.9rem; }
.history-date { font-size: 0.8rem; color: #94a3b8; }
</style>
