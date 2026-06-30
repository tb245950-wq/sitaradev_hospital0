<template>
  <div class="super-admin-dashboard">
    <div class="page-header">
      <h1>Dashboard Super Admin</h1>
      <p class="subtitle">Selamat datang, {{ authStore.user?.name }}. Sistem berjalan normal.</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <p>Memuat data dashboard...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-state">
      <p>⚠️ {{ error }}</p>
      <button @click="loadDashboard" class="btn-retry">Coba Lagi</button>
    </div>

    <!-- Dashboard Content -->
    <div v-else>
      <!-- Stats Grid -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">👥</div>
          <div class="stat-info">
            <div class="stat-value">{{ stats.totalUsers ?? '-' }}</div>
            <div class="stat-label">Total Pengguna</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">✅</div>
          <div class="stat-info">
            <div class="stat-value">{{ stats.activeUsers ?? '-' }}</div>
            <div class="stat-label">Pengguna Aktif</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">⏳</div>
          <div class="stat-info">
            <div class="stat-value">{{ stats.inactiveUsers ?? '-' }}</div>
            <div class="stat-label">Menunggu Aktivasi</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">🏥</div>
          <div class="stat-info">
            <div class="stat-value">{{ stats.totalPolis ?? '-' }}</div>
            <div class="stat-label">Total Poli</div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="quick-actions">
        <h2>Aksi Cepat</h2>
        <div class="actions-grid">
          <router-link to="/super-admin/users" class="action-card">
            <div class="action-icon">👤</div>
            <div class="action-label">Manajemen User</div>
            <div class="action-desc">Kelola akun staff & aktivasi</div>
          </router-link>

          <router-link to="/super-admin/polis" class="action-card">
            <div class="action-icon">🏥</div>
            <div class="action-label">Manajemen Poli</div>
            <div class="action-desc">Tambah & kelola poli klinik</div>
          </router-link>

          <router-link to="/super-admin/audit-logs" class="action-card">
            <div class="action-icon">📋</div>
            <div class="action-label">Log Aktivitas</div>
            <div class="action-desc">Monitor aktivitas sistem</div>
          </router-link>

          <router-link to="/super-admin/settings" class="action-card">
            <div class="action-icon">⚙️</div>
            <div class="action-label">Pengaturan</div>
            <div class="action-desc">Konfigurasi sistem</div>
          </router-link>
        </div>
      </div>

      <!-- System Info -->
      <div class="system-info">
        <h2>Informasi Sistem</h2>
        <div class="info-grid">
          <div class="info-item">
            <span class="info-label">Role Anda</span>
            <span class="info-value badge-super-admin">Super Admin</span>
          </div>
          <div class="info-item">
            <span class="info-label">Status</span>
            <span class="info-value badge-active">Active</span>
          </div>
          <div class="info-item">
            <span class="info-label">Versi Sistem</span>
            <span class="info-value">v1.0.0</span>
          </div>
          <div class="info-item">
            <span class="info-label">Terakhir Login</span>
            <span class="info-value">{{ authStore.user?.last_login_at ? formatDate(authStore.user.last_login_at) : '-' }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../../auth/stores/authStore'
import { superAdminService } from '../services/superAdminService'

const authStore = useAuthStore()
const loading = ref(false)
const error = ref(null)
const stats = ref({
  totalUsers: null,
  activeUsers: null,
  inactiveUsers: null,
  totalPolis: null
})

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  try {
    return new Date(dateStr).toLocaleString('id-ID', {
      day: '2-digit', month: 'short', year: 'numeric',
      hour: '2-digit', minute: '2-digit'
    })
  } catch {
    return dateStr
  }
}

const loadDashboard = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await superAdminService.getDashboard()
    const data = response.data?.data ?? {}
    stats.value = {
      totalUsers: data.total_users ?? data.totalUsers ?? 0,
      activeUsers: data.active_users ?? data.activeUsers ?? 0,
      inactiveUsers: data.inactive_users ?? data.inactiveUsers ?? 0,
      totalPolis: data.total_polis ?? data.totalPolis ?? 0
    }
  } catch (err) {
    console.error('Gagal memuat dashboard super admin:', err)
    error.value = 'Gagal memuat data dashboard. ' + (err.response?.data?.message || err.message)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadDashboard()
})
</script>

<style scoped>
.super-admin-dashboard {
  padding: 1.5rem;
}

.page-header {
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 0.25rem;
}

.subtitle {
  color: #64748b;
  font-size: 0.95rem;
}

/* Loading / Error */
.loading-state, .error-state {
  text-align: center;
  padding: 3rem;
  color: #64748b;
}

.btn-retry {
  margin-top: 1rem;
  padding: 0.5rem 1.5rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  font-size: 0.9rem;
}

/* Stats */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1.25rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  border-radius: 0.75rem;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
  border: 1px solid #e2e8f0;
}

.stat-icon {
  font-size: 2rem;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
}

.stat-label {
  font-size: 0.8rem;
  color: #64748b;
  margin-top: 0.15rem;
}

/* Quick Actions */
.quick-actions {
  margin-bottom: 2rem;
}

.quick-actions h2,
.system-info h2 {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 1rem;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
}

.action-card {
  background: white;
  border-radius: 0.75rem;
  padding: 1.5rem;
  text-align: center;
  text-decoration: none;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
  transition: all 0.2s;
  display: block;
}

.action-card:hover {
  border-color: #3b82f6;
  box-shadow: 0 4px 12px rgba(59,130,246,0.15);
  transform: translateY(-2px);
}

.action-icon {
  font-size: 2rem;
  margin-bottom: 0.75rem;
}

.action-label {
  font-weight: 600;
  color: #1e293b;
  font-size: 0.95rem;
  margin-bottom: 0.25rem;
}

.action-desc {
  font-size: 0.8rem;
  color: #64748b;
}

/* System Info */
.system-info {
  background: white;
  border-radius: 0.75rem;
  padding: 1.5rem;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1rem;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.info-label {
  font-size: 0.75rem;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.info-value {
  font-size: 0.95rem;
  font-weight: 500;
  color: #1e293b;
}

.badge-super-admin {
  display: inline-flex;
  align-items: center;
  background: #fef3c7;
  color: #92400e;
  padding: 0.2rem 0.6rem;
  border-radius: 9999px;
  font-size: 0.8rem;
  font-weight: 600;
}

.badge-active {
  display: inline-flex;
  align-items: center;
  background: #d1fae5;
  color: #065f46;
  padding: 0.2rem 0.6rem;
  border-radius: 9999px;
  font-size: 0.8rem;
  font-weight: 600;
}
</style>
