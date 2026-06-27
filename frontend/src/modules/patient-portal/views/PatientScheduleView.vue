<template>
  <div class="patient-page">
    <div class="page-header">
      <h1>📅 Jadwal Terapi</h1>
      <p>Daftar sesi terapi yang dijadwalkan untuk Anda.</p>
    </div>

    <div v-if="loading" class="loading-state">Memuat jadwal terapi...</div>

    <div v-else-if="error" class="error-state">
      <p>{{ error }}</p>
      <button @click="loadSchedule" class="btn-retry">Coba Lagi</button>
    </div>

    <div v-else>
      <!-- Upcoming Sessions -->
      <section class="schedule-section">
        <h2>Sesi Mendatang</h2>
        <div v-if="upcoming.length === 0" class="empty-state">
          <p>🗓️</p>
          <p>Tidak ada sesi terapi mendatang.</p>
        </div>
        <div v-else class="schedule-list">
          <div v-for="item in upcoming" :key="item.id" class="schedule-card upcoming">
            <div class="schedule-date">
              <span class="day">{{ formatDay(item.tanggal_sesi) }}</span>
              <span class="month">{{ formatMonth(item.tanggal_sesi) }}</span>
            </div>
            <div class="schedule-info">
              <h3>{{ item.jenis_terapi || 'Sesi Terapi' }}</h3>
              <p>🕐 {{ item.waktu_sesi || 'Waktu belum ditentukan' }}</p>
              <p>👤 {{ item.terapis?.name || 'Terapis belum ditugaskan' }}</p>
              <p>📍 {{ item.lokasi || 'Ruang Terapi' }}</p>
            </div>
            <span :class="['status-chip', `status-${item.status}`]">{{ statusLabel(item.status) }}</span>
          </div>
        </div>
      </section>

      <!-- Past Sessions -->
      <section v-if="past.length > 0" class="schedule-section">
        <h2>Riwayat Sesi</h2>
        <div class="schedule-list">
          <div v-for="item in past" :key="item.id" class="schedule-card past">
            <div class="schedule-date past-date">
              <span class="day">{{ formatDay(item.tanggal_sesi) }}</span>
              <span class="month">{{ formatMonth(item.tanggal_sesi) }}</span>
            </div>
            <div class="schedule-info">
              <h3>{{ item.jenis_terapi || 'Sesi Terapi' }}</h3>
              <p>🕐 {{ item.waktu_sesi || '-' }}</p>
              <p>👤 {{ item.terapis?.name || '-' }}</p>
            </div>
            <span :class="['status-chip', `status-${item.status}`]">{{ statusLabel(item.status) }}</span>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { patientService } from '../services/patientService'

const loading = ref(false)
const error = ref(null)
const schedules = ref([])

const today = new Date()
today.setHours(0, 0, 0, 0)

const upcoming = computed(() => schedules.value.filter(s => new Date(s.tanggal_sesi) >= today))
const past = computed(() => schedules.value.filter(s => new Date(s.tanggal_sesi) < today))

function formatDay(dateStr) {
  if (!dateStr) return '-'
  return new Date(dateStr).getDate()
}
function formatMonth(dateStr) {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', { month: 'short', year: 'numeric' })
}
function statusLabel(status) {
  const labels = { scheduled: 'Dijadwalkan', completed: 'Selesai', cancelled: 'Dibatalkan', present: 'Hadir', absent: 'Tidak Hadir' }
  return labels[status] || status
}

async function loadSchedule() {
  loading.value = true
  error.value = null
  try {
    const result = await patientService.getTherapySchedule()
    if (result.success) {
      schedules.value = result.data?.schedules || result.data || []
    } else {
      error.value = result.error || 'Gagal memuat jadwal.'
    }
  } catch (e) {
    error.value = 'Terjadi kesalahan saat memuat data.'
  } finally {
    loading.value = false
  }
}

onMounted(loadSchedule)
</script>

<style scoped>
.patient-page { padding: 2rem; max-width: 800px; }
.page-header { margin-bottom: 2rem; }
.page-header h1 { font-size: 1.5rem; font-weight: 700; color: #1e293b; }
.page-header p { color: #64748b; margin-top: 0.25rem; }
.loading-state { color: #94a3b8; padding: 2rem; text-align: center; }
.error-state { text-align: center; padding: 2rem; color: #ef4444; }
.btn-retry { margin-top: 1rem; padding: 0.5rem 1.5rem; background: #1a73e8; color: white; border: none; border-radius: 0.5rem; cursor: pointer; }
.empty-state { text-align: center; padding: 2rem; color: #94a3b8; font-size: 0.95rem; }
.empty-state p:first-child { font-size: 2.5rem; margin-bottom: 0.5rem; }
.schedule-section { margin-bottom: 2rem; }
.schedule-section h2 { font-size: 1rem; font-weight: 600; color: #374151; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #e5e7eb; }
.schedule-list { display: flex; flex-direction: column; gap: 0.75rem; }
.schedule-card { background: white; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08); display: flex; align-items: center; gap: 1.25rem; border-left: 4px solid #1a73e8; }
.schedule-card.past { border-left-color: #d1d5db; }
.schedule-date { display: flex; flex-direction: column; align-items: center; min-width: 50px; }
.day { font-size: 1.75rem; font-weight: 800; color: #1a73e8; line-height: 1; }
.month { font-size: 0.7rem; color: #94a3b8; text-align: center; }
.past-date .day { color: #9ca3af; }
.schedule-info { flex: 1; }
.schedule-info h3 { font-size: 0.95rem; font-weight: 600; color: #1e293b; margin-bottom: 0.3rem; }
.schedule-info p { font-size: 0.82rem; color: #64748b; margin: 0.1rem 0; }
.status-chip { font-size: 0.72rem; padding: 0.25rem 0.7rem; border-radius: 9999px; font-weight: 600; white-space: nowrap; }
.status-scheduled { background: #dbeafe; color: #1e40af; }
.status-completed, .status-present { background: #dcfce7; color: #166534; }
.status-cancelled, .status-absent { background: #fee2e2; color: #dc2626; }
</style>
