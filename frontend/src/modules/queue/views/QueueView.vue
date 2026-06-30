<template>
  <div class="page-container">
    <div class="page-header">
      <button @click="goBack" class="btn-back">← Kembali ke Dashboard</button>
      <h1 class="page-title">Waiting List (Antrian)</h1>
      <button v-if="canManageQueue" @click="openAddModal" class="btn-primary">+ Tambah Antrian</button>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-value">{{ queueStore.stats.waiting_count || 0 }}</div>
        <div class="stat-label">Menunggu</div>
        <div v-if="queueStore.stats.waiting?.length" class="stat-sublist">
          <div v-for="q in queueStore.stats.waiting.slice(0, 3)" :key="q.id" class="stat-subitem">
            <span class="stat-subname">{{ q.pasien?.nama }}</span>
            <span class="stat-subnum">{{ q.nomor }}</span>
          </div>
          <div v-if="queueStore.stats.waiting.length > 3" class="stat-submore">
            +{{ queueStore.stats.waiting.length - 3 }} lagi
          </div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-value">{{ queueStore.stats.calling_count || 0 }}</div>
        <div class="stat-label">Dipanggil</div>
        <div v-if="queueStore.stats.calling?.length" class="stat-sublist">
          <div v-for="q in queueStore.stats.calling" :key="q.id" class="stat-subitem">
            <span class="stat-subname">{{ q.pasien?.nama }}</span>
            <span class="stat-subnum">{{ q.nomor }}</span>
          </div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-value">{{ queueStore.stats.completed_count || 0 }}</div>
        <div class="stat-label">Selesai</div>
        <div v-if="queueStore.stats.completed?.length" class="stat-sublist">
          <div v-for="q in queueStore.stats.completed" :key="q.id" class="stat-subitem">
            <span class="stat-subname">{{ q.pasien?.nama }}</span>
            <span class="stat-subnum">{{ q.nomor }}</span>
          </div>
        </div>
      </div>
      <div class="stat-card warning">
        <div class="stat-value">{{ queueStore.stats.high_priority_count || 0 }}</div>
        <div class="stat-label">Prioritas Tinggi</div>
        <div v-if="queueStore.stats.high_priority?.length" class="stat-sublist">
          <div v-for="q in queueStore.stats.high_priority" :key="q.id" class="stat-subitem">
            <span class="stat-subname">{{ q.pasien?.nama }}</span>
            <span class="stat-subnum">{{ q.nomor }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Call Next Button -->
    <div v-if="canManageQueue" class="call-next-section">
      <button @click="callNextPatient" class="btn-call-next">Panggil Pasien Berikutnya</button>
    </div>

    <!-- Queue Status Grid -->
    <div v-if="queueStore.stats.waiting?.length || queueStore.stats.calling?.length || queueStore.stats.completed?.length" class="status-section">
      <h2 class="section-title">Status Antrian</h2>
      
      <!-- Waiting List -->
      <div v-if="queueStore.stats.waiting?.length" class="status-group">
        <h3 class="status-group-title">
          <span class="status-badge blue">Menunggu ({{ queueStore.stats.waiting_count }})</span>
        </h3>
        <div class="patient-grid">
          <div v-for="q in queueStore.stats.waiting" :key="q.id" class="patient-card">
            <div class="patient-number">{{ q.nomor }}</div>
            <div class="patient-name">{{ q.pasien?.nama }}</div>
            <div class="patient-meta">NRM: {{ q.pasien?.nrm }}</div>
            <div class="patient-priority" v-if="q.prioritas > 5">
              <span class="priority-label">Prioritas Tinggi</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Calling List -->
      <div v-if="queueStore.stats.calling?.length" class="status-group">
        <h3 class="status-group-title">
          <span class="status-badge yellow">Dipanggil ({{ queueStore.stats.calling_count }})</span>
        </h3>
        <div class="patient-grid">
          <div v-for="q in queueStore.stats.calling" :key="q.id" class="patient-card calling">
            <div class="patient-number">{{ q.nomor }}</div>
            <div class="patient-name">{{ q.pasien?.nama }}</div>
            <div class="patient-meta">NRM: {{ q.pasien?.nrm }}</div>
          </div>
        </div>
      </div>

      <!-- Completed List -->
      <div v-if="queueStore.stats.completed?.length" class="status-group">
        <h3 class="status-group-title">
          <span class="status-badge green">Selesai ({{ queueStore.stats.completed_count }})</span>
        </h3>
        <div class="patient-grid">
          <div v-for="q in queueStore.stats.completed" :key="q.id" class="patient-card completed">
            <div class="patient-number">{{ q.nomor }}</div>
            <div class="patient-name">{{ q.pasien?.nama }}</div>
            <div class="patient-meta">NRM: {{ q.pasien?.nrm }}</div>
            <div class="patient-time">Selesai: {{ formatTime(q.waktu_selesai) }}</div>
          </div>
        </div>
      </div>

      <!-- High Priority List -->
      <div v-if="queueStore.stats.high_priority?.length" class="status-group priority-group">
        <h3 class="status-group-title">
          <span class="status-badge red">Prioritas Tinggi ({{ queueStore.stats.high_priority_count }})</span>
        </h3>
        <div class="patient-grid">
          <div v-for="q in queueStore.stats.high_priority" :key="q.id" class="patient-card priority">
            <div class="patient-number">{{ q.nomor }}</div>
            <div class="patient-name">{{ q.pasien?.nama }}</div>
            <div class="patient-meta">NRM: {{ q.pasien?.nrm }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!queueStore.loading" class="empty-state">
      <h3>Tidak Ada Antrian</h3>
      <p>Tidak ada pasien dalam antrian hari ini</p>
      <button v-if="canManageQueue" @click="openAddModal" class="btn-primary">
        + Daftarkan Pasien
      </button>
    </div>

    <!-- Modal Tambah Antrian -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h2>Tambah Antrian</h2>
          <button @click="closeModal" class="modal-close">&times;</button>
        </div>

        <form @submit.prevent="submitAddQueue" class="modal-body">
          <!-- Cari Pasien -->
          <div class="form-group">
            <label>Cari Pasien <span class="required">*</span></label>
            <input
              v-model="patientSearch"
              @input="searchPatients"
              type="text"
              placeholder="Cari nama atau NRM pasien..."
              class="form-input"
              autocomplete="off"
            />
            <!-- Dropdown hasil pencarian -->
            <div v-if="patientResults.length > 0" class="search-dropdown">
              <div
                v-for="p in patientResults"
                :key="p.id"
                @click="selectPatient(p)"
                class="search-item"
              >
                <div class="search-item-name">{{ p.nama_lengkap }}</div>
                <div class="search-item-meta">NRM: {{ p.nrm }}</div>
              </div>
            </div>
            <div v-if="patientSearch.length >= 2 && patientResults.length === 0 && !searchLoading" class="search-empty">
              Pasien tidak ditemukan
            </div>
          </div>

          <!-- Pasien terpilih -->
          <div v-if="form.selectedPatient" class="selected-patient">
            <span>Pasien: <strong>{{ form.selectedPatient.nama_lengkap }}</strong> — NRM: {{ form.selectedPatient.nrm }}</span>
            <button type="button" @click="clearPatient" class="btn-clear">×</button>
          </div>

          <!-- Jenis Layanan -->
          <div class="form-group">
            <label>Jenis Layanan <span class="required">*</span></label>
            <select v-model="form.jenis_layanan" class="form-input" required>
              <option value="">Pilih jenis layanan</option>
              <option value="konsultasi">Konsultasi Umum</option>
              <option value="assessment">Assessment Medis</option>
              <option value="terapi">Sesi Terapi</option>
              <option value="kontrol">Kontrol</option>
            </select>
          </div>

          <!-- Prioritas -->
          <div class="form-group">
            <label>Prioritas</label>
            <div class="priority-grid">
              <label v-for="opt in priorityOptions" :key="opt.value" :class="['priority-opt', { active: form.prioritas === opt.value }]">
                <input type="radio" :value="opt.value" v-model="form.prioritas" />
                <span :class="['priority-dot', opt.color]"></span>
                {{ opt.label }}
              </label>
            </div>
          </div>

          <!-- Catatan -->
          <div class="form-group">
            <label>Catatan (Opsional)</label>
            <textarea v-model="form.catatan" rows="2" placeholder="Keluhan atau keterangan tambahan..." class="form-input"></textarea>
          </div>

          <div v-if="submitError" class="alert-error">{{ submitError }}</div>

          <div class="modal-footer">
            <button type="button" @click="closeModal" class="btn-secondary">Batal</button>
            <button type="submit" :disabled="!form.selectedPatient || !form.jenis_layanan || submitting" class="btn-primary">
              {{ submitting ? 'Menyimpan...' : 'Tambah ke Antrian' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useQueueStore } from '../stores/queueStore'
import { useAuthStore } from '../../auth/stores/authStore'
import { useNotificationStore } from '../../../shared/stores/notificationStore'
import api from '../../../core/services/api'

const router = useRouter()
const queueStore = useQueueStore()
const authStore = useAuthStore()
const notify = useNotificationStore()

let refreshInterval = null

// ── State Modal ──────────────────────────────────────────────
const showModal     = ref(false)
const patientSearch = ref('')
const patientResults= ref([])
const searchLoading = ref(false)
const submitting    = ref(false)
const submitError   = ref('')
let searchTimeout   = null

const form = ref({
  selectedPatient: null,
  jenis_layanan: '',
  prioritas: 0,
  catatan: ''
})

const priorityOptions = [
  { value: 0, label: 'Normal',    color: 'blue' },
  { value: 3, label: 'Urgent',    color: 'yellow' },
  { value: 6, label: 'Emergency', color: 'red' },
]

const jenisLabel = {
  konsultasi: 'Konsultasi Umum',
  assessment:  'Assessment Medis',
  terapi:      'Sesi Terapi',
  kontrol:     'Kontrol',
}

// ── Computed ────────────────────────────────────────────────
const canManageQueue = computed(() =>
  authStore.isAdmin || authStore.isDokter || authStore.isTerapis
)

// ── Modal ────────────────────────────────────────────────────
const openAddModal = () => {
  submitError.value = ''
  patientSearch.value = ''
  patientResults.value = []
  form.value = { selectedPatient: null, jenis_layanan: '', prioritas: 0, catatan: '' }
  showModal.value = true
}

const closeModal = () => { showModal.value = false }

// ── Cari Pasien ───────────────────────────────────────────────
const searchPatients = () => {
  clearTimeout(searchTimeout)
  if (patientSearch.value.length < 2) {
    patientResults.value = []
    return
  }
  searchTimeout = setTimeout(async () => {
    searchLoading.value = true
    try {
      const res = await api.get('/patients', { params: { search: patientSearch.value, per_page: 8 } })
      patientResults.value = res.data?.data || []
    } catch {
      patientResults.value = []
    } finally {
      searchLoading.value = false
    }
  }, 350)
}

const selectPatient = (p) => {
  form.value.selectedPatient = p
  patientSearch.value = ''
  patientResults.value = []
}

const clearPatient = () => { form.value.selectedPatient = null }

// ── Submit Tambah Antrian ─────────────────────────────────────
const submitAddQueue = async () => {
  if (!form.value.selectedPatient || !form.value.jenis_layanan) return
  submitting.value = true
  submitError.value = ''

  const result = await queueStore.addToQueue({
    id_pasien:     form.value.selectedPatient.id_pasien || form.value.selectedPatient.id,
    jenis_layanan: form.value.jenis_layanan,
    prioritas:     form.value.prioritas,
    catatan:       form.value.catatan || null,
  })

  submitting.value = false

  if (result.success) {
    notify.success(`Antrian berhasil ditambahkan`, 'Berhasil')
    closeModal()
  } else {
    submitError.value = result.message || 'Gagal menambah antrian'
  }
}

// ── Queue Actions ─────────────────────────────────────────────
const goBack = () => router.push('/dashboard')

const fetchQueues = () => queueStore.fetchQueues({ status: 'menunggu,dipanggil' })
const fetchStats  = () => queueStore.getStats()

const callNextPatient = async () => {
  const result = await queueStore.callNext()
  if (result.success) {
    notify.success(`Pasien ${result.data?.nomor_antrian || ''} dipanggil`, 'Panggil')
  } else {
    notify.error(result.message || 'Gagal memanggil pasien', 'Error')
  }
}

const callPatient = async (id) => {
  const result = await queueStore.callQueue(id)
  if (result.success) {
    notify.success('Pasien dipanggil', 'Panggil')
  } else {
    notify.error(result.message || 'Gagal memanggil pasien', 'Error')
  }
}

const completeQueue = async (id) => {
  if (!confirm('Selesaikan antrian ini?')) return
  const result = await queueStore.completeQueue(id)
  if (result.success) notify.success('Antrian selesai', 'Selesai')
}

const cancelQueue = async (id) => {
  if (!confirm('Batalkan antrian ini?')) return
  await queueStore.cancelQueue(id)
}

const getPriorityLabel = (p) => {
  if (p > 5) return 'EMERGENCY'
  if (p > 2) return 'URGENT'
  return 'NORMAL'
}

const formatTime = (time) => {
  if (!time) return '-'
  const date = new Date(time)
  return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

onMounted(() => {
  fetchQueues()
  fetchStats()
  refreshInterval = setInterval(() => { fetchQueues(); fetchStats() }, 30000)
})

onUnmounted(() => { if (refreshInterval) clearInterval(refreshInterval) })
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
.stat-sublist { margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.25rem; align-items: center; max-height: 80px; overflow-y: auto; }
.stat-subitem { display: flex; align-items: center; gap: 0.375rem; font-size: 0.75rem; color: #475569; background: #f8fafc; padding: 0.25rem 0.5rem; border-radius: 0.25rem; width: 100%; justify-content: space-between; }
.stat-subname { font-weight: 500; max-width: 80%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.stat-subnum { font-family: monospace; font-weight: 600; color: #64748b; }
.stat-submore { font-size: 0.7rem; color: #94a3b8; font-weight: 500; }
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

/* ── Modal ── */
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.45);
  display: flex; align-items: center; justify-content: center;
  z-index: 1000;
}
.modal {
  background: white; border-radius: 1rem;
  width: 100%; max-width: 520px; max-height: 90vh;
  overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}
.modal-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
}
.modal-header h2 { font-size: 1.125rem; font-weight: 700; color: #1e293b; margin: 0; }
.modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #94a3b8; line-height: 1; }
.modal-close:hover { color: #1e293b; }
.modal-body { padding: 1.5rem; display: flex; flex-direction: column; gap: 1.25rem; }
.modal-footer { display: flex; gap: 0.75rem; justify-content: flex-end; padding-top: 0.5rem; }

/* ── Form ── */
.form-group { display: flex; flex-direction: column; gap: 0.375rem; position: relative; }
.form-group label { font-size: 0.875rem; font-weight: 600; color: #374151; }
.required { color: #ef4444; }
.form-input {
  padding: 0.625rem 0.875rem;
  border: 1px solid #d1d5db; border-radius: 0.5rem;
  font-size: 0.9375rem; outline: none; transition: border-color 0.15s;
  width: 100%; box-sizing: border-box;
}
.form-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }

/* ── Search Dropdown ── */
.search-dropdown {
  position: absolute; top: 100%; left: 0; right: 0; z-index: 10;
  background: white; border: 1px solid #e2e8f0; border-radius: 0.5rem;
  box-shadow: 0 8px 24px rgba(0,0,0,0.12); max-height: 220px; overflow-y: auto;
}
.search-item {
  padding: 0.625rem 0.875rem; cursor: pointer; border-bottom: 1px solid #f8fafc;
  transition: background 0.1s;
}
.search-item:hover { background: #f0f9ff; }
.search-item:last-child { border-bottom: none; }
.search-item-name { font-weight: 600; color: #1e293b; font-size: 0.875rem; }
.search-item-meta { font-size: 0.75rem; color: #64748b; margin-top: 0.125rem; }
.search-empty { padding: 0.625rem 0.875rem; font-size: 0.875rem; color: #94a3b8; }

/* ── Selected Patient ── */
.selected-patient {
  display: flex; align-items: center; justify-content: space-between;
  background: #f0fdf4; border: 1px solid #86efac; border-radius: 0.5rem;
  padding: 0.625rem 0.875rem; font-size: 0.875rem; color: #166534;
}
.btn-clear { background: none; border: none; cursor: pointer; font-size: 1.25rem; color: #ef4444; line-height: 1; }

/* ── Priority Grid ── */
.priority-grid { display: flex; gap: 0.75rem; }
.priority-opt {
  display: flex; align-items: center; gap: 0.375rem;
  padding: 0.5rem 0.875rem; border: 1px solid #e2e8f0;
  border-radius: 0.5rem; cursor: pointer; font-size: 0.875rem;
  transition: all 0.15s; font-weight: 500; color: #374151;
}
.priority-opt input { display: none; }
.priority-opt.active { border-color: #3b82f6; background: #eff6ff; color: #1d4ed8; }
.priority-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.priority-dot.blue   { background: #3b82f6; }
.priority-dot.yellow { background: #f59e0b; }
.priority-dot.red    { background: #ef4444; }

/* ── Alerts ── */
.alert-error {
  background: #fef2f2; border: 1px solid #fecaca;
  border-radius: 0.5rem; padding: 0.75rem 1rem;
  font-size: 0.875rem; color: #dc2626;
}

/* ── Buttons ── */
.btn-secondary { padding: 0.625rem 1.25rem; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 0.5rem; cursor: pointer; font-weight: 600; font-size: 0.875rem; }
.btn-secondary:hover { background: #e2e8f0; }

/* ── Status Section ── */
.status-section { margin-top: 2rem; }
.status-section .section-title { font-size: 1.125rem; font-weight: 700; color: #1e293b; margin-bottom: 1rem; }
.status-group { margin-bottom: 1.5rem; background: white; border-radius: 0.75rem; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #f1f5f9; }
.status-group.priority-group { border-left: 4px solid #ef4444; }
.status-group-title { margin: 0 0 0.75rem 0; font-size: 0.875rem; font-weight: 600; }
.status-badge { display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; }
.status-badge.blue { background: #e0f2fe; color: #0ea5e9; }
.status-badge.yellow { background: #fef3c7; color: #92400e; }
.status-badge.green { background: #dcfce7; color: #166534; }
.status-badge.red { background: #fee2e2; color: #991b1b; }
.patient-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 0.75rem; }
.patient-card { background: #f8fafc; border-radius: 0.5rem; padding: 0.875rem; display: flex; flex-direction: column; gap: 0.375rem; border: 1px solid #e2e8f0; transition: all 0.15s; }
.patient-card.calling { background: #fef3c7; border-color: #fde047; }
.patient-card.completed { background: #dcfce7; border-color: #86efac; }
.patient-card.priority { background: #fee2e2; border-color: #fca5a5; }
.patient-number { font-family: monospace; font-size: 1.125rem; font-weight: 800; color: #1e40af; }
.patient-name { font-weight: 600; color: #1e293b; font-size: 0.875rem; }
.patient-meta { font-size: 0.75rem; color: #64748b; }
.patient-time { font-size: 0.6875rem; color: #94a3b8; margin-top: auto; }
.patient-priority { margin-top: auto; }
.priority-label { display: inline-block; font-size: 0.625rem; font-weight: 700; color: #991b1b; background: #fecaca; padding: 0.125rem 0.375rem; border-radius: 9999px; }
.empty-state { text-align: center; padding: 3rem 2rem; background: white; border-radius: 0.75rem; border: 1px solid #f1f5f9; }
.empty-state h3 { color: #1e293b; margin-bottom: 0.5rem; font-size: 1rem; }
.empty-state p { color: #64748b; font-size: 0.875rem; margin-bottom: 1rem; }
</style>
