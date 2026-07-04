<template>
  <div class="page-container">
    <div class="page-header">
      <button @click="router.push('/assessments')" class="btn-back">← Kembali</button>
      <h1 class="page-title">{{ editMode ? 'Edit Assessment' : 'Detail Assessment' }}</h1>
      <div class="header-actions">
        <template v-if="!editMode && assessment">
          <button
            v-if="canEdit"
            @click="startEdit"
            class="btn-secondary"
          >Edit</button>
          <button
            v-if="canDelete"
            @click="handleDelete"
            class="btn-danger"
          >Hapus</button>
        </template>
        <template v-if="editMode">
          <button @click="cancelEdit" class="btn-secondary">Batal</button>
          <button @click="handleUpdate" :disabled="saving" class="btn-primary">
            {{ saving ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </template>
      </div>
    </div>

    <div v-if="loading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Memuat data...</p>
    </div>

    <div v-else-if="!assessment" class="empty-state">
      <p>Assessment tidak ditemukan.</p>
    </div>

    <div v-else class="detail-grid">
      <!-- Info Pasien -->
      <div class="card">
        <h3 class="card-title">Informasi Pasien</h3>
        <div class="info-row"><span class="label">Nama</span><span>{{ assessment.pasien?.nama }}</span></div>
        <div class="info-row"><span class="label">NRM</span><span>{{ assessment.pasien?.nrm }}</span></div>
        <div class="info-row"><span class="label">Tgl. Lahir</span><span>{{ formatDate(assessment.pasien?.tanggal_lahir) }}</span></div>
        <div class="info-row"><span class="label">Orang Tua</span><span>{{ assessment.pasien?.nama_wali || '-' }}</span></div>
        <!-- NIK penuh hanya tampil saat assessment aktif (draft) -->
        <div v-if="assessment.pasien?.nik" class="info-row nik-row">
          <span class="label">NIK</span>
          <span class="nik-full-value">{{ assessment.pasien.nik }}</span>
        </div>
        <!-- NIK masked saat assessment sudah final -->
        <div v-else class="info-row">
          <span class="label">NIK</span>
          <span class="nik-masked-value">{{ assessment.pasien?.masked_nik || '-' }}</span>
        </div>
        <div class="info-row"><span class="label">Dokter</span><span>{{ assessment.dokter?.nama }}</span></div>
        <div class="info-row"><span class="label">Tanggal</span><span>{{ formatDate(assessment.tanggal) }}</span></div>
        <div class="info-row">
          <span class="label">Status</span>
          <span :class="['status-badge', `status-${assessment.status}`]">{{ assessment.status }}</span>
        </div>
      </div>

      <!-- Keluhan & Riwayat -->
      <div class="card">
        <h3 class="card-title">Keluhan & Riwayat</h3>
        <div class="form-group">
          <label>Keluhan Utama</label>
          <textarea v-if="editMode" v-model="form.keluhan_utama" rows="3" class="form-input"></textarea>
          <p v-else class="field-value">{{ assessment.keluhan || '-' }}</p>
        </div>
        <div class="form-group">
          <label>Riwayat Penyakit</label>
          <textarea v-if="editMode" v-model="form.riwayat_penyakit" rows="3" class="form-input"></textarea>
          <p v-else class="field-value">{{ assessment.riwayat || '-' }}</p>
        </div>
      </div>

      <!-- Hasil Pemeriksaan Fisik -->
      <div class="card">
        <h3 class="card-title">Hasil Pemeriksaan Fisik</h3>
        <div class="vitals-grid">
          <div class="vital-item">
            <label>Tensi</label>
            <input v-if="editMode" v-model="form.hasil_pemeriksaan.tensi" type="text" class="form-input" placeholder="120/80" />
            <span v-else class="field-value">{{ assessment.hasil_fisik?.tensi || '-' }}</span>
          </div>
          <div class="vital-item">
            <label>Nadi</label>
            <input v-if="editMode" v-model="form.hasil_pemeriksaan.nadi" type="text" class="form-input" placeholder="80 bpm" />
            <span v-else class="field-value">{{ assessment.hasil_fisik?.nadi || '-' }}</span>
          </div>
          <div class="vital-item">
            <label>Suhu</label>
            <input v-if="editMode" v-model="form.hasil_pemeriksaan.suhu" type="text" class="form-input" placeholder="36.5°C" />
            <span v-else class="field-value">{{ assessment.hasil_fisik?.suhu || '-' }}</span>
          </div>
          <div class="vital-item">
            <label>Berat Badan (kg)</label>
            <input v-if="editMode" v-model="form.hasil_pemeriksaan.berat_badan" type="number" class="form-input" />
            <span v-else class="field-value">{{ assessment.hasil_fisik?.berat_badan || '-' }}</span>
          </div>
          <div class="vital-item">
            <label>Tinggi Badan (cm)</label>
            <input v-if="editMode" v-model="form.hasil_pemeriksaan.tinggi_badan" type="number" class="form-input" />
            <span v-else class="field-value">{{ assessment.hasil_fisik?.tinggi_badan || '-' }}</span>
          </div>
        </div>
      </div>

      <!-- Diagnosis & Rencana -->
      <div class="card">
        <h3 class="card-title">Diagnosis & Rencana Terapi</h3>
        <div class="form-group">
          <label>Diagnosis <span v-if="editMode" class="required">*</span></label>
          <textarea v-if="editMode" v-model="form.diagnosis" rows="3" class="form-input" required></textarea>
          <p v-else class="field-value highlight">{{ assessment.diagnosis || '-' }}</p>
        </div>
        <div class="form-group">
          <label>Rencana Terapi</label>
          <textarea v-if="editMode" v-model="form.rencana_terapi" rows="3" class="form-input"></textarea>
          <p v-else class="field-value">{{ assessment.rencana_terapi || '-' }}</p>
        </div>
        <div class="form-group">
          <label>Catatan Tambahan</label>
          <textarea v-if="editMode" v-model="form.catatan_tambahan" rows="2" class="form-input"></textarea>
          <p v-else class="field-value">{{ assessment.catatan || '-' }}</p>
        </div>
      </div>
    </div>

    <div v-if="saveError" class="alert-error">{{ saveError }}</div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAssessmentStore } from '../stores/assessmentStore'
import { useAuthStore } from '../../auth/stores/authStore'
import { useNotificationStore } from '../../../shared/stores/notificationStore'

const router   = useRouter()
const route    = useRoute()
const store    = useAssessmentStore()
const auth     = useAuthStore()
const notify   = useNotificationStore()

const assessment = ref(null)
const loading    = ref(false)
const editMode   = ref(false)
const saving     = ref(false)
const saveError  = ref('')

const form = ref({
  keluhan_utama: '',
  riwayat_penyakit: '',
  diagnosis: '',
  rencana_terapi: '',
  catatan_tambahan: '',
  hasil_pemeriksaan: { tensi: '', nadi: '', suhu: '', berat_badan: '', tinggi_badan: '' },
})

const canEdit = computed(() => {
  if (!assessment.value) return false
  if (auth.isAdmin) return true
  return auth.isDokter &&
    assessment.value.status === 'draft' &&
    assessment.value.dokter?.id === auth.user?.id
})

const canDelete = computed(() => {
  if (!assessment.value) return false
  if (auth.isAdmin) return true
  return auth.isDokter &&
    assessment.value.status === 'draft' &&
    assessment.value.dokter?.id === auth.user?.id
})

const startEdit = () => {
  const a = assessment.value
  form.value = {
    keluhan_utama:    a.keluhan || '',
    riwayat_penyakit: a.riwayat || '',
    diagnosis:        a.diagnosis || '',
    rencana_terapi:   a.rencana_terapi || '',
    catatan_tambahan: a.catatan || '',
    hasil_pemeriksaan: {
      tensi:         a.hasil_fisik?.tensi || '',
      nadi:          a.hasil_fisik?.nadi || '',
      suhu:          a.hasil_fisik?.suhu || '',
      berat_badan:   a.hasil_fisik?.berat_badan || '',
      tinggi_badan:  a.hasil_fisik?.tinggi_badan || '',
    },
  }
  editMode.value = true
}

const cancelEdit = () => { editMode.value = false; saveError.value = '' }

const handleUpdate = async () => {
  if (!form.value.diagnosis.trim()) {
    saveError.value = 'Diagnosis tidak boleh kosong.'
    return
  }
  saving.value = true
  saveError.value = ''
  const result = await store.updateAssessment(route.params.id, form.value)
  saving.value = false
  if (result.success) {
    assessment.value = result.data
    editMode.value = false
    notify.success('Assessment berhasil diperbarui', 'Berhasil')
  } else {
    saveError.value = result.error || 'Gagal menyimpan'
  }
}

const handleDelete = async () => {
  if (!confirm('Hapus assessment ini? Tindakan tidak dapat dibatalkan.')) return
  const result = await store.deleteAssessment(route.params.id)
  if (result.success) {
    notify.success('Assessment dihapus', 'Berhasil')
    router.push('/assessments')
  } else {
    notify.error(result.error || 'Gagal menghapus')
  }
}

const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) : '-'

onMounted(async () => {
  loading.value = true
  const result = await store.getAssessmentById(route.params.id)
  loading.value = false
  if (result.success) assessment.value = result.data
})
</script>

<style scoped>
.page-container { padding: 1.5rem; max-width: 1000px; margin: 0 auto; }
.page-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem; }
.btn-back { background: none; border: none; color: #3b82f6; font-weight: 600; cursor: pointer; font-size: 0.875rem; }
.page-title { flex: 1; font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 0; }
.header-actions { display: flex; gap: 0.75rem; }

.loading-container { text-align: center; padding: 3rem; }
.loading-spinner { width: 36px; height: 36px; border: 3px solid #f1f5f9; border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { to { transform: rotate(360deg); } }

.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
.card { background: white; border-radius: 0.75rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.card-title { font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0 0 1.25rem; padding-bottom: 0.75rem; border-bottom: 1px solid #f1f5f9; }

.info-row { display: flex; gap: 1rem; padding: 0.5rem 0; border-bottom: 1px solid #f8fafc; font-size: 0.9rem; }
.info-row .label { width: 100px; color: #64748b; font-weight: 600; flex-shrink: 0; }

/* NIK aktif (draft) — tampil penuh */
.nik-row { background: #fefce8; border-radius: 0.375rem; padding: 0.5rem 0.5rem; margin: 0 -0.5rem; }
.nik-full-value {
  font-family: 'Courier New', Courier, monospace;
  font-weight: 700;
  font-size: 0.875rem;
  color: #78350f;
  letter-spacing: 0.06em;
  user-select: none;
}
/* NIK final — tampil masked */
.nik-masked-value {
  font-family: 'Courier New', Courier, monospace;
  font-weight: 600;
  font-size: 0.82rem;
  color: #64748b;
  background: #f1f5f9;
  padding: 0.15rem 0.5rem;
  border-radius: 0.25rem;
  letter-spacing: 0.04em;
  user-select: none;
}

.form-group { margin-bottom: 1rem; }
.form-group label { display: block; font-size: 0.8125rem; font-weight: 600; color: #64748b; margin-bottom: 0.375rem; }
.required { color: #ef4444; }
.form-input { width: 100%; padding: 0.625rem 0.875rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.9rem; outline: none; box-sizing: border-box; }
.form-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
.field-value { color: #334155; font-size: 0.9rem; margin: 0; line-height: 1.6; white-space: pre-wrap; }
.field-value.highlight { font-weight: 600; color: #1e293b; }

.vitals-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.vital-item label { display: block; font-size: 0.8125rem; font-weight: 600; color: #64748b; margin-bottom: 0.25rem; }

.status-badge { padding: 0.2rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
.status-draft    { background: #f1f5f9; color: #475569; }
.status-final    { background: #dcfce7; color: #166534; }
.status-submitted{ background: #fef3c7; color: #92400e; }

.btn-primary  { padding: 0.625rem 1.25rem; background: #1e40af; color: white; border: none; border-radius: 0.5rem; font-weight: 600; cursor: pointer; font-size: 0.875rem; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secondary{ padding: 0.625rem 1.25rem; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-weight: 600; cursor: pointer; font-size: 0.875rem; }
.btn-danger   { padding: 0.625rem 1.25rem; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; border-radius: 0.5rem; font-weight: 600; cursor: pointer; font-size: 0.875rem; }
.btn-danger:hover { background: #dc2626; color: white; }

.alert-error { background: #fef2f2; border: 1px solid #fecaca; border-radius: 0.5rem; padding: 0.75rem 1rem; margin-top: 1rem; color: #dc2626; font-size: 0.875rem; }

.empty-state { text-align: center; padding: 4rem; }

@media (max-width: 768px) {
  .detail-grid { grid-template-columns: 1fr; }
  .vitals-grid { grid-template-columns: 1fr; }
}
</style>
