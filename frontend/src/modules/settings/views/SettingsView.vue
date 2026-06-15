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
          <span class="nav-icon">{{ item.icon }}</span>
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
                <div class="icon">💾</div>
                <h4>Backup Database</h4>
                <p>Unduh salinan data sistem saat ini dalam format .sql</p>
                <button class="btn-secondary">Mulai Backup</button>
              </div>
              <div class="action-card">
                <div class="icon">📂</div>
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
  { id: 'general', icon: '⚙️', label: 'Umum' },
  { id: 'backup', icon: '💾', label: 'Backup & Restore' },
  { id: 'logs', icon: '📜', label: 'Log Aktivitas' }
]

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
