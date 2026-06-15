<template>
  <div class="assessment-list-container">
    <!-- Header Section -->
    <div class="header-section">
      <div class="title-group">
        <h1 class="title">Daftar Assessment</h1>
        <p class="subtitle">Manajemen data assessment medis pasien</p>
      </div>
      <div class="action-group">
        <button 
          v-if="canCreate" 
          @click="handleCreate" 
          class="btn-primary"
        >
          <span class="icon">+</span> Assessment Baru
        </button>
        <button @click="refreshData" class="btn-outline">
          <span class="icon">🔄</span> Refresh
        </button>
      </div>
    </div>

    <!-- Alert Success/Error -->
    <div v-if="successMessage" class="alert alert-success">
      {{ successMessage }}
      <button @click="successMessage = ''" class="close-btn">&times;</button>
    </div>
    <div v-if="assessmentStore.error" class="alert alert-error">
      {{ assessmentStore.error }}
      <button @click="assessmentStore.error = null" class="close-btn">&times;</button>
    </div>

    <!-- Filters & Search -->
    <div class="filters-card">
      <div class="filters-grid">
        <div class="search-input-wrapper">
          <span class="search-icon">🔍</span>
          <input 
            type="text" 
            v-model="filters.search" 
            @input="handleSearch"
            placeholder="Cari NRM atau nama pasien..."
            class="form-input"
          />
        </div>
        
        <div class="filter-select-wrapper">
          <select v-model="filters.status" @change="handleFilterChange" class="form-select">
            <option value="">Semua Status</option>
            <option value="draft">Draft</option>
            <option value="final">Final</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Data Table -->
    <div class="table-card">
      <div class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Tanggal</th>
              <th>Pasien</th>
              <th>NRM</th>
              <th>Diagnosis</th>
              <th>Status</th>
              <th class="text-right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <!-- Loading State -->
            <tr v-if="assessmentStore.loading">
              <td colspan="7" class="loading-cell">
                <div class="spinner"></div>
                <span>Memuat data...</span>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-else-if="assessmentStore.assessments.length === 0">
              <td colspan="7" class="empty-cell">
                Data assessment tidak ditemukan.
              </td>
            </tr>

            <!-- Data Rows -->
            <tr v-for="assessment in assessmentStore.assessments" :key="assessment.id_assessment">
              <td class="id-cell">#{{ assessment.id_assessment }}</td>
              <td>{{ formatDate(assessment.tanggal_assessment) }}</td>
              <td class="font-bold">{{ assessment.patient?.nama_lengkap || 'N/A' }}</td>
              <td><span class="nrm-badge">{{ assessment.patient?.nrm || '-' }}</span></td>
              <td class="diagnosis-cell">
                <div class="truncate-text" :title="assessment.diagnosis">
                  {{ assessment.diagnosis || '-' }}
                </div>
              </td>
              <td>
                <span :class="['status-badge', `status-${assessment.status}`]">
                  {{ assessment.status }}
                </span>
              </td>
              <td class="text-right">
                <div class="btn-group-actions">
                  <button @click="handleView(assessment)" class="btn-icon" title="Lihat">👁️</button>
                  <button 
                    v-if="canEdit" 
                    @click="handleEdit(assessment)" 
                    class="btn-icon" 
                    title="Edit"
                  >✏️</button>
                  <button 
                    v-if="canDelete" 
                    @click="confirmDelete(assessment)" 
                    class="btn-icon text-red" 
                    title="Hapus"
                  >🗑️</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="pagination-footer" v-if="assessmentStore.pagination.last_page > 1">
        <div class="pagination-info">
          Menampilkan {{ assessmentStore.assessments.length }} dari {{ assessmentStore.pagination.total }} data
        </div>
        <div class="pagination-controls">
          <button 
            :disabled="assessmentStore.pagination.current_page === 1"
            @click="changePage(assessmentStore.pagination.current_page - 1)"
            class="btn-page"
          >
            &laquo; Prev
          </button>
          
          <button 
            v-for="page in visiblePages" 
            :key="page"
            @click="changePage(page)"
            :class="['btn-page', { active: assessmentStore.pagination.current_page === page }]"
          >
            {{ page }}
          </button>

          <button 
            :disabled="assessmentStore.pagination.current_page === assessmentStore.pagination.last_page"
            @click="changePage(assessmentStore.pagination.current_page + 1)"
            class="btn-page"
          >
            Next &raquo;
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useAssessmentStore } from '../stores/assessmentStore'
import { useAuthStore } from '../../auth/stores/authStore'
import { useRouter } from 'vue-router'

const assessmentStore = useAssessmentStore()
const authStore = useAuthStore()
const router = useRouter()

const filters = reactive({
  search: '',
  status: '',
  page: 1
})

const successMessage = ref('')
let searchTimeout = null

// RBAC Computed Properties
const canCreate = computed(() => authStore.userRole === 'dokter' || authStore.userRole === 'admin')
const canEdit = computed(() => authStore.userRole === 'dokter')
const canDelete = computed(() => authStore.userRole === 'admin')

onMounted(() => {
  fetchData()
})

const fetchData = () => {
  assessmentStore.fetchAssessments(filters)
}

const refreshData = () => {
  fetchData()
}

const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    filters.page = 1
    fetchData()
  }, 500)
}

const handleFilterChange = () => {
  filters.page = 1
  fetchData()
}

const changePage = (page) => {
  filters.page = page
  fetchData()
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

const handleCreate = () => {
  // router.push('/assessment/create')
  alert('Fitur Create sedang dikembangkan')
}

const handleView = (item) => {
  // router.push(`/assessment/${item.id_assessment}`)
  alert(`Detail Assessment Pasien: ${item.patient?.nama_lengkap}`)
}

const handleEdit = (item) => {
  // router.push(`/assessment/${item.id_assessment}/edit`)
  alert(`Edit Assessment ID: ${item.id_assessment}`)
}

const confirmDelete = async (item) => {
  if (confirm(`Apakah Anda yakin ingin menghapus assessment untuk ${item.patient?.nama_lengkap}?`)) {
    const result = await assessmentStore.deleteAssessment(item.id_assessment)
    if (result.success) {
      successMessage.value = 'Assessment berhasil dihapus'
      setTimeout(() => successMessage.value = '', 3000)
    }
  }
}

// Pagination Logic
const visiblePages = computed(() => {
  const current = assessmentStore.pagination.current_page
  const last = assessmentStore.pagination.last_page
  const pages = []
  
  let start = Math.max(1, current - 2)
  let end = Math.min(last, current + 2)
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  return pages
})
</script>

<style scoped>
.assessment-list-container {
  padding: 1.5rem;
}

.header-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.subtitle {
  color: #64748b;
  font-size: 0.875rem;
  margin: 0.25rem 0 0 0;
}

.action-group {
  display: flex;
  gap: 0.75rem;
}

/* UI Components */
.btn-primary {
  background: #3b82f6;
  color: white;
  padding: 0.625rem 1.25rem;
  border-radius: 0.5rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-outline {
  background: white;
  color: #64748b;
  padding: 0.625rem 1.25rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
}

.filters-card {
  background: white;
  padding: 1rem;
  border-radius: 0.75rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 1rem;
}

.filters-grid {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.search-input-wrapper {
  position: relative;
  flex: 1;
}

.search-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
}

.form-input {
  width: 100%;
  padding: 0.625rem 1rem 0.625rem 2.5rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  outline: none;
}

.form-select {
  padding: 0.625rem 2rem 0.625rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  background-color: white;
  outline: none;
}

/* Table Styles */
.table-card {
  background: white;
  border-radius: 0.75rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.table-container {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  background: #f8fafc;
  text-align: left;
  padding: 1rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  border-bottom: 1px solid #e2e8f0;
}

.data-table td {
  padding: 1rem;
  border-bottom: 1px solid #f1f5f9;
  font-size: 0.875rem;
  color: #334155;
}

.id-cell {
  color: #94a3b8;
  font-weight: 500;
}

.nrm-badge {
  background: #f1f5f9;
  padding: 0.25rem 0.5rem;
  border-radius: 0.375rem;
  font-family: monospace;
  font-weight: 600;
}

.diagnosis-cell {
  max-width: 200px;
}

.truncate-text {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.status-badge {
  padding: 0.25rem 0.625rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
}

.status-draft { background: #f1f5f9; color: #475569; }
.status-final { background: #dcfce7; color: #166534; }

.btn-icon {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  padding: 0.375rem;
  border-radius: 0.375rem;
  cursor: pointer;
  margin-left: 0.25rem;
}

.btn-icon:hover {
  background: #f1f5f9;
}

.text-red { color: #ef4444; }
.text-right { text-align: right; }
.font-bold { font-weight: 600; }

/* Pagination */
.pagination-footer {
  padding: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 1px solid #f1f5f9;
}

.pagination-info {
  font-size: 0.875rem;
  color: #64748b;
}

.pagination-controls {
  display: flex;
  gap: 0.25rem;
}

.btn-page {
  padding: 0.5rem 0.75rem;
  border: 1px solid #e2e8f0;
  background: white;
  border-radius: 0.375rem;
  cursor: pointer;
  font-size: 0.875rem;
}

.btn-page.active {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

.btn-page:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Alerts */
.alert {
  padding: 1rem;
  border-radius: 0.5rem;
  margin-bottom: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

.close-btn {
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  line-height: 1;
}

/* Loading Spinner */
.loading-cell {
  text-align: center;
  padding: 4rem !important;
  color: #64748b;
}

.spinner {
  width: 24px;
  height: 24px;
  border: 3px solid #f1f5f9;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  display: inline-block;
  margin-right: 0.75rem;
  vertical-align: middle;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
