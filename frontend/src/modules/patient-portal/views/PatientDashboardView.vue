<template>
  <div class="patient-dashboard">
    <!-- Sidebar Pasien -->
    <aside class="patient-sidebar">
      <div class="sidebar-header">
        <img src="../../../../assets/SITARA_RM_BG.png" alt="SITARA" class="logo" />
        <div>
          <h2>SITARA</h2>
          <p>Portal Pasien</p>
        </div>
      </div>

      <nav class="sidebar-nav">
        <router-link to="/pasien/dashboard" class="nav-item" active-class="active">
          <span class="icon">📊</span>
          <span>Dashboard</span>
        </router-link>
        <router-link to="/pasien/booking" class="nav-item" active-class="active">
          <span class="icon">📅</span>
          <span>Booking Antrian</span>
        </router-link>
        <router-link to="/pasien/antrian-saya" class="nav-item" active-class="active">
          <span class="icon">🎫</span>
          <span>Antrian Saya</span>
        </router-link>
        <router-link to="/pasien/jadwal" class="nav-item" active-class="active">
          <span class="icon">📆</span>
          <span>Jadwal Terapi</span>
        </router-link>
        <router-link to="/pasien/riwayat" class="nav-item" active-class="active">
          <span class="icon">📋</span>
          <span>Riwayat Medis</span>
        </router-link>
        <router-link to="/pasien/profil" class="nav-item" active-class="active">
          <span class="icon">👤</span>
          <span>Profil Saya</span>
        </router-link>
      </nav>

      <div class="sidebar-footer">
        <div class="user-info">
          <div class="user-avatar">{{ userInitials }}</div>
          <div class="user-details">
            <div class="user-name">{{ patientStore.user?.name }}</div>
            <div class="user-role">Pasien</div>
          </div>
        </div>
        <button @click="handleLogout" class="btn-logout">🚪 Logout</button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <div class="content-header">
        <div>
          <h1>Selamat Datang, {{ patientStore.user?.name }}!</h1>
          <p>Akses layanan klinik SITARA dari portal pasien Anda</p>
        </div>
      </div>

      <!-- Stat Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon" style="background: #dbeafe; color: #1e40af;">📅</div>
          <div class="stat-info">
            <h3>Antrian Aktif</h3>
            <p class="stat-value">{{ stats.activeQueue || 0 }}</p>
            <small>{{ stats.queueNumber || 'Tidak ada' }}</small>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background: #dcfce7; color: #166534;">📆</div>
          <div class="stat-info">
            <h3>Jadwal Terapi</h3>
            <p class="stat-value">{{ stats.upcomingTherapy || 0 }}</p>
            <small>Bulan ini</small>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background: #fef3c7; color: #92400e;">📋</div>
          <div class="stat-info">
            <h3>Assessment</h3>
            <p class="stat-value">{{ stats.totalAssessment || 0 }}</p>
            <small>Total dilakukan</small>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background: #f3e8ff; color: #7c3aed;">🧠</div>
          <div class="stat-info">
            <h3>Program Terapi</h3>
            <p class="stat-value">{{ stats.activeTherapy || 0 }}</p>
            <small>Sedang berjalan</small>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="quick-actions">
        <h2 class="section-title">Aksi Cepat</h2>
        <div class="actions-grid">
          <router-link to="/pasien/booking" class="action-card primary">
            <div class="action-icon">📅</div>
            <div class="action-info">
              <h3>Booking Antrian</h3>
              <p>Daftar antrian untuk konsultasi hari ini</p>
            </div>
          </router-link>

          <router-link to="/pasien/antrian-saya" class="action-card success">
            <div class="action-icon">🎫</div>
            <div class="action-info">
              <h3>Cek Antrian Saya</h3>
              <p>Lihat status antrian Anda</p>
            </div>
          </router-link>

          <router-link to="/pasien/jadwal" class="action-card warning">
            <div class="action-icon">📆</div>
            <div class="action-info">
              <h3>Jadwal Terapi</h3>
              <p>Lihat jadwal terapi berikutnya</p>
            </div>
          </router-link>

          <router-link to="/pasien/riwayat" class="action-card info">
            <div class="action-icon">📋</div>
            <div class="action-info">
              <h3>Riwayat Medis</h3>
              <p>Lihat riwayat assessment & terapi</p>
            </div>
          </router-link>
        </div>
      </div>

      <!-- Info Box -->
      <div class="info-banner">
        <div class="info-icon">💡</div>
        <div class="info-content">
          <h3>Tahukah Anda?</h3>
          <p>Anda dapat melakukan booking antrian langsung dari portal ini. Pilih dokter dan poli yang diinginkan, lalu tunggu panggilan dari admin klinik.</p>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { usePatientStore } from '../stores/patientStore'
import { patientService } from '../services/patientService'

const router = useRouter()
const patientStore = usePatientStore()

const stats = ref({
  activeQueue: 0,
  queueNumber: '',
  upcomingTherapy: 0,
  totalAssessment: 0,
  activeTherapy: 0
})

const userInitials = computed(() => {
  const name = patientStore.user?.name || ''
  const parts = name.split(' ')
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase()
  }
  return name.substring(0, 2).toUpperCase()
})

const handleLogout = async () => {
  if (confirm('Yakin ingin keluar?')) {
    await patientStore.logout()
    router.push('/pasien/login')
  }
}

const fetchDashboardStats = async () => {
  try {
    const response = await patientService.getDashboardStats()
    if (response.success) {
      stats.value = { ...stats.value, ...response.data }
    }
  } catch (error) {
    console.error('Error fetching stats:', error)
  }
}

onMounted(() => {
  fetchDashboardStats()
})
</script>

<style scoped>
.patient-dashboard {
  display: flex;
  min-height: 100vh;
  background: #f8fafc;
}

/* Sidebar */
.patient-sidebar {
  width: 260px;
  background: #1e293b;
  color: white;
  display: flex;
  flex-direction: column;
  position: fixed;
  left: 0;
  top: 0;
  height: 100vh;
  z-index: 100;
}

.sidebar-header {
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}
.sidebar-header .logo { width: 40px; height: 40px; }
.sidebar-header h2 { font-size: 1.25rem; margin: 0; }
.sidebar-header p { font-size: 0.75rem; color: #94a3b8; margin: 0; }

.sidebar-nav {
  flex: 1;
  padding: 1rem 0;
  overflow-y: auto;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1.5rem;
  color: #cbd5e1;
  text-decoration: none;
  transition: all 0.2s;
}

.nav-item:hover { background: rgba(255, 255, 255, 0.05); color: white; }
.nav-item.active { background: #10b981; color: white; border-right: 4px solid white; }
.icon { font-size: 1.25rem; }

.sidebar-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.user-avatar {
  width: 40px;
  height: 40px;
  background: #10b981;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
}

.user-name { font-weight: 600; font-size: 0.9rem; }
.user-role { font-size: 0.75rem; color: #94a3b8; }

.btn-logout {
  width: 100%;
  padding: 0.5rem;
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
  border: 1px solid #ef4444;
  border-radius: 0.5rem;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-logout:hover { background: #ef4444; color: white; }

/* Main Content */
.main-content {
  flex: 1;
  margin-left: 260px;
  padding: 2rem;
}

.content-header {
  margin-bottom: 2rem;
}

.content-header h1 {
  font-size: 1.75rem;
  color: #1e293b;
  margin-bottom: 0.25rem;
}

.content-header p { color: #64748b; }

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1.25rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.stat-icon {
  width: 56px;
  height: 56px;
  border-radius: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.stat-info h3 {
  font-size: 0.8rem;
  color: #64748b;
  margin-bottom: 0.25rem;
  font-weight: 500;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
  line-height: 1;
}

.stat-info small { color: #94a3b8; font-size: 0.75rem; }

/* Quick Actions */
.section-title {
  font-size: 1.25rem;
  color: #1e293b;
  margin-bottom: 1rem;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.action-card {
  background: white;
  padding: 1.5rem;
  border-radius: 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  text-decoration: none;
  color: inherit;
  border-left: 4px solid transparent;
  transition: all 0.3s;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.action-card:hover {
  transform: translateX(4px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.action-card.primary { border-left-color: #1e40af; }
.action-card.success { border-left-color: #10b981; }
.action-card.warning { border-left-color: #f59e0b; }
.action-card.info { border-left-color: #8b5cf6; }

.action-icon { font-size: 2rem; }
.action-info h3 { font-size: 1rem; color: #1e293b; margin-bottom: 0.25rem; }
.action-info p { font-size: 0.85rem; color: #64748b; margin: 0; }

/* Info Banner */
.info-banner {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  padding: 2rem;
  border-radius: 1rem;
  display: flex;
  gap: 1.5rem;
  align-items: center;
}

.info-icon { font-size: 3rem; }

.info-content h3 { margin-bottom: 0.5rem; font-size: 1.25rem; }
.info-content p { opacity: 0.95; line-height: 1.6; }

@media (max-width: 768px) {
  .patient-sidebar { transform: translateX(-100%); }
  .main-content { margin-left: 0; }
}
</style>
