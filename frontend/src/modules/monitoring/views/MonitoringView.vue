<template>
  <div v-if="error" class="error-page">{{ error }}</div>
  <div v-else class="page-container">
    <div class="page-header">
      <div>
        <button @click="goToDashboard" class="btn-back">← Kembali ke Dashboard</button>
        <h1 class="page-title">Monitoring Progress</h1>
        <p class="page-subtitle">Catatan harian dan perkembangan terapi anak</p>
      </div>
      <button 
        v-if="authStore.user?.role === 'dokter' || authStore.user?.role === 'terapis'" 
        @click="showCreateForm = true" 
        class="btn-primary"
      >
        + Catat Sesi
      </button>
    </div>

    <!-- Search & Filter -->
    <div class="filters-card">
      <input 
        type="text" 
        v-model="searchQuery" 
        placeholder="Cari pasien atau terapis..."
        class="form-input"
      />
    </div>

    <!-- Table -->
    <div class="content-card">
      <div v-if="monitoringStore.loading" class="loading-state">
        <p>Memuat data monitoring...</p>
      </div>
      <div v-else-if="filteredMonitorings.length === 0" class="empty-state">
        <p>Belum ada catatan monitoring.</p>
      </div>
      <div v-else class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>Pasien</th>
              <th>Terapis</th>
              <th>Score</th>
              <th>Kehadiran</th>
              <th>Catatan</th>
              <th style="text-align: right;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="m in filteredMonitorings" :key="m.id">
              <td>{{ formatDate(m.tanggal_sesi) }}</td>
              <td><strong>{{ m.pasien?.nama }}</strong></td>
              <td>{{ m.terapis?.nama }}</td>
              <td>
                <span :class="['score-badge', getScoreClass(m.progress_score)]">
                  {{ m.progress_score ?? '-' }}%
                </span>
              </td>
              <td>
                <span :class="['status-badge', `kehadiran-${m.kehadiran}`]">
                  {{ formatKehadiran(m.kehadiran) }}
                </span>
              </td>
              <td class="truncate">{{ m.catatan_perkembangan || '-' }}</td>
              <td style="text-align: right;">
                <div class="action-group">
                  <button 
                    @click="openDetail(m)" 
                    class="btn-action detail"
                    title="Lihat Detail"
                  >
                    👁️
                  </button>
                  <button 
                    v-if="authStore.user?.role === 'dokter' || authStore.user?.role === 'admin'" 
                    @click="generateAssessment(m.therapy?.id)" 
                    class="btn-action assess"
                    title="Generate Assessment"
                  >
                    📄
                  </button>
                  <button 
                    v-if="canDelete(m)"
                    @click="deleteMonitoring(m.id)" 
                    class="btn-action delete"
                    title="Hapus"
                  >
                    🗑️
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ========== MODAL DETAIL ========== -->
    <div v-if="showDetailModal" class="modal-overlay" @click.self="showDetailModal = false">
      <div class="modal-card">
        <div class="modal-header">
          <h3>Detail Monitoring</h3>
          <button @click="showDetailModal = false" class="btn-close">×</button>
        </div>
        <div v-if="selectedMonitoring" class="modal-body">
          <div class="detail-grid">
            <div class="detail-row">
              <span class="label">Pasien</span>
              <span class="value">{{ selectedMonitoring.pasien?.nama }}</span>
            </div>
            <div class="detail-row">
              <span class="label">NRM</span>
              <span class="value">{{ selectedMonitoring.pasien?.nrm }}</span>
            </div>
            <div class="detail-row">
              <span class="label">Terapi</span>
              <span class="value">{{ selectedMonitoring.therapy?.nama_terapi }}</span>
            </div>
            <div class="detail-row">
              <span class="label">Terapis</span>
              <span class="value">{{ selectedMonitoring.terapis?.nama }}</span>
            </div>
            <div class="detail-row">
              <span class="label">Tanggal Sesi</span>
              <span class="value">{{ formatDateFull(selectedMonitoring.tanggal_sesi) }}</span>
            </div>
            <div class="detail-row">
              <span class="label">Waktu</span>
              <span class="value">{{ selectedMonitoring.waktu_mulai }} - {{ selectedMonitoring.waktu_selesai }}</span>
            </div>
            <div class="detail-row">
              <span class="label">Kehadiran</span>
              <span :class="['status-badge', `kehadiran-${selectedMonitoring.kehadiran}`]">
                {{ formatKehadiran(selectedMonitoring.kehadiran) }}
              </span>
            </div>
            <div class="detail-row">
              <span class="label">Score Progress</span>
              <span :class="['score-badge', getScoreClass(selectedMonitoring.progress_score)]">
                {{ selectedMonitoring.progress_score ?? '-' }}%
              </span>
            </div>
          </div>

          <div class="detail-section">
            <h4>Perkembangan</h4>
            <p class="detail-text">{{ selectedMonitoring.catatan_perkembangan || '-' }}</p>
          </div>

          <div class="detail-section">
            <h4>Kondisi Pasien</h4>
            <p class="detail-text">{{ selectedMonitoring.kondisi_pasien || '-' }}</p>
          </div>

          <div class="detail-section">
            <h4>Rekomendasi</h4>
            <p class="detail-text">{{ selectedMonitoring.rekomendasi || '-' }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ========== MODAL CREATE ========== -->
    <div v-if="showCreateForm" class="modal-overlay" @click.self="showCreateForm = false">
      <div class="modal-card modal-large">
        <div class="modal-header">
          <h3>Catat Sesi Monitoring Baru</h3>
          <button @click="showCreateForm = false" class="btn-close">×</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Pilih Terapi <span class="required">*</span></label>
            <select v-model="newMonitoring.id_terapi" class="form-input" required>
              <option value="">-- Pilih Terapi --</option>
              <option v-for="t in activeTherapies" :key="t.id" :value="t.id">
                {{ t.nama }} ({{ t.pasien?.nama }})
              </option>
            </select>
          </div>

          <div class="form-group">
            <label>Tanggal Sesi <span class="required">*</span></label>
            <input type="date" v-model="newMonitoring.tanggal_sesi" class="form-input" required />
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Waktu Mulai</label>
              <input type="time" v-model="newMonitoring.waktu_mulai" class="form-input" />
            </div>
            <div class="form-group">
              <label>Waktu Selesai</label>
              <input type="time" v-model="newMonitoring.waktu_selesai" class="form-input" />
            </div>
          </div>

          <div class="form-group">
            <label>Kehadiran <span class="required">*</span></label>
            <select v-model="newMonitoring.kehadiran" class="form-input" required>
              <option value="">-- Pilih --</option>
              <option value="hadir">Hadir</option>
              <option value="tidak_hadir">Tidak Hadir</option>
              <option value="izin">Izin</option>
              <option value="sakit">Sakit</option>
            </select>
          </div>

          <div class="form-group">
            <label>Score Progress (0-100)</label>
            <input type="number" v-model="newMonitoring.progress_score" class="form-input" min="0" max="100" />
          </div>

          <div class="form-group">
            <label>Catatan Perkembangan</label>
            <textarea v-model="newMonitoring.catatan_perkembangan" class="form-textarea" rows="3" placeholder="Deskripsi perkembangan pasien..."></textarea>
          </div>

          <div class="form-group">
            <label>Kondisi Pasien</label>
            <textarea v-model="newMonitoring.kondisi_pasien" class="form-textarea" rows="2" placeholder="Kondisi umum pasien saat sesi..."></textarea>
          </div>

          <div class="form-group">
            <label>Rekomendasi</label>
            <textarea v-model="newMonitoring.rekomendasi" class="form-textarea" rows="2" placeholder="Rekomendasi untuk sesi berikutnya..."></textarea>
          </div>

          <div v-if="createError" class="alert-error">{{ createError }}</div>

          <div class="modal-actions">
            <button @click="showCreateForm = false" class="btn-secondary">Batal</button>
            <button @click="submitCreate" :disabled="createLoading" class="btn-primary">
              {{ createLoading ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../auth/stores/authStore'
import { useMonitoringStore } from '../stores/monitoringStore'
import { useNavigation } from '../../../shared/composables/useNavigation'
import api from '../../../core/services/api'

const router = useRouter()
const authStore = useAuthStore()
const monitoringStore = useMonitoringStore()
const { goToDashboard } = useNavigation()

// ── State ─────────────────────────────────────────────────
const searchQuery = ref('')
const error = ref(null)
const showDetailModal = ref(false)
const showCreateForm = ref(false)
const selectedMonitoring = ref(null)
const activeTherapies = ref([])
const createLoading = ref(false)
const createError = ref('')

const newMonitoring = ref({
  id_terapi: '',
  tanggal_sesi: new Date().toISOString().split('T')[0],
  waktu_mulai: '09:00',
  waktu_selesai: '10:00',
  kehadiran: 'hadir',
  progress_score: 50,
  catatan_perkembangan: '',
  kondisi_pasien: '',
  rekomendasi: ''
})

// ── Computed ──────────────────────────────────────────────
const filteredMonitorings = computed(() => {
  if (!monitoringStore.monitorings) return []
  const q = searchQuery.value?.toLowerCase() || ''
  return monitoringStore.monitorings.filter(m =>
    (m.pasien?.nama?.toLowerCase().includes(q) || '') ||
    (m.terapis?.nama?.toLowerCase().includes(q) || '')
  )
})

// ── Lifecycle ─────────────────────────────────────────────
onMounted(async () => {
  await refreshData()
  if (showCreateForm.value) {
    await fetchActiveTherapies()
  }
})

// ── Methods ───────────────────────────────────────────────
const refreshData = async () => {
  try {
    await monitoringStore.fetchMonitorings()
  } catch (err) {
    error.value = 'Gagal memuat data monitoring.'
  }
}

const fetchActiveTherapies = async () => {
  try {
    const res = await api.get('/therapies', { params: { status: 'aktif' } })
    activeTherapies.value = res.data?.data ?? []
  } catch (err) {
    console.error('Error fetching therapies:', err)
  }
}

const openDetail = (monitoring) => {
  selectedMonitoring.value = monitoring
  showDetailModal.value = true
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' })
}

const formatDateFull = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
}

const formatKehadiran = (status) => {
  const map = {
    'hadir': 'Hadir',
    'tidak_hadir': 'Tidak Hadir',
    'izin': 'Izin',
    'sakit': 'Sakit'
  }
  return map[status] || status
}

const getScoreClass = (score) => {
  if (!score) return 'neutral'
  if (score >= 80) return 'excellent'
  if (score >= 60) return 'good'
  if (score >= 40) return 'fair'
  return 'poor'
}

const canDelete = (monitoring) => {
  if (authStore.user?.role === 'admin') return true
  if (authStore.user?.role === 'dokter' || authStore.user?.role === 'terapis') {
    return monitoring.terapis?.id === authStore.user?.id
  }
  return false
}

const generateAssessment = async (therapyId) => {
  if (!therapyId) {
    alert('Data terapi tidak ditemukan')
    return
  }
  if (!confirm('Buat draft assessment medis berdasarkan hasil monitoring ini?')) return

  const result = await monitoringStore.generateAssessment(therapyId)
  if (result.success) {
    alert('Assessment berhasil dibuat (Draft)')
    router.push('/assessments')
  } else {
    alert('Error: ' + result.error)
  }
}

const deleteMonitoring = async (id) => {
  if (!confirm('Hapus catatan monitoring ini?')) return

  const result = await monitoringStore.deleteMonitoring(id)
  if (result.success) {
    alert('Berhasil dihapus')
    await refreshData()
  } else {
    alert('Error: ' + result.error)
  }
}

const submitCreate = async () => {
  if (!newMonitoring.value.id_terapi) {
    createError.value = 'Pilih terapi terlebih dahulu'
    return
  }
  if (!newMonitoring.value.tanggal_sesi) {
    createError.value = 'Isi tanggal sesi'
    return
  }

  createLoading.value = true
  createError.value = ''

  try {
    const res = await api.post('/monitorings', newMonitoring.value)
    if (res.data?.success) {
      alert('Monitoring berhasil dicatat')
      showCreateForm.value = false
      newMonitoring.value = {
        id_terapi: '',
        tanggal_sesi: new Date().toISOString().split('T')[0],
        waktu_mulai: '09:00',
        waktu_selesai: '10:00',
        kehadiran: 'hadir',
        progress_score: 50,
        catatan_perkembangan: '',
        kondisi_pasien: '',
        rekomendasi: ''
      }
      await refreshData()
    } else {
      createError.value = res.data?.message || 'Gagal menyimpan monitoring'
    }
  } catch (err) {
    createError.value = err.response?.data?.message || 'Terjadi kesalahan'
  } finally {
    createLoading.value = false
  }
}
</script>

<style scoped>
/* ── Layout ────────────────────────────────────────────── */
.page-container { padding: 2rem; max-width: 1400px; margin: 0 auto; }
.page-header {
  display: flex; justify-content: space-between; align-items: flex-start;
  margin-bottom: 1.5rem; gap: 2rem;
}
.page-header > div { flex: 1; }
.page-title { font-size: 1.75rem; font-weight: 700; color: #1e293b; margin: 0; }
.page-subtitle { color: #64748b; margin: 0.5rem 0 0; }
.btn-back {
  background: none; border: none; color: #3b82f6; font-weight: 600;
  cursor: pointer; font-size: 0.875rem; padding: 0; margin-bottom: 0.75rem;
}

/* ── Cards ─────────────────────────────────────────────── */
.filters-card, .content-card {
  background: white; border-radius: 0.875rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
.filters-card { padding: 1rem; margin-bottom: 1.5rem; }
.content-card { overflow: hidden; }

/* ── Form Input ────────────────────────────────────────── */
.form-input {
  width: 100%; padding: 0.625rem 1rem; border: 1px solid #e2e8f0;
  border-radius: 0.5rem; font-size: 0.875rem; box-sizing: border-box;
}
.form-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); outline: none; }

/* ── Table ─────────────────────────────────────────────── */
.table-container { overflow-x: auto; }
.data-table {
  width: 100%; border-collapse: collapse; font-size: 0.875rem;
}
.data-table th {
  background: #f8fafc; padding: 1rem 1.25rem; text-align: left;
  font-weight: 600; color: #64748b; border-bottom: 1px solid #e2e8f0;
}
.data-table td { padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; }
.data-table tbody tr:hover { background: #f8fafc; }

.truncate { max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* ── Badges & Status ───────────────────────────────────── */
.score-badge {
  display: inline-block; padding: 0.375rem 0.75rem; border-radius: 9999px;
  font-weight: 600; font-size: 0.8125rem;
}
.score-badge.excellent { background: #dcfce7; color: #166534; }
.score-badge.good { background: #dbeafe; color: #1e40af; }
.score-badge.fair { background: #fef3c7; color: #92400e; }
.score-badge.poor { background: #fee2e2; color: #991b1b; }
.score-badge.neutral { background: #f3f4f6; color: #6b7280; }

.status-badge {
  display: inline-block; padding: 0.375rem 0.75rem; border-radius: 9999px;
  font-weight: 600; font-size: 0.8125rem;
}
.kehadiran-hadir { background: #dcfce7; color: #166534; }
.kehadiran-tidak_hadir { background: #fee2e2; color: #991b1b; }
.kehadiran-izin { background: #fef3c7; color: #92400e; }
.kehadiran-sakit { background: #dbeafe; color: #1e40af; }

/* ── Action Buttons ────────────────────────────────────── */
.action-group { display: flex; gap: 0.5rem; justify-content: flex-end; }
.btn-action {
  width: 32px; height: 32px; border-radius: 0.5rem;
  border: 1px solid #e2e8f0; background: #f8fafc; cursor: pointer;
  font-size: 1rem; transition: all 0.15s;
}
.btn-action:hover { background: #f1f5f9; }
.btn-action.delete:hover { background: #fee2e2; border-color: #fecaca; }
.btn-action.assess:hover { background: #dbeafe; border-color: #bfdbfe; }

.btn-primary {
  padding: 0.625rem 1.25rem; background: #2563eb; color: white;
  border: none; border-radius: 0.5rem; font-weight: 600; cursor: pointer;
  font-size: 0.875rem; transition: background 0.15s;
}
.btn-primary:hover:not(:disabled) { background: #1d4ed8; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

.btn-secondary {
  padding: 0.625rem 1.25rem; background: #f8fafc; color: #475569;
  border: 1px solid #e2e8f0; border-radius: 0.5rem; font-weight: 600;
  cursor: pointer; font-size: 0.875rem;
}

/* ── States ────────────────────────────────────────────── */
.loading-state, .empty-state {
  padding: 3rem 2rem; text-align: center; color: #64748b;
}

/* ── Modal ─────────────────────────────────────────────── */
.modal-overlay {
  position: fixed; top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.5); display: flex; align-items: center;
  justify-content: center; z-index: 50; backdrop-filter: blur(2px);
}

.modal-card {
  background: white; border-radius: 1rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
  max-width: 600px; width: 90vw; max-height: 85vh; overflow-y: auto;
}
.modal-large { max-width: 700px; }

.modal-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 1.5rem; border-bottom: 1px solid #f1f5f9;
}
.modal-header h3 { font-size: 1.125rem; font-weight: 700; color: #1e293b; margin: 0; }
.btn-close {
  background: none; border: none; font-size: 1.5rem; color: #94a3b8;
  cursor: pointer; padding: 0; width: 32px; height: 32px;
  display: flex; align-items: center; justify-content: center;
}
.btn-close:hover { color: #475569; }

.modal-body { padding: 1.5rem; }
.modal-actions {
  display: flex; gap: 1rem; justify-content: flex-end;
  padding-top: 1.5rem; border-top: 1px solid #f1f5f9;
}

/* ── Detail Grid ───────────────────────────────────────── */
.detail-grid { display: grid; gap: 1rem; margin-bottom: 2rem; }
.detail-row {
  display: grid; grid-template-columns: 150px 1fr; gap: 1rem;
  padding-bottom: 1rem; border-bottom: 1px solid #f1f5f9;
}
.detail-row .label { font-weight: 600; color: #64748b; font-size: 0.875rem; }
.detail-row .value { color: #1e293b; }

.detail-section { margin-bottom: 1.5rem; }
.detail-section h4 { font-size: 0.95rem; font-weight: 700; color: #1e293b; margin: 0 0 0.75rem; }
.detail-text { margin: 0; color: #334155; line-height: 1.6; white-space: pre-wrap; }

/* ── Form ──────────────────────────────────────────────── */
.form-group { margin-bottom: 1.25rem; }
.form-group label {
  display: block; font-size: 0.875rem; font-weight: 600; color: #64748b;
  margin-bottom: 0.375rem;
}
.required { color: #ef4444; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-textarea {
  width: 100%; padding: 0.625rem 1rem; border: 1px solid #e2e8f0;
  border-radius: 0.5rem; font-size: 0.875rem; font-family: inherit;
  resize: vertical; box-sizing: border-box;
}
.form-textarea:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); outline: none; }

/* ── Alert ─────────────────────────────────────────────── */
.alert-error {
  background: #fef2f2; border: 1px solid #fecaca; border-radius: 0.5rem;
  padding: 0.75rem 1rem; color: #dc2626; font-size: 0.875rem; margin-bottom: 1rem;
}

/* ── Responsive ────────────────────────────────────────── */
@media (max-width: 768px) {
  .page-header { flex-direction: column; }
  .form-row { grid-template-columns: 1fr; }
  .modal-card { max-width: 95vw; }
  .detail-row { grid-template-columns: 120px 1fr; }
}
</style>
