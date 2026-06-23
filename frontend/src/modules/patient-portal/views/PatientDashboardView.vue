<template>
  <div class="patient-dashboard">
    <!-- Sidebar Pasien -->
    <aside class="patient-sidebar">
      <div class="sidebar-header">
        <img src="../../../assets/SITARA_RM_BG.png" alt="SITARA" class="logo" />
        <div>
          <h2>SITARA</h2>
          <p>Portal Pasien</p>
        </div>
      </div>

      <nav class="sidebar-nav">
        <router-link to="/pasien/dashboard" class="nav-item" active-class="active">
          <span class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="3" width="7" height="7"></rect>
              <rect x="14" y="3" width="7" height="7"></rect>
              <rect x="14" y="14" width="7" height="7"></rect>
              <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
          </span>
          <span>Dashboard</span>
        </router-link>
        <router-link to="/pasien/booking" class="nav-item" active-class="active">
          <span class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="16" y1="2" x2="16" y2="6"></line>
              <line x1="8" y1="2" x2="8" y2="6"></line>
              <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
          </span>
          <span>Booking Antrian</span>
        </router-link>
        <router-link to="/pasien/antrian-saya" class="nav-item" active-class="active">
          <span class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="4" width="18" height="16" rx="2"></rect>
              <line x1="16" y1="2" x2="16" y2="4"></line>
              <line x1="8" y1="2" x2="8" y2="4"></line>
              <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
          </span>
          <span>Antrian Saya</span>
        </router-link>
        <router-link to="/pasien/jadwal" class="nav-item" active-class="active">
          <span class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16"></path>
              <path d="M12 7v5l3 3"></path>
            </svg>
          </span>
          <span>Jadwal Terapi</span>
        </router-link>
        <router-link to="/pasien/riwayat" class="nav-item" active-class="active">
          <span class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
              <line x1="16" y1="13" x2="8" y2="13"></line>
              <line x1="16" y1="17" x2="8" y2="17"></line>
            </svg>
          </span>
          <span>Riwayat Medis</span>
        </router-link>
        <router-link to="/pasien/profil" class="nav-item" active-class="active">
          <span class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
          </span>
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
        <button @click="handleLogout" class="btn-logout">
          <span class="icon btn-logout-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
              <polyline points="16 17 21 12 16 7"></polyline>
              <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
          </span>
          Logout
        </button>
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
          <div class="stat-icon" style="background: #dbeafe; color: #1e40af;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="16" y1="2" x2="16" y2="6"></line>
              <line x1="8" y1="2" x2="8" y2="6"></line>
              <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
          </div>
          <div class="stat-info">
            <h3>Antrian Aktif</h3>
            <p class="stat-value">{{ stats.activeQueue || 0 }}</p>
            <small>{{ stats.queueNumber || 'Tidak ada' }}</small>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background: #dcfce7; color: #166534;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16"></path>
              <path d="M12 7v5l3 3"></path>
            </svg>
          </div>
          <div class="stat-info">
            <h3>Jadwal Terapi</h3>
            <p class="stat-value">{{ stats.upcomingTherapy || 0 }}</p>
            <small>Bulan ini</small>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background: #fef3c7; color: #92400e;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
              <line x1="16" y1="13" x2="8" y2="13"></line>
              <line x1="16" y1="17" x2="8" y2="17"></line>
            </svg>
          </div>
          <div class="stat-info">
            <h3>Assessment</h3>
            <p class="stat-value">{{ stats.totalAssessment || 0 }}</p>
            <small>Total dilakukan</small>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon" style="background: #f3e8ff; color: #7c3aed;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
            </svg>
          </div>
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
            <div class="action-icon">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
              </svg>
            </div>
            <div class="action-info">
              <h3>Booking Antrian</h3>
              <p>Daftar antrian untuk konsultasi hari ini</p>
            </div>
          </router-link>

          <router-link to="/pasien/antrian-saya" class="action-card success">
            <div class="action-icon">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                <line x1="16" y1="2" x2="16" y2="4"></line>
                <line x1="8" y1="2" x2="8" y2="4"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
              </svg>
            </div>
            <div class="action-info">
              <h3>Cek Antrian Saya</h3>
              <p>Lihat status antrian Anda</p>
            </div>
          </router-link>

          <router-link to="/pasien/jadwal" class="action-card warning">
            <div class="action-icon">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16"></path>
                <path d="M12 7v5l3 3"></path>
              </svg>
            </div>
            <div class="action-info">
              <h3>Jadwal Terapi</h3>
              <p>Lihat jadwal terapi berikutnya</p>
            </div>
          </router-link>

          <router-link to="/pasien/riwayat" class="action-card info">
            <div class="action-icon">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
              </svg>
            </div>
            <div class="action-info">
              <h3>Riwayat Medis</h3>
              <p>Lihat riwayat assessment & terapi</p>
            </div>
          </router-link>
        </div>
      </div>

      <!-- Info Box -->
      <div class="info-banner">
        <div class="info-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A5 5 0 0 0 8 8c0 1 .5 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"></path>
            <path d="M9 18h6"></path>
            <path d="M10 22h4"></path>
          </svg>
        </div>
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

.icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
  color: #94a3b8;
}

.nav-item:hover .icon,
.nav-item.active .icon {
  color: white;
}

.icon svg {
  width: 100%;
  height: 100%;
}

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
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: all 0.2s;
}

.btn-logout:hover { background: #ef4444; color: white; }
.btn-logout-icon {
  color: inherit;
  width: 16px;
  height: 16px;
}

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
}

.stat-icon svg {
  width: 28px;
  height: 28px;
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

.action-card.primary { border-left-color: #1e40af; color: #1e40af; }
.action-card.success { border-left-color: #10b981; color: #10b981; }
.action-card.warning { border-left-color: #f59e0b; color: #f59e0b; }
.action-card.info { border-left-color: #8b5cf6; color: #8b5cf6; }

.action-icon {
  width: 42px;
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.action-icon svg {
  width: 100%;
  height: 100%;
}

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

.info-icon {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: white;
}
.info-icon svg {
  width: 100%;
  height: 100%;
}

.info-content h3 { margin-bottom: 0.5rem; font-size: 1.25rem; }
.info-content p { opacity: 0.95; line-height: 1.6; }

@media (max-width: 768px) {
  .patient-sidebar { transform: translateX(-100%); }
  .main-content { margin-left: 0; }
}
</style>
