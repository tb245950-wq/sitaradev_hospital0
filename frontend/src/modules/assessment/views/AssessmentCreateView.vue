<template>
  <div class="page-container">
    <div class="page-header">
      <button @click="goBack" class="btn-back">← Batal</button>
      <h1 class="page-title">Buat Assessment Baru</h1>
    </div>

    <form @submit.prevent="handleSubmit" class="form-card">
      <div class="form-section">
        <h3>Informasi Pasien</h3>
        
        <div class="form-group">
          <label>Pasien <span class="required">*</span></label>
          <select v-model="form.id_pasien" required class="form-select">
            <option value="">Pilih Pasien</option>
            <option v-for="patient in todayPatients" :key="patient.id" :value="patient.id">
              {{ patient.nama }} - {{ patient.nrm }}
            </option>
          </select>
          <small class="hint">Pasien yang datang hari ini (dari antrian)</small>
        </div>
        
        <div class="form-group">
          <label>Tanggal Assessment <span class="required">*</span></label>
          <input v-model="form.tanggal_assessment" type="date" required class="form-input" />
        </div>
      </div>

      <div class="form-section">
        <h3>Pemeriksaan</h3>
        
        <div class="form-group">
          <label>Anamnesis (Keluhan Utama) <span class="required">*</span></label>
          <textarea v-model="form.keluhan_utama" required rows="3" class="form-textarea" placeholder="Deskripsikan keluhan utama pasien..."></textarea>
        </div>
        
        <div class="form-group">
          <label>Riwayat Penyakit</label>
          <textarea v-model="form.riwayat_penyakit" rows="3" class="form-textarea" placeholder="Riwayat penyakit terdahulu..."></textarea>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Tensi (mmHg) <span class="required">*</span></label>
            <input v-model="form.hasil_pemeriksaan.tensi" type="text" required class="form-input" placeholder="120/80" />
          </div>
          <div class="form-group">
            <label>Nadi (bpm) <span class="required">*</span></label>
            <input v-model="form.hasil_pemeriksaan.nadi" type="text" required class="form-input" placeholder="80" />
          </div>
          <div class="form-group">
            <label>Suhu (°C) <span class="required">*</span></label>
            <input v-model="form.hasil_pemeriksaan.suhu" type="text" required class="form-input" placeholder="36.5" />
          </div>
        </div>
      </div>

      <div class="form-section">
        <h3>Diagnosis & Rencana</h3>
        
        <div class="form-group">
          <label>Diagnosis <span class="required">*</span></label>
          <input v-model="form.diagnosis" type="text" required class="form-input" placeholder="Contoh: Gangguan Spektrum Autisme" />
        </div>
        
        <div class="form-group">
          <label>Rencana Terapi</label>
          <textarea v-model="form.rencana_terapi" rows="3" class="form-textarea" placeholder="Rencana program terapi selanjutnya..."></textarea>
        </div>
        
        <div class="form-group">
          <label>Catatan Medis</label>
          <textarea v-model="form.catatan_medis" rows="2" class="form-textarea" placeholder="Catatan internal dokter..."></textarea>
        </div>
      </div>

      <div class="form-actions">
        <button type="button" @click="goBack" class="btn-secondary">Batal</button>
        <button type="submit" :disabled="loading" class="btn-primary">
          {{ loading ? 'Menyimpan...' : 'Simpan Assessment' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAssessmentStore } from '../stores/assessmentStore'
import { queueService } from '../../queue/services/queueService'

const router = useRouter()
const assessmentStore = useAssessmentStore()

const loading = ref(false)
const todayPatients = ref([])

const form = ref({
  id_pasien: '',
  tanggal_assessment: new Date().toISOString().split('T')[0],
  keluhan_utama: '',
  riwayat_penyakit: '',
  hasil_pemeriksaan: {
    tensi: '',
    nadi: '',
    suhu: '',
    berat_badan: '',
    tinggi_badan: ''
  },
  diagnosis: '',
  rencana_terapi: '',
  catatan_medis: '',
  status: 'draft'
})

const goBack = () => router.push('/assessments')

const fetchTodayPatients = async () => {
  try {
    // Ambil semua antrian hari ini (tanpa filter jenis_layanan)
    // agar semua pasien yang datang ke klinik bisa di-assessment
    const today = new Date().toISOString().split('T')[0]
    const response = await queueService.getQueues({
      tanggal: today
    })

    // Ekstrak pasien unik dari antrian hari ini
    const patientsMap = new Map()
    const items = response.data ?? response
    items.forEach(q => {
      if (q.pasien && q.pasien.id) {
        patientsMap.set(q.pasien.id, q.pasien)
      }
    })

    todayPatients.value = Array.from(patientsMap.values())
  } catch (error) {
    console.error('Error fetching today patients:', error)
  }
}

const handleSubmit = async () => {
  loading.value = true
  try {
    // Bersihkan field numerik yang kosong agar tidak gagal validasi 'numeric'
    const payload = {
      ...form.value,
      hasil_pemeriksaan: {
        ...form.value.hasil_pemeriksaan,
        berat_badan: form.value.hasil_pemeriksaan.berat_badan !== ''
          ? Number(form.value.hasil_pemeriksaan.berat_badan)
          : undefined,
        tinggi_badan: form.value.hasil_pemeriksaan.tinggi_badan !== ''
          ? Number(form.value.hasil_pemeriksaan.tinggi_badan)
          : undefined,
      }
    }
    // Hapus key undefined agar tidak dikirim ke backend
    if (payload.hasil_pemeriksaan.berat_badan === undefined) delete payload.hasil_pemeriksaan.berat_badan
    if (payload.hasil_pemeriksaan.tinggi_badan === undefined) delete payload.hasil_pemeriksaan.tinggi_badan

    const result = await assessmentStore.createAssessment(payload)
    if (result.success) {
      alert('Assessment berhasil dibuat')
      router.push('/assessments')
    } else {
      alert('Gagal: ' + result.error)
    }
  } catch (error) {
    alert('Terjadi kesalahan')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchTodayPatients()
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
.form-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
.form-actions { display: flex; justify-content: flex-end; gap: 1rem; padding-top: 1.5rem; border-top: 1px solid #f1f5f9; }
.btn-secondary { padding: 0.75rem 1.5rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.5rem; cursor: pointer; color: #475569; }
.btn-primary { padding: 0.75rem 1.5rem; background: #1e40af; color: white; border: none; border-radius: 0.5rem; font-weight: 600; }
.btn-primary:disabled { opacity: 0.7; cursor: not-allowed; }
</style>
