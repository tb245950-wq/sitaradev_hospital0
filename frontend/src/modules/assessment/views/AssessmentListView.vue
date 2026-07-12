<template>
  <div class="page-container">
    <!-- Header -->
    <div class="page-header">
      <button @click="goBack" class="btn-back">
        <span class="arrow">←</span>
        <span>Kembali ke Dashboard</span>
      </button>
      <h1 class="page-title">Assessment Medis</h1>
      <div class="page-actions">
        <button v-if="authStore.isDokter" @click="openCreateModal" class="btn-primary">
          + Assessment Baru
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters-card">
      <div class="filters-grid">
        <div class="filter-group">
          <label>Pencarian</label>
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Cari nama pasien, diagnosis..."
            @input="debouncedSearch"
            class="form-input"
          />
        </div>
        <div class="filter-group">
          <label>Status</label>
          <select v-model="statusFilter" @change="applyFilters" class="form-select">
            <option value="">Semua Status</option>
            <option value="draft">Draft</option>
            <option value="final">Final</option>
          </select>
        </div>
        <div class="filter-group filter-actions">
          <button @click="resetFilters" class="btn-secondary">Reset Filter</button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="assessmentStore.loading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Memuat data assessment...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="assessmentStore.error" class="error-container">
      <p>{{ assessmentStore.error }}</p>
      <button @click="assessmentStore.fetchAssessments()" class="btn-retry">Coba Lagi</button>
    </div>

    <!-- Assessment Table -->
    <div v-else-if="assessmentStore.assessments.length > 0" class="table-card">
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>Pasien</th>
              <th>Diagnosis</th>
              <th>Hasil Fisik</th>
              <th>Status</th>
              <th>Dokter</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="assessment in assessmentStore.assessments" :key="assessment.id">
              <td>{{ formatDate(assessment.tanggal) }}</td>
              <td>
                <div class="patient-info">
                  <div class="patient-name">{{ assessment.pasien?.nama }}</div>
                  <div class="patient-nik">NRM: {{ assessment.pasien?.nrm }}</div>
                </div>
              </td>
              <td class="diagnosis-cell">{{ assessment.diagnosis }}</td>
              <td>
                <div class="truncate-text" v-if="assessment.hasil_fisik">
                  T: {{ assessment.hasil_fisik.tensi || '-' }}, N: {{ assessment.hasil_fisik.nadi || '-' }}
                </div>
                <span v-else>-</span>
              </td>
              <td>
                <span :class="['status-badge', `status-${assessment.status || 'draft'}`]">
                  {{ assessment.status || 'draft' }}
                </span>
              </td>
              <td>{{ assessment.dokter?.nama }}</td>
              <td>
                <div class="action-buttons" style="display: flex; gap: 0.5rem;">
                  <button @click="viewDetail(assessment.id)" class="btn-icon-sm" title="Lihat Detail" style="color: #64748b;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                      <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                  </button>
                  <button v-if="canEdit(assessment)" @click="editAssessment(assessment.id)" class="btn-icon-sm" title="Edit" style="color: #3b82f6;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;">
                      <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                      <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                  </button>
                  <button v-if="authStore.isDokter && (assessment.status || 'draft') === 'draft'" @click="submitAssessment(assessment.id)" class="btn-icon-sm" title="Finalisasi Assessment" style="color: #10b981;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;">
                      <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                  </button>
                  <button v-if="canDelete(assessment)" @click="confirmDelete(assessment.id)" class="btn-icon-sm btn-delete" title="Hapus" style="color: #ef4444; border: 1px solid #fee2e2; background: #fef2f2;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;">
                      <polyline points="3 6 5 6 21 6"></polyline>
                      <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                      <line x1="10" y1="11" x2="10" y2="17"></line>
                      <line x1="14" y1="11" x2="14" y2="17"></line>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="assessmentStore.pagination.last_page > 1" class="pagination">
        <button @click="changePage(assessmentStore.pagination.current_page - 1)" :disabled="assessmentStore.pagination.current_page === 1" class="btn-pagination">← Prev</button>
        <span class="pagination-info">Halaman {{ assessmentStore.pagination.current_page }} dari {{ assessmentStore.pagination.last_page }}</span>
        <button @click="changePage(assessmentStore.pagination.current_page + 1)" :disabled="assessmentStore.pagination.current_page === assessmentStore.pagination.last_page" class="btn-pagination">Next →</button>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="empty-state">
      <div class="empty-icon" style="display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; color: #94a3b8;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 48px; height: 48px;">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
          <polyline points="14 2 14 8 20 8"></polyline>
          <line x1="16" y1="13" x2="8" y2="13"></line>
          <line x1="16" y1="17" x2="8" y2="17"></line>
        </svg>
      </div>
      <h3>Belum Ada Assessment</h3>
      <p>Belum ada data assessment yang tercatat</p>
      <button v-if="authStore.isDokter" @click="openCreateModal" class="btn-primary">+ Buat Assessment Pertama</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAssessmentStore } from '../stores/assessmentStore'
import { useAuthStore } from '../../auth/stores/authStore'
import { useNotificationStore } from '../../../shared/stores/notificationStore'

const router = useRouter()
const assessmentStore = useAssessmentStore()
const authStore = useAuthStore()
const notify = useNotificationStore()
const searchQuery = ref('')
const statusFilter = ref('')
let searchTimeout = null

const goBack = () => router.push('/dashboard')

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => applyFilters(), 500)
}

const applyFilters = () => {
  const params = {}
  if (searchQuery.value) params.search = searchQuery.value
  if (statusFilter.value) params.status = statusFilter.value
  assessmentStore.fetchAssessments(params)
}

const resetFilters = () => {
  searchQuery.value = ''
  statusFilter.value = ''
  assessmentStore.fetchAssessments()
}

const openCreateModal = () => router.push('/assessments/create')
const viewDetail = (id) => router.push(`/assessments/${id}`)
const editAssessment = (id) => router.push(`/assessments/${id}?edit=1`)

const submitAssessment = async (id) => {
  if (confirm('Submit assessment ini?')) {
    const result = await assessmentStore.submitAssessment(id)
    if (!result.success) alert(result.error || 'Gagal submit')
  }
}

const confirmDelete = async (id) => {
  if (confirm('Yakin ingin menghapus assessment ini?')) {
    const result = await assessmentStore.deleteAssessment(id)
    if (!result.success) alert(result.error || 'Gagal menghapus')
  }
}

const canEdit = (assessment) => {
  if (authStore.isAdmin) return true
  // Dokter hanya bisa edit assessment miliknya sendiri yang masih draft
  if (authStore.isDokter) return (assessment.status || 'draft') === 'draft' && assessment.dokter?.id === authStore.user?.id
  return false
}

const canDelete = (assessment) => {
  if (authStore.isAdmin) return true
  // Dokter hanya bisa hapus assessment miliknya sendiri yang masih draft
  if (authStore.isDokter) return (assessment.status || 'draft') === 'draft' && assessment.dokter?.id === authStore.user?.id
  return false
}

const changePage = (page) => {
  if (page >= 1 && page <= assessmentStore.pagination.last_page) {
    assessmentStore.fetchAssessments({ page })
  }
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

onMounted(() => {
  assessmentStore.fetchAssessments()
})
</script>

<style scoped>
.page-container { padding: 1.5rem; max-width: 1400px; margin: 0 auto; }
.page-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0; }
.btn-back { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.75rem; background: transparent; color: #64748b; border: 1px solid #e2e8f0; border-radius: 0.5rem; cursor: pointer; font-size: 0.875rem; }
.page-title { flex: 1; font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 0; }
.btn-primary { padding: 0.625rem 1.25rem; background: #1e40af; color: white; border: none; border-radius: 0.5rem; font-weight: 600; cursor: pointer; font-size: 0.875rem; }
.filters-card { background: white; padding: 1.25rem; border-radius: 0.75rem; margin-bottom: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.filters-grid { display: grid; grid-template-columns: 2fr 1fr auto; gap: 1rem; align-items: flex-end; }
.filter-group { display: flex; flex-direction: column; gap: 0.375rem; }
.filter-group label { font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; }
.form-input, .form-select { padding: 0.625rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-size: 0.875rem; outline: none; }
.btn-secondary { padding: 0.625rem 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.5rem; cursor: pointer; font-size: 0.875rem; color: #475569; }
.loading-container, .error-container { text-align: center; padding: 3rem; background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.loading-spinner { width: 40px; height: 40px; border: 3px solid #f1f5f9; border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { to { transform: rotate(360deg); } }
.table-card { background: white; border-radius: 0.75rem; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.table-responsive { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { background: #f8fafc; padding: 0.875rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; }
.data-table td { padding: 1rem; border-bottom: 1px solid #f1f5f9; font-size: 0.875rem; color: #334155; }
.patient-info { display: flex; flex-direction: column; }
.patient-name { font-weight: 600; color: #1e293b; }
.patient-nik { font-size: 0.75rem; color: #94a3b8; }
.diagnosis-cell { max-width: 250px; }
.truncate-text { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.status-badge { padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; }
.status-draft { background: #f1f5f9; color: #475569; }
.status-submitted { background: #fef3c7; color: #92400e; }
.status-approved { background: #dcfce7; color: #166534; }
.status-final { background: #dcfce7; color: #166534; }
.action-buttons { display: flex; gap: 0.375rem; }
.btn-icon-sm { background: #f8fafc; border: 1px solid #e2e8f0; cursor: pointer; padding: 0.375rem; border-radius: 0.375rem; font-size: 1rem; display: flex; align-items: center; justify-content: center; }
.btn-icon-sm:hover { background: #f1f5f9; }
.btn-delete { color: #ef4444; }
.pagination { display: flex; justify-content: center; align-items: center; gap: 1rem; padding: 1rem; border-top: 1px solid #f1f5f9; }
.btn-pagination { padding: 0.5rem 0.875rem; background: white; border: 1px solid #e2e8f0; border-radius: 0.375rem; cursor: pointer; font-size: 0.8125rem; }
.btn-pagination:disabled { opacity: 0.5; cursor: not-allowed; }
.pagination-info { font-size: 0.8125rem; color: #64748b; }
.empty-state { text-align: center; padding: 4rem 2rem; background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.empty-icon { font-size: 3rem; margin-bottom: 1rem; }
.empty-state h3 { font-size: 1.125rem; color: #1e293b; margin: 0 0 0.5rem 0; }
.empty-state p { color: #64748b; margin-bottom: 1.5rem; font-size: 0.875rem; }
</style>
