<template>
  <div class="page-container">
    <div class="page-header">
      <button @click="goBack" class="btn-back">← Batal</button>
      <h1 class="page-title">Tambah ke Antrian</h1>
    </div>

    <form @submit.prevent="handleSubmit" class="form-card">
      <div class="form-group">
        <label>Pasien <span class="required">*</span></label>
        <select v-model="form.id_pasien" required class="form-select">
          <option value="">Pilih Pasien</option>
          <option v-for="patient in patients" :key="patient.id" :value="patient.id">
            {{ patient.nrm }} - {{ patient.nama }}
          </option>
        </select>
        <div class="form-hint">Cari pasien berdasarkan NRM atau Nama</div>
      </div>

      <div class="form-group">
        <label>Jenis Layanan <span class="required">*</span></label>
        <select v-model="form.jenis_layanan" required class="form-select">
          <option value="assessment">Assessment Medis</option>
          <option value="terapi">Sesi Terapi</option>
        </select>
      </div>

      <div class="form-group">
        <label>Prioritas</label>
        <select v-model.number="form.prioritas" class="form-select">
          <option :value="0">Normal (0)</option>
          <option :value="3">Urgent (3)</option>
          <option :value="7">Emergency (7)</option>
          <option :value="10">Kritis (10)</option>
        </select>
      </div>

      <div class="form-group">
        <label>Catatan</label>
        <textarea v-model="form.catatan" rows="3" class="form-textarea" placeholder="Catatan tambahan (keluhan singkat, dll)"></textarea>
      </div>

      <div class="form-actions">
        <button type="button" @click="goBack" class="btn-secondary">Batal</button>
        <button type="submit" :disabled="loading" class="btn-primary">
          {{ loading ? 'Menyimpan...' : 'Daftarkan ke Antrian' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { usePatientStore } from '../../patients/stores/patientStore'
import { queueService } from '../services/queueService'

const router = useRouter()
const patientStore = usePatientStore()

const loading = ref(false)
const patients = ref([])

const form = ref({
  id_pasien: '',
  jenis_layanan: 'assessment',
  prioritas: 0,
  catatan: ''
})

const goBack = () => router.push('/queues')

const fetchPatients = async () => {
  try {
    // Fetch all for selection (limit 100 for simplicity)
    const response = await patientStore.fetchPatients(1)
    patients.value = patientStore.patients
  } catch (error) {
    console.error('Error fetching patients:', error)
  }
}

const handleSubmit = async () => {
  loading.value = true
  try {
    const response = await queueService.addToQueue(form.value)
    if (response.success) {
      alert('Pasien berhasil ditambahkan ke antrian')
      router.push('/queues')
    }
  } catch (error) {
    alert('Gagal menambah ke antrian: ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchPatients()
})
</script>

<style scoped>
.page-container { padding: 2rem; max-width: 800px; margin: 0 auto; }
.page-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem; }
.btn-back { padding: 0.5rem 1rem; background: transparent; border: 1px solid #e2e8f0; border-radius: 0.5rem; cursor: pointer; color: #64748b; }
.page-title { flex: 1; font-size: 1.75rem; font-weight: 700; color: #1e293b; }
.form-card { background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #f1f5f9; }
.form-group { margin-bottom: 1.5rem; }
.form-group label { display: block; font-weight: 600; color: #334155; margin-bottom: 0.5rem; }
.required { color: #ef4444; }
.form-select, .form-textarea { width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-size: 0.95rem; outline: none; }
.form-textarea { resize: vertical; min-height: 100px; }
.form-hint { font-size: 0.75rem; color: #64748b; margin-top: 0.25rem; }
.form-actions { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #f1f5f9; }
.btn-secondary { padding: 0.75rem 1.5rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.5rem; cursor: pointer; font-weight: 600; color: #475569; }
.btn-primary { padding: 0.75rem 1.5rem; background: #1e40af; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 600; }
.btn-primary:disabled { opacity: 0.7; cursor: not-allowed; }
</style>
