<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h1 class="page-title">Monitoring Progress</h1>
        <p class="page-subtitle">Catatan harian dan perkembangan terapi anak</p>
      </div>
      <div class="page-actions">
        <button 
          v-if="authStore.isDokter || authStore.isTerapis" 
          @click="openCreateModal" 
          class="btn-primary"
        >
          + Catat Sesi
        </button>
        <button @click="refreshData" class="btn-secondary">
          🔄 Refresh
        </button>
      </div>
    </div>

    <!-- RBAC Info Notice (untuk Admin) -->
    <div v-if="authStore.isAdmin" class="rbac-notice">
      <span class="icon">ℹ️</span>
      <span><strong>Mode Lihat Saja:</strong> Admin hanya dapat melihat grafik dan catatan monitoring tanpa izin untuk mengubah data medis.</span>
    </div>

    <div class="monitoring-layout">
      <!-- Main Content: List Sesi -->
      <div class="monitoring-main">
        <div class="filters-card">
          <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="Cari pasien atau terapis..."
            class="form-input"
          />
        </div>

        <div class="content-card">
          <div class="table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Tanggal</th>
                  <th>Pasien</th>
                  <th>Terapis</th>
                  <th>Catatan</th>
                  <th class="text-right">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="monitoringStore.loading">
                  <td colspan="5" class="text-center py-8">Memuat data monitoring...</td>
                </tr>
                <tr v-else-if="filteredMonitorings.length === 0">
                  <td colspan="5" class="text-center py-8">Belum ada catatan monitoring.</td>
                </tr>
                <tr v-for="m in filteredMonitorings" :key="m.id">
                  <td>{{ formatDate(m.tanggal_sesi) }}</td>
                  <td><strong>{{ m.patient?.nama }}</strong></td>
                  <td>{{ m.user?.name || 'Unknown' }}</td>
                  <td><span class="truncate">{{ m.catatan_progress }}</span></td>
                  <td class="text-right">
                    <div class="action-buttons justify-end">
                      <button @click="viewDetail(m)" class="btn-icon-sm">👁️</button>
                      <template v-if="authStore.isDokter">
                        <button @click="deleteMonitoring(m)" class="btn-icon-sm text-red-500">🗑️</button>
                      </template>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Sidebar: Statistics/Graph Placeholder -->
      <div class="monitoring-sidebar">
        <div class="stats-card">
          <h3>Ringkasan Bulan Ini</h3>
          <div class="stat-row">
            <span>Total Sesi</span>
            <strong>24</strong>
          </div>
          <div class="stat-row">
            <span>Pasien Aktif</span>
            <strong>12</strong>
          </div>
        </div>
        
        <div class="chart-card">
          <h3>Trend Perkembangan</h3>
          <div class="chart-placeholder">
            <!-- Grafik akan diimplementasikan dengan Chart.js -->
            <div class="bar-chart">
              <div class="bar" style="height: 40%"></div>
              <div class="bar" style="height: 60%"></div>
              <div class="bar" style="height: 85%"></div>
              <div class="bar" style="height: 70%"></div>
            </div>
            <p class="text-xs text-gray-400 mt-4 text-center">Data perkembangan 4 minggu terakhir</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../auth/stores/authStore'
import { useMonitoringStore } from '../stores/monitoringStore'

const router = useRouter()
const authStore = useAuthStore()
const monitoringStore = useMonitoringStore()

const searchQuery = ref('')

onMounted(() => {
  refreshData()
})

const refreshData = () => {
  monitoringStore.fetchMonitorings()
}

const filteredMonitorings = computed(() => {
  if (!searchQuery.value) return monitoringStore.monitorings
  const q = searchQuery.value.toLowerCase()
  return monitoringStore.monitorings.filter(m => 
    m.patient?.nama.toLowerCase().includes(q) || 
    m.user?.name.toLowerCase().includes(q)
  )
})

const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' })
}

const openCreateModal = () => alert('Buka Modal Catat Sesi Baru')
const viewDetail = (m) => alert(`Detail Monitoring: ${m.patient?.nama}`)
const deleteMonitoring = (m) => {
  if (confirm('Hapus catatan monitoring ini?')) alert('Menghapus...')
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

.monitoring-layout {
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 2rem;
}

.filters-card {
  background: white;
  padding: 1rem;
  border-radius: 0.75rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  margin-bottom: 1.5rem;
}

.form-input {
  width: 100%;
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

.truncate {
  display: block;
  max-width: 300px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.monitoring-sidebar {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.stats-card, .chart-card {
  background: white;
  padding: 1.5rem;
  border-radius: 1rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.stats-card h3, .chart-card h3 {
  font-size: 1rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 1.25rem;
}

.stat-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.75rem;
  font-size: 0.875rem;
}

.chart-placeholder {
  height: 150px;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
}

.bar-chart {
  display: flex;
  align-items: flex-end;
  gap: 0.75rem;
  height: 100px;
  padding: 0 1rem;
}

.bar {
  flex: 1;
  background: #3b82f6;
  border-radius: 4px 4px 0 0;
  min-height: 10px;
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
.text-xs { font-size: 0.75rem; }
.text-gray-400 { color: #9ca3af; }
.mt-4 { margin-top: 1rem; }
</style>
