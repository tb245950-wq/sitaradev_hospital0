<template>
  <div class="page-container">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Daftar Pasien</h1>
        <p class="page-subtitle">Kelola data rekam medis anak</p>
      </div>
      
      <!-- Action Buttons -->
      <div class="page-actions">
        <router-link 
          v-if="canManagePatients" 
          to="/patients/create" 
          class="btn-primary"
        >
          <span class="btn-icon">+</span>
          Tambah Pasien
        </router-link>
        <button @click="refreshData" class="btn-secondary">
          🔄 Refresh
        </button>
      </div>
    </div>

    <!-- RBAC Info Notice (untuk Terapis) -->
    <div v-if="authStore.isTerapis" class="rbac-notice">
      <span class="icon">ℹ️</span>
      <span><strong>Mode Lihat Saja:</strong> Terapis tidak dapat menambah atau mengubah data induk pasien.</span>
    </div>

    <!-- Filters -->
    <div class="filters-card">
      <div class="filters-grid">
        <div class="filter-group">
          <label>Cari Pasien</label>
          <div class="search-input-wrapper">
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Cari nama, NIK, atau nomor rekam medis..."
              @input="debouncedSearch"
              class="form-input"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content (Table) -->
    <div class="content-card">
      <div class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>No. RM</th>
              <th>Nama Lengkap</th>
              <th>NIK</th>
              <th>Jenis Kelamin</th>
              <th>Tanggal Lahir</th>
              <th>Nama Orang Tua</th>
              <th class="text-right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="patientStore.loading">
              <td colspan="7" class="text-center py-8">
                <div class="loading-spinner"></div>
                <p>Memuat data...</p>
              </td>
            </tr>
            <tr v-else-if="patientStore.patients.length === 0">
              <td colspan="7" class="text-center py-8">
                <p class="text-gray-500">Tidak ada data pasien ditemukan</p>
              </td>
            </tr>
            <tr v-for="patient in patientStore.patients" :key="patient.id">
              <td><strong>{{ patient.nrm }}</strong></td>
              <td>{{ patient.nama }}</td>
              <td>{{ patient.nik || '-' }}</td>
              <td>{{ patient.jenis_kelamin }}</td>
              <td>{{ formatDate(patient.info_lahir?.tanggal) }}</td>
              <td>{{ patient.wali?.nama || '-' }}</td>
              <td class="text-right">
                <div class="action-buttons justify-end">
                  <router-link :to="`/patients/${patient.id}`" class="btn-icon-sm" title="Detail">
                    👁️
                  </router-link>
                  <template v-if="canManagePatients">
                    <router-link :to="`/patients/${patient.id}/edit`" class="btn-icon-sm" title="Edit">
                      ✏️
                    </router-link>
                    <button @click="confirmDelete(patient)" class="btn-icon-sm text-red-500" title="Hapus">
                      🗑️
                    </button>
                  </template>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="patientStore.pagination.last_page > 1" class="pagination">
        <div class="pagination-info">
          Menampilkan {{ patientStore.patients.length }} dari {{ patientStore.pagination.total }} pasien
        </div>
        <div class="pagination-controls">
          <button 
            @click="changePage(patientStore.pagination.current_page - 1)"
            :disabled="patientStore.pagination.current_page === 1"
            class="btn-pagination"
          >
            ← Prev
          </button>
          <span class="pagination-page">
            Halaman {{ patientStore.pagination.current_page }} dari {{ patientStore.pagination.last_page }}
          </span>
          <button 
            @click="changePage(patientStore.pagination.current_page + 1)"
            :disabled="patientStore.pagination.current_page === patientStore.pagination.last_page"
            class="btn-pagination"
          >
            Next →
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../../auth/stores/authStore'
import { usePatientStore } from '../stores/patientStore'

const authStore = useAuthStore()
const patientStore = usePatientStore()

const searchQuery = ref('')
let searchTimeout = null

// RBAC Permissions
const canManagePatients = computed(() => {
  return authStore.isAdmin || authStore.isDokter
})

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    patientStore.setFilters({ search: searchQuery.value })
    patientStore.fetchPatients(1)
  }, 500)
}

const refreshData = () => {
  patientStore.fetchPatients(patientStore.pagination.current_page)
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric'
  })
}

const changePage = (page) => {
  patientStore.fetchPatients(page)
}

const confirmDelete = async (patient) => {
  if (confirm(`Apakah Anda yakin ingin menghapus data pasien ${patient.nama}? Tindakan ini tidak dapat dibatalkan.`)) {
    const result = await patientStore.deletePatient(patient.id)
    if (result.success) {
      // Success handled by store
    } else {
      alert(result.error)
    }
  }
}

onMounted(() => {
  patientStore.fetchPatients()
})
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
  margin-top: 0.25rem;
}

.page-actions {
  display: flex;
  gap: 0.75rem;
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
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 1.5rem;
}

.form-input {
  width: 100%;
  max-width: 400px;
  padding: 0.625rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 0.875rem;
}

.form-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.content-card {
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
  text-align: left;
  padding: 1rem 1.5rem;
  background: #f8fafc;
  color: #64748b;
  font-weight: 600;
  font-size: 0.875rem;
  border-bottom: 1px solid #e2e8f0;
}

.data-table td {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
  font-size: 0.875rem;
  color: #334155;
}

.btn-primary {
  background: #2563eb;
  color: white;
  padding: 0.625rem 1.25rem;
  border-radius: 0.5rem;
  text-decoration: none;
  font-weight: 600;
  font-size: 0.875rem;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  transition: background 0.2s;
}

.btn-primary:hover {
  background: #1d4ed8;
}

.btn-secondary {
  background: white;
  color: #475569;
  padding: 0.625rem 1.25rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-secondary:hover {
  background: #f8fafc;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.btn-icon-sm {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 0.375rem;
  cursor: pointer;
  width: 2rem;
  height: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  text-decoration: none;
  transition: all 0.2s;
}

.btn-icon-sm:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
}

.pagination-info {
  font-size: 0.875rem;
  color: #64748b;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.pagination-page {
  font-size: 0.875rem;
  font-weight: 500;
  color: #1e293b;
}

.btn-pagination {
  padding: 0.5rem 0.75rem;
  border: 1px solid #e2e8f0;
  background: white;
  border-radius: 0.375rem;
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 500;
  color: #475569;
}

.btn-pagination:hover:not(:disabled) {
  background: #f8fafc;
}

.btn-pagination:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.text-right { text-align: right; }
.justify-end { justify-content: flex-end; }
.text-red-500 { color: #ef4444; }
.py-8 { padding-top: 2rem; padding-bottom: 2rem; }
.text-center { text-align: center; }

.loading-spinner {
  border: 3px solid #f3f3f3;
  border-top: 3px solid #3b82f6;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
