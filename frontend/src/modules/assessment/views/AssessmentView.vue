<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <button @click="goToDashboard" class="btn-back">
          <span class="arrow">←</span>
          <span>Kembali ke Dashboard</span>
        </button>
        <h1 class="page-title">Assessment Medis</h1>
        <p class="page-subtitle">Diagnosis dan rekomendasi awal pasien</p>
      </div>
      <div class="page-actions">
        <button 
          v-if="authStore.isDokter" 
          @click="openCreateModal" 
          class="btn-primary"
        >
          + Assessment Baru
        </button>
        <button @click="refreshData" class="btn-secondary">
          🔄 Refresh
        </button>
      </div>
    </div>

    <!-- RBAC Info Notice (untuk Admin) -->
    <div v-if="authStore.isAdmin" class="rbac-notice">
      <span class="icon">ℹ️</span>
      <span><strong>Mode Lihat Saja:</strong> Admin hanya dapat melihat dokumen assessment tanpa izin untuk mengubah data medis.</span>
    </div>

    <!-- Filters -->
    <div class="filters-card">
      <div class="filters-grid">
        <div class="filter-group">
          <label>Cari Pasien / Diagnosis</label>
          <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="Ketik nama pasien atau kata kunci diagnosis..."
            class="form-input"
          />
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="content-card">
      <div class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>Pasien</th>
              <th>NRM</th>
              <th>Diagnosis</th>
              <th>Rekomendasi</th>
              <th class="text-right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="assessmentStore.loading">
              <td colspan="6" class="text-center py-8">Memuat data assessment...</td>
            </tr>
            <tr v-else-if="filteredAssessments.length === 0">
              <td colspan="6" class="text-center py-8">Belum ada data assessment.</td>
            </tr>
            <tr v-for="a in filteredAssessments" :key="a.id">
              <td>{{ formatDate(a.tanggal_assessment) }}</td>
              <td><strong>{{ a.patient?.nama }}</strong></td>
              <td>{{ a.patient?.nrm }}</td>
              <td><span class="diagnosis-text">{{ a.diagnosis || '-' }}</span></td>
              <td><span class="truncate">{{ a.rekomendasi_terapi || '-' }}</span></td>
              <td class="text-right">
                <div class="action-buttons justify-end">
                  <button @click="viewDetail(a)" class="btn-icon-sm" title="Lihat Detail">👁️</button>
                  <template v-if="authStore.isDokter">
                    <button @click="editAssessment(a)" class="btn-icon-sm" title="Edit">✏️</button>
                    <button @click="deleteAssessment(a)" class="btn-icon-sm text-red-500" title="Hapus">🗑️</button>
                  </template>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../auth/stores/authStore'
import { useAssessmentStore } from '../stores/assessmentStore'
import { useNavigation } from '../../../shared/composables/useNavigation'

const router = useRouter()
const authStore = useAuthStore()
const assessmentStore = useAssessmentStore()
const { goToDashboard } = useNavigation()

const searchQuery = ref('')

onMounted(() => {
  if (authStore.isTerapis) {
    router.push('/unauthorized')
    return
  }
  refreshData()
})

const refreshData = () => {
  assessmentStore.fetchAssessments()
}

const filteredAssessments = computed(() => {
  if (!searchQuery.value) return assessmentStore.assessments
  const q = searchQuery.value.toLowerCase()
  return assessmentStore.assessments.filter(a => 
    a.patient?.nama.toLowerCase().includes(q) || 
    a.diagnosis?.toLowerCase().includes(q)
  )
})

const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

const openCreateModal = () => alert('Buka Modal Assessment Baru')
const viewDetail = (a) => alert(`Detail Assessment: ${a.patient?.nama}`)
const editAssessment = (a) => alert(`Edit Assessment ID: ${a.id}`)
const deleteAssessment = (a) => {
  if (confirm('Hapus data assessment ini?')) {
    alert('Menghapus...')
  }
}
</script>

<style scoped>
.page-container {
  padding: 2rem;
  max-width: 1400px;
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

.rbac-notice {
  background: #eff6ff;
  border: 1px solid #3b82f6;
  border-left: 4px solid #3b82f6;
  padding: 1rem 1.5rem;
  border-radius: 0.5rem;
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.filters-card {
  background: white;
  padding: 1.5rem;
  border-radius: 0.75rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  margin-bottom: 1.5rem;
}

.form-input {
  width: 100%;
  max-width: 400px;
  padding: 0.625rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
}

.content-card {
  background: white;
  border-radius: 1rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  overflow: hidden;
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
  background: #f8fafc;
}

.data-table td {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
  font-size: 0.875rem;
}

.diagnosis-text {
  font-weight: 600;
  color: #3b82f6;
}

.truncate {
  display: block;
  max-width: 250px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

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
