<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <button @click="goToDashboard" class="btn-back">
          <span class="arrow">←</span>
          <span>Kembali ke Dashboard</span>
        </button>
        <h1 class="page-title">Pengaturan Sistem</h1>
        <p class="page-subtitle">Konfigurasi aplikasi dan pemeliharaan data</p>
      </div>
    </div>

    <div class="settings-grid">
      <!-- Sidebar Nav -->
      <div class="settings-nav">
        <button 
          v-for="item in navItems" 
          :key="item.id"
          @click="activeSection = item.id"
          :class="['nav-item', { active: activeSection === item.id }]"
        >
          <span class="nav-icon" v-html="iconSvgs[item.icon]" style="display: inline-flex; align-items: center;"></span>
          {{ item.label }}
        </button>
      </div>

      <!-- Main Content -->
      <div class="settings-content">
        <div class="content-card">
          <div v-if="activeSection === 'general'" class="settings-section">
            <h2 class="section-title">Informasi Umum</h2>
            <div class="form-grid">
              <div class="form-group">
                <label>Nama Aplikasi</label>
                <input type="text" value="SITARA" class="form-input" />
              </div>
              <div class="form-group">
                <label>Instansi</label>
                <input type="text" value="RSUD Kota Tangerang" class="form-input" />
              </div>
            </div>
            <div class="form-actions">
              <button class="btn-primary">Simpan Perubahan</button>
            </div>
          </div>

          <div v-if="activeSection === 'backup'" class="settings-section">
            <h2 class="section-title">Database Backup & Restore</h2>
            <div class="backup-actions">
              <div class="action-card">
                <div class="icon" style="color: #3b82f6; display: flex; justify-content: center; margin-bottom: 1rem;">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 36px; height: 36px;">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                  </svg>
                </div>
                <h4>Backup Database</h4>
                <p>Unduh salinan data sistem saat ini dalam format .sql</p>
                <button class="btn-secondary">Mulai Backup</button>
              </div>
              <div class="action-card">
                <div class="icon" style="color: #10b981; display: flex; justify-content: center; margin-bottom: 1rem;">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 36px; height: 36px;">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                  </svg>
                </div>
                <h4>Restore Data</h4>
                <p>Unggah file backup untuk memulihkan data sistem.</p>
                <button class="btn-secondary">Pilih File...</button>
              </div>
            </div>
          </div>

          <div v-if="activeSection === 'logs'" class="settings-section">
            <h2 class="section-title">Log Aktivitas</h2>
            <div class="table-container">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Waktu</th>
                    <th>User</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>15/06/2026 10:20</td>
                    <td>Admin</td>
                    <td>Update data pasien: RM-001</td>
                  </tr>
                </tbody>
              </table>
            </div>
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

const activeSection = ref('general')
const navItems = [
  { id: 'general', icon: 'cog', label: 'Umum' },
  { id: 'backup', icon: 'save', label: 'Backup & Restore' },
  { id: 'logs', icon: 'logs', label: 'Log Aktivitas' }
]

const iconSvgs = {
  cog: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 18px; height: 18px;"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>`,
  save: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 18px; height: 18px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>`,
  logs: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 18px; height: 18px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>`
}

onMounted(() => {
  if (!authStore.isAdmin) {
    router.push('/unauthorized')
  }
})
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

.settings-grid {
  display: grid;
  grid-template-columns: 250px 1fr;
  gap: 2rem;
}

.settings-nav {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1rem;
  border: none;
  background: none;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  text-align: left;
  transition: all 0.2s;
}

.nav-item:hover {
  background: #f1f5f9;
  color: #1e293b;
}

.nav-item.active {
  background: #eff6ff;
  color: #2563eb;
}

.content-card {
  background: white;
  padding: 2rem;
  border-radius: 1rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.section-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 2rem;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
  margin-bottom: 2rem;
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

.backup-actions {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

.action-card {
  border: 1px solid #e2e8f0;
  padding: 1.5rem;
  border-radius: 0.75rem;
  text-align: center;
}

.action-card .icon {
  font-size: 2rem;
  margin-bottom: 1rem;
}

.action-card h4 {
  margin-bottom: 0.5rem;
}

.action-card p {
  font-size: 0.8125rem;
  color: #64748b;
  margin-bottom: 1.5rem;
}

.btn-primary {
  background: #2563eb;
  color: white;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
}

.btn-secondary {
  background: white;
  color: #475569;
  padding: 0.5rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  text-align: left;
  padding: 1rem;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

.data-table td {
  padding: 1rem;
  border-bottom: 1px solid #f1f5f9;
}

@media (max-width: 768px) {
  .settings-grid {
    grid-template-columns: 1fr;
  }
}
</style>
