<template>
  <div class="patient-dashboard">
    <!-- Sidebar -->
    <aside class="patient-sidebar">
      <div class="sidebar-header">
        <img src="@/assets/SITARA_RM_BG.png" alt="SITARA" class="logo" />
        <div>
          <h2>SITARA</h2>
          <p>Portal Pasien</p>
        </div>
      </div>
      <nav class="sidebar-nav">
        <router-link to="/pasien/dashboard" class="nav-item">Dashboard</router-link>
        <router-link to="/pasien/antrian" class="nav-item">Antrian</router-link>
        <router-link to="/pasien/jadwal" class="nav-item active">Jadwal Terapi</router-link>
        <router-link to="/pasien/riwayat" class="nav-item">Riwayat Medis</router-link>
        <router-link to="/pasien/profil" class="nav-item">Profil Saya</router-link>
      </nav>
      <div class="sidebar-footer">
        <button @click="handleLogout" class="btn-logout">Logout</button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <div class="content-header">
        <button @click="$router.push('/pasien/dashboard')" class="btn-back">← Kembali</button>
        <h1>Jadwal Terapi</h1>
        <p>Sesi terapi yang telah dijadwalkan untuk Anda</p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Memuat jadwal terapi...</p>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="error-message">
        {{ error }}
        <button @click="loadSchedule" class="btn-retry">Coba Lagi</button>
      </div>

      <div v-else>
        <!-- Summary -->
        <div class="summary-grid">
          <div class="summary-card">
            <div class="summary-value">{{ upcoming.length }}</div>
            <div class="summary-label">Sesi Mendatang</div>
          </div>
          <div class="summary-card done">
            <div class="summary-value">{{ done.length }}</div>
            <div class="summary-label">Sudah Selesai</div>
          </div>
          <div class="summary-card absent">
            <div class="summary-value">{{ absent.length }}</div>
            <div class="summary-label">Tidak Hadir</div>
          </div>
        </div>

        <!-- Upcoming Sessions -->
        <section class="section-card">
          <h2 class="section-title">Sesi Mendatang</h2>
          <div v-if="upcoming.length === 0" class="empty-state">
            <p>🗓️</p>
            <p>Tidak ada sesi terapi mendatang</p>
          </div>
          <div v-else class="session-list">
            <div v-for="s in upcoming" :key="s.id" class="session-card upcoming">
              <div class="session-date">
                <span class="date-day">{{ formatDay(s.tanggal_sesi) }}</span>
                <span class="date-month">{{ formatMonth(s.tanggal_sesi) }}</span>
              </div>
              <div class="session-info">
                <div class="session-title">{{ s.jenis_terapi }}</div>
                <div class="session-meta">
                  <span v-if="s.waktu_mulai">🕐 {{ s.waktu_mulai }}{{ s.waktu_selesai ? ' – ' + s.waktu_selesai : '' }}</span>
                  <span>👤 {{ s.terapis?.name || 'Belum ditugaskan' }}</span>
                </div>
                <div v-if="s.catatan" class="session-note">📝 {{ s.catatan }}</div>
              </div>
              <span :class="['kehadiran-badge', s.kehadiran]">{{ kehadiranLabel(s.kehadiran) }}</span>
            </div>
          </div>
        </section>

        <!-- Past Sessions -->
        <section v-if="past.length > 0" class="section-card">
          <h2 class="section-title">Riwayat Sesi</h2>
          <div class="session-list">
            <div v-for="s in past" :key="s.id" class="session-card past">
              <div class="session-date past">
                <span class="date-day">{{ formatDay(s.tanggal_sesi) }}</span>
                <span class="date-month">{{ formatMonth(s.tanggal_sesi) }}</span>
              </div>
              <div class="session-info">
                <div class="session-title">{{ s.jenis_terapi }}</div>
                <div class="session-meta">
                  <span v-if="s.waktu_mulai">🕐 {{ s.waktu_mulai }}</span>
                  <span>👤 {{ s.terapis?.name || '-' }}</span>
                </div>
                <div v-if="s.progress_score != null" class="progress-bar">
                  <span>Progress:</span>
                  <div class="bar-track"><div class="bar-fill" :style="{ width: s.progress_score + '%' }"></div></div>
                  <span>{{ s.progress_score }}%</span>
                </div>
              </div>
              <span :class="['kehadiran-badge', s.kehadiran]">{{ kehadiranLabel(s.kehadiran) }}</span>
            </div>
          </div>
        </section>

        <!-- No data at all -->
        <div v-if="schedules.length === 0" class="empty-full">
          <p>🗓️</p>
          <h3>Belum Ada Jadwal Terapi</h3>
          <p>Jadwal sesi terapi akan muncul di sini setelah terapis menjadwalkan sesi untuk Anda.</p>
        </div>
      </div>
    </main>
  </div>

  <!-- Modal Konfirmasi Logout -->
  <LogoutConfirmModal
    :show="showLogoutModal"
    :loading="logoutLoading"
    :user-name="patientStore.user?.name"
    @confirm="doLogout"
    @cancel="showLogoutModal = false"
  />
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { usePatientStore } from '../stores/patientStore'
import { patientService } from '../services/patientService'
import LogoutConfirmModal from '../../../shared/components/LogoutConfirmModal.vue'
import { useNotificationStore } from '../../../shared/stores/notificationStore'

const router = useRouter()
const patientStore = usePatientStore()
const notify = useNotificationStore()

const showLogoutModal = ref(false)
const logoutLoading   = ref(false)

const loading = ref(false)
const error = ref(null)
const schedules = ref([])

const today = new Date()
today.setHours(0, 0, 0, 0)

const upcoming = computed(() => schedules.value.filter(s => new Date(s.tanggal_sesi) >= today && s.kehadiran !== 'hadir' && s.kehadiran !== 'tidak_hadir'))
const past = computed(() => schedules.value.filter(s => new Date(s.tanggal_sesi) < today || s.kehadiran === 'hadir' || s.kehadiran === 'tidak_hadir'))
const done = computed(() => schedules.value.filter(s => s.kehadiran === 'hadir'))
const absent = computed(() => schedules.value.filter(s => s.kehadiran === 'tidak_hadir'))

const formatDay = (d) => d ? new Date(d).getDate() : '-'
const formatMonth = (d) => d ? new Date(d).toLocaleDateString('id-ID', { month: 'short', year: 'numeric' }) : '-'
const kehadiranLabel = (k) => ({ hadir: 'Hadir', tidak_hadir: 'Tidak Hadir', belum_hadir: 'Belum' })[k] ?? (k || 'Dijadwalkan')

const loadSchedule = async () => {
  loading.value = true
  error.value = null
  try {
    const result = await patientService.getTherapySchedule()
    if (result.success) {
      schedules.value = result.data?.schedules || result.data || []
    } else {
      error.value = result.error || 'Gagal memuat jadwal'
    }
  } catch {
    error.value = 'Terjadi kesalahan saat memuat data'
  } finally {
    loading.value = false
  }
}

const handleLogout = () => { showLogoutModal.value = true }

const doLogout = async () => {
  logoutLoading.value = true
  try {
    await patientStore.logout()
    notify.success('Anda berhasil keluar. Sampai jumpa!', 'Logout Berhasil')
    setTimeout(() => { router.push('/pasien/login') }, 800)
  } catch (e) {
    router.push('/pasien/login')
  } finally {
    logoutLoading.value = false
    showLogoutModal.value = false
  }
}

onMounted(loadSchedule)
</script>

<style scoped>
.patient-dashboard { display: flex; min-height: 100vh; background: #f8fafc; }
.patient-sidebar { width: 260px; background: #1e293b; color: white; display: flex; flex-direction: column; position: fixed; left: 0; top: 0; height: 100vh; }
.sidebar-header { padding: 1.5rem; display: flex; align-items: center; gap: 1rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
.sidebar-header .logo { width: 40px; height: 40px; }
.sidebar-header h2 { margin: 0; font-size: 1.25rem; }
.sidebar-header p { margin: 0; font-size: 0.75rem; color: #94a3b8; }
.sidebar-nav { flex: 1; padding: 1rem 0; }
.nav-item { display: flex; padding: 0.75rem 1.5rem; color: #cbd5e1; text-decoration: none; transition: all 0.2s; }
.nav-item:hover { background: rgba(255,255,255,0.05); color: white; }
.nav-item.active { background: #10b981; color: white; border-right: 4px solid white; }
.sidebar-footer { padding: 1rem 1.5rem; border-top: 1px solid rgba(255,255,255,0.1); }
.btn-logout { width: 100%; padding: 0.5rem; background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid #ef4444; border-radius: 0.5rem; cursor: pointer; font-weight: 600; }
.btn-logout:hover { background: #ef4444; color: white; }

.main-content { flex: 1; margin-left: 260px; padding: 2rem; }
.content-header { margin-bottom: 2rem; }
.btn-back { background: none; border: none; color: #059669; cursor: pointer; font-weight: 600; padding: 0; margin-bottom: 0.5rem; display: block; }
.content-header h1 { font-size: 1.75rem; color: #1e293b; margin: 0.25rem 0; }
.content-header p { color: #64748b; margin: 0; }

.loading-container { text-align: center; padding: 3rem; }
.loading-spinner { width: 40px; height: 40px; border: 3px solid #f1f5f9; border-top-color: #10b981; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { to { transform: rotate(360deg); } }

.error-message { background: #fef2f2; border: 1px solid #fecaca; padding: 1.5rem; border-radius: 0.75rem; color: #dc2626; text-align: center; }
.btn-retry { margin-top: 0.75rem; padding: 0.5rem 1.25rem; background: #059669; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 600; }

.summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 2rem; }
.summary-card { background: white; padding: 1.25rem; border-radius: 0.75rem; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border-top: 3px solid #10b981; }
.summary-card.done { border-top-color: #3b82f6; }
.summary-card.absent { border-top-color: #ef4444; }
.summary-value { font-size: 2rem; font-weight: 700; color: #1e293b; }
.summary-label { font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase; margin-top: 0.25rem; }

.section-card { background: white; border-radius: 0.75rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
.section-title { font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0 0 1rem 0; padding-bottom: 0.75rem; border-bottom: 2px solid #f1f5f9; }

.session-list { display: flex; flex-direction: column; gap: 0.75rem; }
.session-card { display: flex; align-items: flex-start; gap: 1.25rem; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e2e8f0; transition: all 0.2s; }
.session-card.upcoming { border-left: 4px solid #10b981; background: #f0fdf4; }
.session-card.past { border-left: 4px solid #d1d5db; background: #f8fafc; }
.session-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }

.session-date { display: flex; flex-direction: column; align-items: center; min-width: 48px; }
.date-day { font-size: 1.75rem; font-weight: 800; color: #10b981; line-height: 1; }
.session-date.past .date-day { color: #9ca3af; }
.date-month { font-size: 0.65rem; color: #94a3b8; text-align: center; }

.session-info { flex: 1; }
.session-title { font-weight: 600; color: #1e293b; margin-bottom: 0.375rem; }
.session-meta { display: flex; gap: 1rem; flex-wrap: wrap; }
.session-meta span { font-size: 0.8rem; color: #64748b; }
.session-note { font-size: 0.8rem; color: #64748b; margin-top: 0.375rem; font-style: italic; }

.kehadiran-badge { font-size: 0.7rem; font-weight: 700; padding: 0.25rem 0.75rem; border-radius: 9999px; white-space: nowrap; align-self: flex-start; }
.kehadiran-badge.hadir { background: #dcfce7; color: #166534; }
.kehadiran-badge.tidak_hadir { background: #fee2e2; color: #991b1b; }
.kehadiran-badge.belum_hadir, .kehadiran-badge.null { background: #dbeafe; color: #1e40af; }

.progress-bar { display: flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem; font-size: 0.75rem; color: #64748b; }
.bar-track { flex: 1; height: 6px; background: #e2e8f0; border-radius: 9999px; overflow: hidden; }
.bar-fill { height: 100%; background: #10b981; border-radius: 9999px; transition: width 0.5s; }

.empty-state { text-align: center; padding: 2rem; color: #94a3b8; }
.empty-state p:first-child { font-size: 2.5rem; }
.empty-full { text-align: center; padding: 4rem 2rem; background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.empty-full p:first-child { font-size: 3rem; }
.empty-full h3 { color: #1e293b; margin: 0.75rem 0 0.5rem; }
.empty-full p { color: #64748b; }

@media (max-width: 768px) {
  .patient-sidebar { transform: translateX(-100%); }
  .main-content { margin-left: 0; padding: 1rem; }
  .summary-grid { grid-template-columns: 1fr; }
}
</style>
