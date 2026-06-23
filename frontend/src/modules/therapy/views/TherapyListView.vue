<template>
  <div class="page-container">
    <div class="page-header">
      <button @click="goBack" class="btn-back">← Kembali ke Dashboard</button>
      <h1 class="page-title">Program Terapi</h1>
      <button v-if="authStore.isDokter" @click="openCreateModal" class="btn-primary">+ Program Terapi Baru</button>
    </div>

    <!-- Filters -->
    <div class="filters-card">
      <div class="filters-grid">
        <div class="filter-group">
          <input type="text" v-model="searchQuery" placeholder="Cari nama terapi atau deskripsi..." class="form-input" @input="debouncedSearch" />
        </div>
        <div class="filter-group">
          <select v-model="statusFilter" @change="applyFilters" class="form-select">
            <option value="">Semua Status</option>
            <option value="terjadwal">Terjadwal</option>
            <option value="berjalan">Berjalan</option>
            <option value="selesai">Selesai</option>
            <option value="dihentikan">Dihentikan</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="therapyStore.loading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Memuat data terapi...</p>
    </div>

    <!-- Therapy Cards Grid -->
    <div v-else-if="therapyStore.therapies.length > 0" class="therapy-grid">
      <div v-for="therapy in therapyStore.therapies" :key="therapy.id" class="therapy-card">
        <div class="therapy-header">
          <h3>{{ therapy.pasien?.nama }}</h3>
          <span :class="['status-badge', therapy.status]">{{ therapy.status }}</span>
        </div>
        <div class="therapy-body">
          <div class="therapy-type">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px; display: inline-block; vertical-align: text-bottom; margin-right: 0.25rem; color: #4f46e5;">
              <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
            </svg>
            {{ therapy.nama_terapi }}
          </div>
          <div class="therapy-schedule">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px; display: inline-block; vertical-align: text-bottom; margin-right: 0.25rem; color: #64748b;">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="16" y1="2" x2="16" y2="6"></line>
              <line x1="8" y1="2" x2="8" y2="6"></line>
              <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            {{ formatDate(therapy.jadwal?.mulai) }} - {{ therapy.jadwal?.selesai ? formatDate(therapy.jadwal?.selesai) : 'Berlanjut' }}
          </div>
          <div class="therapy-frequency" v-if="therapy.rencana">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px; display: inline-block; vertical-align: text-bottom; margin-right: 0.25rem; color: #64748b;">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            {{ therapy.rencana.frekuensi_per_minggu }}x/minggu, {{ therapy.rencana.durasi_hari }} hari
          </div>
          <div v-if="therapy.terapis" class="therapy-therapist">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px; display: inline-block; vertical-align: text-bottom; margin-right: 0.25rem; color: #0f766e;">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
            {{ therapy.terapis.nama }}
          </div>
        </div>
        <div class="therapy-actions">
          <button @click="viewDetail(therapy.id)" class="btn-secondary">Detail</button>
          <button v-if="canEdit" @click="editTherapy(therapy.id)" class="btn-secondary">Edit</button>
          <button v-if="canDelete" @click="confirmDelete(therapy.id)" class="btn-delete">Hapus</button>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="therapyStore.pagination.last_page > 1" class="pagination">
        <button @click="changePage(therapyStore.pagination.current_page - 1)" :disabled="therapyStore.pagination.current_page === 1" class="btn-pagination">← Prev</button>
        <span class="pagination-info">Halaman {{ therapyStore.pagination.current_page }} dari {{ therapyStore.pagination.last_page }}</span>
        <button @click="changePage(therapyStore.pagination.current_page + 1)" :disabled="therapyStore.pagination.current_page === therapyStore.pagination.last_page" class="btn-pagination">Next →</button>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="empty-state">
      <div class="empty-icon" style="display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; color: #94a3b8;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 48px; height: 48px;">
          <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
        </svg>
      </div>
      <h3>Belum Ada Program Terapi</h3>
      <p>Belum ada program terapi yang tercatat</p>
      <button v-if="authStore.isDokter" @click="openCreateModal" class="btn-primary">+ Buat Program Pertama</button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useTherapyStore } from '../stores/therapyStore'
import { useAuthStore } from '../../auth/stores/authStore'

const router = useRouter()
const therapyStore = useTherapyStore()
const authStore = useAuthStore()

const searchQuery = ref('')
const statusFilter = ref('')
let searchTimeout = null

const therapies = computed(() => therapyStore.therapies)
const canEdit = computed(() => authStore.isDokter || authStore.isAdmin || authStore.isTerapis)
const canDelete = computed(() => authStore.isAdmin)

const goBack = () => router.push('/dashboard')
const openCreateModal = () => alert('Fitur Buat Program Terapi akan segera hadir')
const viewDetail = (id) => alert(`Detail Terapi ID: ${id}`)
const editTherapy = (id) => alert(`Edit Terapi ID: ${id}`)

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => applyFilters(), 500)
}

const applyFilters = () => {
  const params = {}
  if (searchQuery.value) params.search = searchQuery.value
  if (statusFilter.value) params.status = statusFilter.value
  therapyStore.fetchTherapies(params)
}

const confirmDelete = async (id) => {
  if (confirm('Yakin ingin menghapus program terapi ini?')) {
    const result = await therapyStore.deleteTherapy(id)
    if (result.success) alert('Program terapi berhasil dihapus')
  }
}

const changePage = (page) => {
  if (page >= 1 && page <= therapyStore.pagination.last_page) {
    therapyStore.fetchTherapies({ page })
  }
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

onMounted(() => {
  therapyStore.fetchTherapies()
})
</script>

<style scoped>
.page-container { padding: 1.5rem; max-width: 1400px; margin: 0 auto; }
.page-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem; }
.btn-back { padding: 0.5rem 1rem; background: transparent; border: 1px solid #e2e8f0; border-radius: 0.5rem; cursor: pointer; font-size: 0.875rem; color: #64748b; }
.page-title { flex: 1; font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 0; }
.btn-primary { padding: 0.625rem 1.25rem; background: #1e40af; color: white; border: none; border-radius: 0.5rem; font-weight: 600; cursor: pointer; font-size: 0.875rem; }
.filters-card { background: white; padding: 1.25rem; border-radius: 0.75rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.filters-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; }
.form-input, .form-select { padding: 0.625rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-size: 0.875rem; outline: none; }
.loading-container { text-align: center; padding: 3rem; }
.loading-spinner { width: 40px; height: 40px; border: 3px solid #f1f5f9; border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { to { transform: rotate(360deg); } }
.therapy-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.25rem; }
.therapy-card { background: white; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #f1f5f9; transition: transform 0.2s; }
.therapy-card:hover { transform: translateY(-4px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.therapy-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid #f8fafc; }
.therapy-header h3 { font-size: 1rem; color: #1e293b; margin: 0; font-weight: 700; }
.status-badge { padding: 0.125rem 0.625rem; border-radius: 9999px; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; }
.status-badge.terjadwal { background: #f1f5f9; color: #475569; }
.status-badge.berjalan { background: #dcfce7; color: #15803d; }
.status-badge.selesai { background: #e0f2fe; color: #0369a1; }
.status-badge.dihentikan { background: #fee2e2; color: #b91c1c; }
.therapy-body { display: flex; flex-direction: column; gap: 0.625rem; margin-bottom: 1.25rem; }
.therapy-type { font-weight: 600; color: #1e40af; font-size: 0.875rem; }
.therapy-schedule, .therapy-frequency, .therapy-therapist { font-size: 0.8125rem; color: #64748b; display: flex; align-items: center; gap: 0.5rem; }
.therapy-actions { display: flex; gap: 0.5rem; }
.btn-secondary { flex: 1; padding: 0.5rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.5rem; cursor: pointer; font-size: 0.75rem; font-weight: 600; color: #475569; }
.btn-secondary:hover { background: #f1f5f9; }
.btn-delete { padding: 0.5rem; background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; border-radius: 0.5rem; cursor: pointer; font-size: 0.75rem; font-weight: 600; }
.btn-delete:hover { background: #ffe4e6; }
.pagination { display: flex; justify-content: center; align-items: center; gap: 1rem; padding: 1.5rem 0; }
.btn-pagination { padding: 0.5rem 0.875rem; background: white; border: 1px solid #e2e8f0; border-radius: 0.375rem; cursor: pointer; font-size: 0.8125rem; }
.btn-pagination:disabled { opacity: 0.5; cursor: not-allowed; }
.pagination-info { font-size: 0.8125rem; color: #64748b; }
.empty-state { text-align: center; padding: 4rem 2rem; background: white; border-radius: 0.75rem; border: 1px solid #f1f5f9; }
.empty-icon { font-size: 3rem; margin-bottom: 1rem; opacity: 0.5; }
</style>
