<template>
  <div class="patient-list">
    <div class="page-header">
      <div>
        <h1 class="page-title">Daftar Pasien</h1>
        <p class="page-subtitle">Kelola data rekam medis anak</p>
      </div>
      <router-link to="/patients/create" class="btn-primary">
        <span class="btn-icon">+</span>
        Tambah Pasien
      </router-link>
    </div>

    <!-- Filters -->
    <div class="filters-card">
      <div class="filters-grid">
        <div class="filter-group">
          <label>Cari Pasien</label>
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Cari nama atau nomor rekam medis..."
            @input="debouncedSearch"
            class="form-input"
          />
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="table-card">
      <div class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>No. RM</th>
              <th>Nama Lengkap</th>
              <th>Jenis Kelamin</th>
              <th>Tanggal Lahir</th>
              <th>Nama Orang Tua</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="patientStore.loading">
              <td colspan="6" class="text-center">Loading...</td>
            </tr>
            <tr v-else-if="patientStore.patients.length === 0">
              <td colspan="6" class="text-center">Tidak ada data pasien</td>
            </tr>
            <tr v-for="patient in patientStore.patients" :key="patient.id">
              <td><strong>{{ patient.no_rm }}</strong></td>
              <td>{{ patient.nama }}</td>
              <td>{{ patient.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
              <td>{{ formatDate(patient.tanggal_lahir) }}</td>
              <td>{{ patient.nama_orang_tua }}</td>
              <td>
                <div class="action-buttons">
                  <router-link :to="`/patients/${patient.id}`" class="btn-icon-sm" title="Detail">
                    👁️
                  </router-link>
                  <router-link :to="`/patients/${patient.id}/edit`" class="btn-icon-sm" title="Edit">
                    ✏️
                  </router-link>
                  <button @click="confirmDelete(patient)" class="btn-icon-sm" title="Hapus">
                    🗑️
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="patientStore.pagination.last_page > 1" class="pagination">
        <button 
          @click="changePage(patientStore.pagination.current_page - 1)"
          :disabled="patientStore.pagination.current_page === 1"
          class="btn-pagination"
        >
          ← Prev
        </button>
        <span class="pagination-info">
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
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { usePatientStore } from '../stores/patientStore'

const patientStore = usePatientStore()
const searchQuery = ref('')
let searchTimeout = null

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    patientStore.setFilters({ search: searchQuery.value })
    patientStore.fetchPatients(1)
  }, 500)
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
  if (confirm(`Apakah Anda yakin ingin menghapus data pasien ${patient.nama}?`)) {
    const result = await patientStore.deletePatient(patient.id)
    if (result.success) {
      alert('Data pasien berhasil dihapus')
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
.patient-list {
  padding: 2rem;
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

.filters-card {
  background: white;
  padding: 1.5rem;
  border-radius: 0.75rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
}

.form-input {
  width: 100%;
  max-width: 400px;
  padding: 0.75rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
}

.table-card {
  background: white;
  border-radius: 0.75rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  text-align: left;
  padding: 1rem;
  background: #f8fafc;
  color: #64748b;
  font-weight: 600;
  font-size: 0.875rem;
  border-bottom: 1px solid #e2e8f0;
}

.data-table td {
  padding: 1rem;
  border-bottom: 1px solid #f1f5f9;
}

.btn-primary {
  background: #2563eb;
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  text-decoration: none;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.btn-icon-sm {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.25rem;
  font-size: 1.25rem;
  text-decoration: none;
}

.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: #f8fafc;
}

.btn-pagination {
  padding: 0.5rem 1rem;
  border: 1px solid #e2e8f0;
  background: white;
  border-radius: 0.375rem;
  cursor: pointer;
}

.btn-pagination:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
