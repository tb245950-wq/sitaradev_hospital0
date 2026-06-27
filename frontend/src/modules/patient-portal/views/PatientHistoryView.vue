<template>
  <div class="patient-page">
    <div class="page-header">
      <h1>📋 Riwayat Medis</h1>
      <p>Riwayat assessment dan program terapi Anda.</p>
    </div>

    <div v-if="loading" class="loading-state">Memuat riwayat medis...</div>

    <div v-else-if="error" class="error-state">
      <p>{{ error }}</p>
      <button @click="loadHistory" class="btn-retry">Coba Lagi</button>
    </div>

    <div v-else>
      <!-- Assessments -->
      <section class="history-section">
        <h2>Assessment Medis</h2>
        <div v-if="assessments.length === 0" class="empty-state">Belum ada data assessment.</div>
        <div v-else class="history-list">
          <div v-for="item in assessments" :key="item.id" class="history-card">
            <div class="card-header">
              <span class="badge badge-assessment">Assessment</span>
              <span class="date">{{ formatDate(item.created_at) }}</span>
            </div>
            <div class="card-body">
              <p><strong>Diagnosis:</strong> {{ item.diagnosis || '-' }}</p>
              <p><strong>ICD-10:</strong> {{ item.icd10_code || '-' }}</p>
              <p><strong>Dokter:</strong> {{ item.dokter?.name || '-' }}</p>
              <p v-if="item.catatan_medis"><strong>Catatan:</strong> {{ item.catatan_medis }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Therapies -->
      <section class="history-section">
        <h2>Program Terapi</h2>
        <div v-if="therapies.length === 0" class="empty-state">Belum ada program terapi.</div>
        <div v-else class="history-list">
          <div v-for="item in therapies" :key="item.id" class="history-card">
            <div class="card-header">
              <span class="badge badge-therapy">Terapi</span>
              <span :class="['status-badge', `status-${item.status}`]">{{ item.status }}</span>
              <span class="date">{{ formatDate(item.created_at) }}</span>
            </div>
            <div class="card-body">
              <p><strong>Jenis Terapi:</strong> {{ item.jenis_terapi || '-' }}</p>
              <p><strong>Terapis:</strong> {{ item.terapis?.name || '-' }}</p>
              <p><strong>Total Sesi:</strong> {{ item.total_sesi || 0 }}</p>
              <p><strong>Sesi Selesai:</strong> {{ item.sesi_selesai || 0 }}</p>
            </div>
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
const assessments = ref([])
const therapies = ref([])

function formatDate(dateStr) {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
}

async function loadHistory() {
  loading.value = true
  error.value = null
  try {
    const result = await patientService.getMedicalHistory()
    if (result.success) {
      assessments.value = result.data?.assessments || []
      therapies.value = result.data?.therapies || []
    } else {
      error.value = result.error || 'Gagal memuat riwayat.'
    }
  } catch (e) {
    error.value = 'Terjadi kesalahan saat memuat data.'
  } finally {
    loading.value = false
  }
}

onMounted(loadHistory)
</script>

<style scoped>
.patient-page { padding: 2rem; max-width: 900px; }
.page-header { margin-bottom: 2rem; }
.page-header h1 { font-size: 1.5rem; font-weight: 700; color: #1e293b; }
.page-header p { color: #64748b; margin-top: 0.25rem; }
.loading-state, .empty-state { color: #94a3b8; text-align: center; padding: 2rem; }
.error-state { text-align: center; padding: 2rem; color: #ef4444; }
.btn-retry { margin-top: 1rem; padding: 0.5rem 1.5rem; background: #1a73e8; color: white; border: none; border-radius: 0.5rem; cursor: pointer; }
.history-section { margin-bottom: 2rem; }
.history-section h2 { font-size: 1.1rem; font-weight: 600; color: #374151; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #e5e7eb; }
.history-list { display: flex; flex-direction: column; gap: 1rem; }
.history-card { background: white; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border: 1px solid #f1f5f9; }
.card-header { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; flex-wrap: wrap; }
.date { margin-left: auto; font-size: 0.8rem; color: #9ca3af; }
.badge { font-size: 0.75rem; padding: 0.2rem 0.6rem; border-radius: 9999px; font-weight: 600; }
.badge-assessment { background: #dbeafe; color: #1e40af; }
.badge-therapy { background: #fef3c7; color: #92400e; }
.status-badge { font-size: 0.75rem; padding: 0.2rem 0.6rem; border-radius: 9999px; }
.status-aktif { background: #dcfce7; color: #166534; }
.status-selesai { background: #f1f5f9; color: #64748b; }
.status-pending { background: #fefce8; color: #713f12; }
.card-body { display: flex; flex-direction: column; gap: 0.35rem; }
.card-body p { font-size: 0.875rem; color: #4b5563; margin: 0; }
</style>
