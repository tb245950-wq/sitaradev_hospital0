<script setup>
import { ref, computed, onMounted, onErrorCaptured } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../auth/stores/authStore'
import { useMonitoringStore } from '../stores/monitoringStore'
import { useNavigation } from '../../../shared/composables/useNavigation'

const router = useRouter()
const authStore = useAuthStore()
const monitoringStore = useMonitoringStore()
const { goToDashboard } = useNavigation()

const searchQuery = ref('')
const error = ref(null)

onErrorCaptured((err) => {
  error.value = 'Terjadi kesalahan pada komponen Monitoring.'
  console.error('MonitoringView Error:', err)
  return false
})

onMounted(() => {
  refreshData()
})

const refreshData = async () => {
  try {
    await monitoringStore.fetchMonitorings()
  } catch (err) {
    error.value = 'Gagal memuat data monitoring.'
  }
}

const filteredMonitorings = computed(() => {
  if (!monitoringStore.monitorings) return []
  const q = searchQuery.value?.toLowerCase() || ''
  return monitoringStore.monitorings.filter(m => 
    m.pasien?.nama?.toLowerCase().includes(q) || 
    m.terapis?.nama?.toLowerCase().includes(q)
  )
})

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' })
}

const openCreateModal = () => alert('Fitur Catat Sesi akan segera hadir')
const viewDetail = (m) => alert(`Detail Monitoring: ${m.pasien?.nama || 'Unknown'}`)

const handleGenerateAssessment = async (therapyId) => {
  if (!therapyId) return alert('Data terapi tidak ditemukan')
  if (confirm('Buat draft assessment medis berdasarkan hasil monitoring ini?')) {
    const result = await monitoringStore.generateAssessment(therapyId)
    if (result.success) {
      alert('Assessment berhasil dibuat (Draft)')
      router.push('/assessments')
    } else {
      alert(result.error)
    }
  }
}

const handleDeleteMonitoring = async (id) => {
  if (confirm('Hapus catatan monitoring ini?')) {
    try {
      await monitoringStore.deleteMonitoring(id)
      alert('Berhasil dihapus')
      refreshData()
    } catch (e) {
      alert('Gagal menghapus')
    }
  }
}
</script>

<template>
  <div v-if="error" class="error-page">{{ error }}</div>
  <div v-else class="page-container">
    <div class="page-header">
      <div>
        <button @click="goToDashboard" class="btn-back">
          <span class="arrow">←</span>
          <span>Kembali ke Dashboard</span>
        </button>
        <h1 class="page-title">Monitoring Progress</h1>
        <p class="page-subtitle">Catatan harian dan perkembangan terapi anak</p>
      </div>
      <div class="page-actions">
        <button 
          v-if="authStore.user?.role === 'dokter' || authStore.user?.role === 'terapis'" 
          @click="openCreateModal" 
          class="btn-primary"
        >
          + Catat Sesi
        </button>
        <button @click="refreshData" class="btn-secondary" style="display: inline-flex; align-items: center; gap: 0.5rem;">
          Refresh
        </button>
      </div>
    </div>

    <!-- RBAC Info Notice (untuk Admin) -->
    <div v-if="authStore.user?.role === 'admin'" class="rbac-notice" style="display: flex; align-items: center; gap: 0.75rem;">
      <span><strong>Mode Lihat Saja:</strong> Admin hanya dapat melihat grafik dan catatan monitoring.</span>
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
                  <td><strong>{{ m.pasien?.nama }}</strong></td>
                  <td>{{ m.terapis?.nama || 'Unknown' }}</td>
                  <td><span class="truncate">{{ m.catatan_perkembangan }}</span></td>
                  <td class="text-right">
                    <div class="action-buttons justify-end" style="display: flex; gap: 0.5rem;">
                      <button @click="viewDetail(m)" class="btn-icon-sm" title="Detail">👁️</button>
                      <button 
                        v-if="authStore.user?.role === 'dokter' || authStore.user?.role === 'admin'" 
                        @click="handleGenerateAssessment(m.therapy?.id)" 
                        class="btn-icon-sm" 
                        title="Generate Assessment"
                      >
                        📄
                      </button>
                      <button 
                        v-if="authStore.user?.role === 'dokter' || authStore.user?.role === 'admin'" 
                        @click="handleDeleteMonitoring(m.id)" 
                        class="btn-icon-sm" 
                        title="Hapus"
                      >
                        🗑️
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page-container { padding: 2rem; max-width: 1400px; margin: 0 auto; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
.page-title { font-size: 1.75rem; font-weight: 700; color: #1e293b; }
.page-subtitle { color: #64748b; }
.rbac-notice { background: #eff6ff; border: 1px solid #3b82f6; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; }
.monitoring-layout { display: grid; grid-template-columns: 1fr; gap: 2rem; }
.filters-card { background: white; padding: 1rem; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 1.5rem; }
.form-input { width: 100%; padding: 0.625rem 1rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; }
.content-card { background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { text-align: left; padding: 1rem 1.5rem; font-size: 0.875rem; color: #64748b; border-bottom: 1px solid #e2e8f0; background: #f8fafc; }
.data-table td { padding: 1rem 1.5rem; border-bottom: 1px solid #f1f5f9; font-size: 0.875rem; }
.truncate { display: block; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.btn-primary { background: #2563eb; color: white; padding: 0.625rem 1.25rem; border-radius: 0.5rem; font-weight: 600; border: none; cursor: pointer; }
.btn-secondary { background: white; color: #475569; padding: 0.625rem 1.25rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-weight: 600; cursor: pointer; }
.btn-icon-sm { background: #f8fafc; border: 1px solid #e2e8f0; width: 32px; height: 32px; border-radius: 0.375rem; cursor: pointer; }
.text-right { text-align: right; }
.justify-end { justify-content: flex-end; }
.text-center { text-align: center; }
.py-8 { padding-top: 2rem; padding-bottom: 2rem; }
</style>
