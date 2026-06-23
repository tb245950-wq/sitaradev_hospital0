<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <button @click="goToDashboard" class="btn-back">
          <span class="arrow">←</span>
          <span>Kembali ke Dashboard</span>
        </button>
        <h1 class="page-title">Laporan Medis</h1>
        <p class="page-subtitle">Analisis dan rekapitulasi data pasien</p>
      </div>
    </div>

    <div class="reports-grid">
      <!-- Report Generator Section -->
      <div class="report-selection-card">
        <h3>Pilih Jenis Laporan</h3>
        <div class="report-types">
          <div 
            v-for="type in reportTypes" 
            :key="type.id"
            :class="['report-type-item', { active: selectedType === type.id }]"
            @click="selectedType = type.id"
          >
            <span class="report-icon" v-html="iconSvgs[type.icon]"></span>
            <div class="report-info">
              <strong>{{ type.label }}</strong>
              <p>{{ type.description }}</p>
            </div>
          </div>
        </div>

        <div class="report-filters">
          <div class="form-group">
            <label>Periode Waktu</label>
            <select v-model="filters.period" class="form-input">
              <option value="today">Hari Ini</option>
              <option value="this_week">Minggu Ini</option>
              <option value="this_month">Bulan Ini</option>
              <option value="custom">Kustom Tanggal</option>
            </select>
          </div>
          
          <div v-if="filters.period === 'custom'" class="date-range">
            <input type="date" v-model="filters.startDate" class="form-input" />
            <span>sampai</span>
            <input type="date" v-model="filters.endDate" class="form-input" />
          </div>

          <button @click="generateReport" class="btn-primary-full">
            Generate Laporan
          </button>
        </div>
      </div>

      <!-- Preview / History Section -->
      <div class="report-history-card">
        <h3>Riwayat Laporan Terakhir</h3>
        <div class="history-list">
          <div v-for="h in history" :key="h.id" class="history-item">
            <div class="history-info">
              <strong>{{ h.name }}</strong>
              <p>{{ h.date }} • {{ h.size }}</p>
            </div>
            <div class="history-actions" style="display: flex; gap: 0.5rem;">
              <button class="btn-icon-sm" style="display: flex; align-items: center; justify-content: center; color: #64748b;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
              </button>
              <button class="btn-icon-sm" style="display: flex; align-items: center; justify-content: center; color: #2563eb;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                  <polyline points="7 10 12 15 17 10"></polyline>
                  <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
              </button>
            </div>
          </div>
          <div v-if="history.length === 0" class="empty-history">
            <p>Belum ada laporan yang di-generate.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../auth/stores/authStore'
import { useNavigation } from '../../../shared/composables/useNavigation'

const router = useRouter()
const authStore = useAuthStore()
const { goToDashboard } = useNavigation()

const selectedType = ref('daily')
const reportTypes = [
  { id: 'daily', icon: 'calendar', label: 'Laporan Harian', description: 'Rekap antrian dan layanan hari ini' },
  { id: 'patient', icon: 'user', label: 'Laporan Per Pasien', description: 'Riwayat lengkap medis satu pasien' },
  { id: 'monthly', icon: 'chart', label: 'Statistik Bulanan', description: 'Tren pertumbuhan dan kunjungan' }
]

const iconSvgs = {
  calendar: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 22px; height: 22px; color: #2563eb;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>`,
  user: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 22px; height: 22px; color: #0f766e;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>`,
  chart: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 22px; height: 22px; color: #7c3aed;"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>`
}

const filters = ref({
  period: 'this_month',
  startDate: '',
  endDate: ''
})

const history = ref([
  { id: 1, name: 'Rekap_Harian_15062026.pdf', date: '15 Jun 2026', size: '1.2 MB' },
  { id: 2, name: 'Laporan_Bulanan_Mei.xlsx', date: '01 Jun 2026', size: '2.5 MB' }
])

onMounted(() => {
  if (authStore.isTerapis) {
    router.push('/unauthorized')
  }
})

const generateReport = () => {
  alert('Menghasilkan laporan... Mohon tunggu.')
}
</script>

<style scoped>
.page-container {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
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

.reports-grid {
  display: grid;
  grid-template-columns: 1fr 400px;
  gap: 2rem;
}

.report-selection-card, .report-history-card {
  background: white;
  padding: 2rem;
  border-radius: 1rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.report-selection-card h3, .report-history-card h3 {
  font-size: 1.125rem;
  font-weight: 700;
  color: #334155;
  margin-bottom: 1.5rem;
}

.report-types {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 2rem;
}

.report-type-item {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  padding: 1.25rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.75rem;
  cursor: pointer;
  transition: all 0.2s;
}

.report-type-item:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
}

.report-type-item.active {
  background: #eff6ff;
  border-color: #3b82f6;
  box-shadow: 0 0 0 1px #3b82f6;
}

.report-icon {
  font-size: 1.5rem;
  width: 48px;
  height: 48px;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.5rem;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.report-info strong {
  display: block;
  font-size: 0.9375rem;
  color: #1e293b;
}

.report-info p {
  font-size: 0.8125rem;
  color: #64748b;
}

.report-filters {
  padding-top: 1.5rem;
  border-top: 1px solid #f1f5f9;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #64748b;
  margin-bottom: 0.5rem;
}

.form-input {
  width: 100%;
  padding: 0.625rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
}

.date-range {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
  font-size: 0.8125rem;
  color: #94a3b8;
}

.btn-primary-full {
  width: 100%;
  padding: 0.875rem;
  background: #2563eb;
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-weight: 700;
  cursor: pointer;
}

.history-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.history-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: #f8fafc;
  border-radius: 0.75rem;
}

.history-info strong {
  display: block;
  font-size: 0.875rem;
  color: #334155;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 200px;
}

.history-info p {
  font-size: 0.75rem;
  color: #94a3b8;
}

.history-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-icon-sm {
  width: 32px;
  height: 32px;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 0.375rem;
  cursor: pointer;
}

.empty-history {
  text-align: center;
  padding: 2rem;
  color: #94a3b8;
  font-size: 0.875rem;
}

@media (max-width: 1024px) {
  .reports-grid {
    grid-template-columns: 1fr;
  }
}
</style>
