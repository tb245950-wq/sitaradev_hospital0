<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <button @click="goToDashboard" class="btn-back">
          <span class="arrow">←</span>
          <span>Kembali ke Dashboard</span>
        </button>
        <h1 class="page-title">Program Terapi</h1>
        <p class="page-subtitle">Rencana dan jadwal terapi pasien</p>
      </div>
      <div class="page-actions">
        <button 
          v-if="authStore.isDokter" 
          @click="openCreateModal" 
          class="btn-primary"
        >
          + Program Baru
        </button>
        <button @click="refreshData" class="btn-secondary">
          🔄 Refresh
        </button>
      </div>
    </div>

    <!-- RBAC Info Notice (untuk Admin) -->
    <div v-if="authStore.isAdmin" class="rbac-notice">
      <span class="icon">ℹ️</span>
      <span><strong>Mode Lihat Saja:</strong> Admin hanya dapat melihat rencana terapi tanpa izin untuk mengubah data medis.</span>
    </div>

    <!-- Filters -->
    <div class="filters-card">
      <div class="filters-grid">
        <div class="filter-group">
          <label>Cari Pasien / Jenis Terapi</label>
          <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="Ketik nama pasien atau jenis terapi..."
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
              <th>Pasien</th>
              <th>Jenis Terapi</th>
              <th>Jadwal</th>
              <th>Progress</th>
              <th>Status</th>
              <th class="text-right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="therapyStore.loading">
              <td colspan="6" class="text-center py-8">Memuat data terapi...</td>
            </tr>
            <tr v-else-if="filteredTherapies.length === 0">
              <td colspan="6" class="text-center py-8">Belum ada program terapi.</td>
            </tr>
            <tr v-for="t in filteredTherapies" :key="t.id">
              <td>
                <div class="patient-info">
                  <strong>{{ t.patient?.nama }}</strong>
                  <span class="text-xs text-gray-500">{{ t.patient?.nrm }}</span>
                </div>
              </td>
              <td>{{ t.jenis_terapi }}</td>
              <td>{{ t.jadwal_rutin || 'Belum diatur' }}</td>
              <td>
                <div class="progress-container">
                  <div class="progress-bar">
                    <div class="progress-fill" :style="{ width: t.progress + '%' }"></div>
                  </div>
                  <span class="progress-text">{{ t.progress || 0 }}%</span>
                </div>
              </td>
              <td>
                <span :class="['status-pill', t.status]">
                  {{ t.status }}
                </span>
              </td>
              <td class="text-right">
                <div class="action-buttons justify-end">
                  <button @click="viewDetail(t)" class="btn-icon-sm" title="Lihat Detail">👁️</button>
                  
                  <!-- Update Progress - Dokter & Terapis -->
                  <button 
                    v-if="authStore.isDokter || authStore.isTerapis" 
                    @click="updateProgress(t)" 
                    class="btn-icon-sm" 
                    title="Update Progress"
                  >
                    📈
                  </button>
                  
                  <!-- Edit/Delete - Dokter Only -->
                  <template v-if="authStore.isDokter">
                    <button @click="editTherapy(t)" class="btn-icon-sm" title="Edit">✏️</button>
                    <button @click="deleteTherapy(t)" class="btn-icon-sm text-red-500" title="Hapus">🗑️</button>
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
import { useTherapyStore } from '../stores/therapyStore'
import { useNavigation } from '../../../shared/composables/useNavigation'

const router = useRouter()
const authStore = useAuthStore()
const therapyStore = useTherapyStore()
const { goToDashboard } = useNavigation()

const searchQuery = ref('')

onMounted(() => {
  refreshData()
})

const refreshData = () => {
  therapyStore.fetchTherapies()
}

const filteredTherapies = computed(() => {
  if (!searchQuery.value) return therapyStore.therapies
  const q = searchQuery.value.toLowerCase()
  return therapyStore.therapies.filter(t => 
    t.patient?.nama.toLowerCase().includes(q) || 
    t.jenis_terapi?.toLowerCase().includes(q)
  )
})

const openCreateModal = () => alert('Buka Modal Program Terapi Baru')
const viewDetail = (t) => alert(`Detail Terapi: ${t.patient?.nama}`)
const updateProgress = (t) => alert(`Update Progress: ${t.patient?.nama}`)
const editTherapy = (t) => alert(`Edit Program Terapi ID: ${t.id}`)
const deleteTherapy = (t) => {
  if (confirm('Hapus program terapi ini?')) {
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

.patient-info {
  display: flex;
  flex-direction: column;
}

.progress-container {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  max-width: 150px;
}

.progress-bar {
  flex: 1;
  height: 8px;
  background: #f1f5f9;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: #3b82f6;
  border-radius: 4px;
}

.progress-text {
  font-size: 0.75rem;
  font-weight: 600;
  color: #64748b;
  min-width: 30px;
}

.status-pill {
  padding: 0.25rem 0.75rem;
  border-radius: 1rem;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
}

.status-pill.aktif { background: #dcfce7; color: #166534; }
.status-pill.selesai { background: #f1f5f9; color: #64748b; }

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
.text-xs { font-size: 0.75rem; }
.text-gray-500 { color: #6b7280; }
</style>
