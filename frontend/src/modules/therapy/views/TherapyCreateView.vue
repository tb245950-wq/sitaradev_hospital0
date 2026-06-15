<template>
  <div class="page-container">
    <div class="page-header">
      <button @click="goBack" class="btn-back">← Batal</button>
      <h1 class="page-title">Buat Program Terapi Baru</h1>
    </div>

    <form @submit.prevent="handleSubmit" class="form-card">
      <div class="form-section">
        <h3>Informasi Pasien</h3>
        
        <div class="form-group">
          <label>Pasien <span class="required">*</span></label>
          <select v-model="form.id_pasien" required class="form-select">
            <option value="">Pilih Pasien</option>
            <option v-for="patient in patients" :key="patient.id" :value="patient.id">
              {{ patient.nama }} - {{ patient.nrm }}
            </option>
          </select>
        </div>
        
        <div class="form-group">
          <label>Assessment Referensi</label>
          <select v-model="form.id_assessment" class="form-select">
            <option value="">Pilih Assessment (Opsional)</option>
            <option v-for="assessment in assessments" :key="assessment.id" :value="assessment.id">
              {{ assessment.diagnosis }} - {{ formatDate(assessment.tanggal) }}
            </option>
          </select>
        </div>
      </div>

      <div class="form-section">
        <h3>Detail Program Terapi</h3>
        
        <div class="form-group">
          <label>Nama/Jenis Terapi <span class="required">*</span></label>
          <input v-model="form.nama_terapi" type="text" required class="form-input" placeholder="Contoh: Terapi Wicara" />
        </div>
        
        <div class="form-group">
          <label>Deskripsi & Tujuan <span class="required">*</span></label>
          <textarea v-model="form.deskripsi" required rows="3" class="form-textarea" placeholder="Deskripsi program dan tujuan yang ingin dicapai..."></textarea>
        </div>
        
        <div class="form-group">
          <label>Dosis/Metode</label>
          <input v-model="form.dosis" type="text" class="form-input" placeholder="Contoh: 2x sehari, metode PECS, dll" />
        </div>
      </div>

      <div class="form-section">
        <h3>Jadwal & Durasi</h3>
        
        <div class="form-row">
          <div class="form-group">
            <label>Frekuensi per Minggu <span class="required">*</span></label>
            <input v-model.number="form.frekuensi_per_minggu" type="number" min="1" max="7" required class="form-input" />
            <small class="hint">1-7 kali per minggu</small>
          </div>
          
          <div class="form-group">
            <label>Estimasi Durasi (Hari) <span class="required">*</span></label>
            <input v-model.number="form.durasi_hari" type="number" min="1" required class="form-input" />
            <small class="hint">Total masa program</small>
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label>Tanggal Mulai <span class="required">*</span></label>
            <input v-model="form.tanggal_mulai" type="date" required class="form-input" />
          </div>
          
          <div class="form-group">
            <label>Tanggal Selesai</label>
            <input v-model="form.tanggal_selesai" type="date" class="form-input" />
            <small class="hint">Opsional</small>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <button type="button" @click="goBack" class="btn-secondary">Batal</button>
        <button type="submit" :disabled="loading" class="btn-primary">
          {{ loading ? 'Menyimpan...' : 'Simpan Program Terapi' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useTherapyStore } from '../stores/therapyStore'
import { usePatientStore } from '../../patients/stores/patientStore'
import { useAssessmentStore } from '../../assessment/stores/assessmentStore'

const router = useRouter()
const therapyStore = useTherapyStore()
const patientStore = usePatientStore()
const assessmentStore = useAssessmentStore()

const loading = ref(false)
const patients = ref([])
const assessments = ref([])

const form = ref({
  id_pasien: '',
  id_assessment: '',
  nama_terapi: '',
  deskripsi: '',
  dosis: '',
  durasi_hari: 30,
  frekuensi_per_minggu: 2,
  tanggal_mulai: new Date().toISOString().split('T')[0],
  tanggal_selesai: ''
})

const goBack = () => router.push('/therapies')

const fetchData = async () => {
  try {
    await Promise.all([
      patientStore.fetchPatients(1),
      assessmentStore.fetchAssessments()
    ])
    patients.value = patientStore.patients
    assessments.value = assessmentStore.assessments
  } catch (error) {
    console.error('Error fetching data:', error)
  }
}

const handleSubmit = async () => {
  loading.value = true
  try {
    const result = await therapyStore.createTherapy(form.value)
    if (result.success) {
      alert('Program terapi berhasil dibuat')
      router.push('/therapies')
    } else {
      alert('Gagal: ' + result.error)
    }
  } catch (error) {
    alert('Terjadi kesalahan')
  } finally {
    loading.value = false
  }
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID')
}

onMounted(() => {
  fetchData()
})
</script>

<style scoped>
.page-container { padding: 2rem; max-width: 900px; margin: 0 auto; }
.page-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem; }
.btn-back { padding: 0.5rem 1rem; background: transparent; border: 1px solid #e2e8f0; border-radius: 0.5rem; cursor: pointer; color: #64748b; }
.page-title { flex: 1; font-size: 1.75rem; font-weight: 700; color: #1e293b; }
.form-card { background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #f1f5f9; }
.form-section { margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid #f1f5f9; }
.form-section:last-of-type { border-bottom: none; }
.form-section h3 { font-size: 1.125rem; font-weight: 600; color: #1e293b; margin-bottom: 1rem; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; font-weight: 600; color: #334155; margin-bottom: 0.5rem; }
.required { color: #ef4444; }
.form-input, .form-select, .form-textarea { width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-size: 0.95rem; outline: none; }
.form-textarea { resize: vertical; min-height: 80px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-actions { display: flex; justify-content: flex-end; gap: 1rem; padding-top: 1.5rem; border-top: 1px solid #f1f5f9; }
.btn-secondary { padding: 0.75rem 1.5rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.5rem; cursor: pointer; color: #475569; }
.btn-primary { padding: 0.75rem 1.5rem; background: #1e40af; color: white; border: none; border-radius: 0.5rem; font-weight: 600; }
.btn-primary:disabled { opacity: 0.7; cursor: not-allowed; }
</style>
