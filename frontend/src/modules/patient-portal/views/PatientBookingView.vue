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
        <router-link to="/pasien/dashboard" class="nav-item">
          <span>Dashboard</span>
        </router-link>
        <router-link to="/pasien/antrian" class="nav-item active">
          <span>Antrian</span>
        </router-link>
        <router-link to="/pasien/jadwal" class="nav-item">
          <span>Jadwal Terapi</span>
        </router-link>
        <router-link to="/pasien/riwayat" class="nav-item">
          <span>Riwayat Medis</span>
        </router-link>
        <router-link to="/pasien/profil" class="nav-item">
          <span>Profil Saya</span>
        </router-link>
      </nav>
      <div class="sidebar-footer">
        <button @click="handleLogout" class="btn-logout">
          Logout
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <div class="content-header">
        <button @click="goBack" class="btn-back">← Kembali</button>
        <h1>Antrian</h1>
        <p>Booking antrian atau lihat riwayat antrian Anda</p>
      </div>

      <!-- Tab Navigation -->
      <div class="tab-navigation">
        <button 
          :class="['tab-btn', { active: activeTab === 'booking' }]"
          @click="activeTab = 'booking'"
        >
          + Booking Antrian
        </button>
        <button 
          :class="['tab-btn', { active: activeTab === 'active' }]"
          @click="activeTab = 'active'"
        >
          🎫 Antrian Aktif
        </button>
        <button 
          :class="['tab-btn', { active: activeTab === 'history' }]"
          @click="activeTab = 'history'"
        >
          📋 Riwayat
        </button>
      </div>

      <!-- TAB: BOOKING -->
      <div v-show="activeTab === 'booking'" class="booking-card">
        <!-- Banner profil belum lengkap -->
        <div v-if="!checkingProfile && !profileComplete" class="profile-guard-banner">
          <div class="guard-icon">🔒</div>
          <div class="guard-content">
            <strong>Profil belum lengkap</strong>
            <p>Anda harus melengkapi data berikut sebelum bisa booking:</p>
            <ul>
              <li v-for="field in profileMissing" :key="field">{{ field }}</li>
            </ul>
          </div>
          <button @click="$router.push('/pasien/profil')" class="btn-go-profile">
            Lengkapi Profil →
          </button>
        </div>

        <form @submit.prevent="handleBooking" class="booking-form" :class="{ 'form-disabled': !profileComplete }">
          <!-- Pilih Poli -->
          <div class="form-section">
            <h3 class="section-title">Pilih Poli</h3>
            <div class="poli-grid">
              <label v-for="poli in poliOptions" :key="poli.value" class="poli-option" :class="{ selected: form.poli === poli.value }">
                <input type="radio" :value="poli.value" v-model="form.poli" required :disabled="!profileComplete" />
                <div class="poli-name">{{ poli.label }}</div>
                <div class="poli-desc">{{ poli.description }}</div>
              </label>
            </div>
          </div>

          <!-- Pilih Dokter / Terapis -->
          <div class="form-section">
            <h3 class="section-title">Pilih Dokter / Terapis</h3>
            <div v-if="loadingDoctors" class="empty-state">Memuat daftar dokter...</div>
            <div v-else-if="doctors.length === 0" class="empty-state">
              <p>Tidak ada dokter / terapis tersedia saat ini.</p>
            </div>
            <div v-else class="doctor-grid">
              <label v-for="doctor in doctors" :key="doctor.id" class="doctor-option" :class="{ selected: form.doctor_id === doctor.id }">
                <input type="radio" :value="doctor.id" v-model="form.doctor_id" />
                <div class="doctor-avatar">{{ getInitials(doctor.name) }}</div>
                <div class="doctor-info">
                  <div class="doctor-name">{{ doctor.name }}</div>
                  <div class="doctor-nip">{{ doctor.role === 'dokter' ? 'Dokter' : 'Terapis' }} · NIP: {{ doctor.nip }}</div>
                </div>
              </label>
            </div>
          </div>

          <!-- Pilih Jenis Layanan -->
          <div class="form-section">
            <h3 class="section-title">Jenis Layanan</h3>
            <select v-model="form.type" required class="form-select">
              <option value="">Pilih Jenis Layanan</option>
              <option value="consultation">Konsultasi Umum</option>
              <option value="assessment">Assessment</option>
              <option value="therapy">Terapi</option>
              <option value="control">Kontrol</option>
            </select>
          </div>

          <!-- Pilih Prioritas -->
          <div class="form-section">
            <h3 class="section-title">Prioritas</h3>
            <div class="priority-options">
              <label class="priority-option" :class="{ selected: form.priority === 'normal', normal: true }">
                <input type="radio" value="normal" v-model="form.priority" />
                <span class="priority-badge normal">Normal</span>
                <small>Antrian reguler</small>
              </label>
              <label class="priority-option" :class="{ selected: form.priority === 'urgent', urgent: true }">
                <input type="radio" value="urgent" v-model="form.priority" />
                <span class="priority-badge urgent">Urgent</span>
                <small>Perlu segera</small>
              </label>
            </div>
          </div>

          <!-- Catatan -->
          <div class="form-section">
            <h3 class="section-title">Keluhan / Catatan (Opsional)</h3>
            <textarea v-model="form.notes" rows="3" class="form-textarea" placeholder="Jelaskan keluhan atau catatan tambahan..."></textarea>
          </div>

          <!-- Submit -->
          <div class="form-actions">
            <button type="button" @click="goBack" class="btn-secondary">Batal</button>
            <button type="submit" :disabled="loading" class="btn-primary">
              <span v-if="loading">Memproses...</span>
              <span v-else>Booking Antrian</span>
            </button>
          </div>

          <!-- Success Message -->
          <div v-if="bookingSuccess" class="success-message">
            <div>
              <h3>Booking Berhasil!</h3>
              <p>Nomor Antrian: <strong>{{ queueNumber }}</strong></p>
              <p>Silakan tunggu panggilan dari admin klinik.</p>
              <button @click="activeTab = 'active'" class="btn-link">Lihat Antrian Aktif →</button>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="errorMessage" class="error-message">
            {{ errorMessage }}
          </div>
        </form>
      </div>

      <!-- TAB: ACTIVE QUEUE -->
      <div v-show="activeTab === 'active'" class="active-queue-section">
        <div v-if="loadingQueue" class="loading-container">
          <div class="loading-spinner"></div>
          <p>Memuat data antrian...</p>
        </div>
        <div v-else-if="errorQueue" class="error-message">
          {{ errorQueue }}
          <button @click="loadQueue" class="btn-retry">Coba Lagi</button>
        </div>
        <div v-else>
          <div v-if="activeQueue" class="active-queue-card">
            <div class="queue-badge">Antrian Aktif Hari Ini</div>
            <div class="queue-number">{{ activeQueue.nomor_antrian }}</div>
            <p class="queue-label">Nomor Antrian Anda</p>
            <div :class="['status-badge', `status-${activeQueue.status}`]">
              {{ statusLabel(activeQueue.status) }}
            </div>
            <div class="queue-details">
              <div class="detail-item">
                <span class="detail-icon">🏥</span>
                <div>
                  <p class="detail-label">Poli</p>
                  <p class="detail-value">{{ activeQueue.poli || '-' }}</p>
                </div>
              </div>
              <div class="detail-item">
                <span class="detail-icon">👨‍⚕️</span>
                <div>
                  <p class="detail-label">Dokter / Terapis</p>
                  <p class="detail-value">{{ activeQueue.dokter?.name || '-' }}</p>
                </div>
              </div>
              <div class="detail-item">
                <span class="detail-icon">📅</span>
                <div>
                  <p class="detail-label">Tanggal Daftar</p>
                  <p class="detail-value">{{ formatDateTime(activeQueue.tanggal) }}</p>
                </div>
              </div>
              <div class="detail-item">
                <span class="detail-icon">📋</span>
                <div>
                  <p class="detail-label">Jenis Layanan</p>
                  <p class="detail-value">{{ formatServiceType(activeQueue.jenis_layanan) }}</p>
                </div>
              </div>
            </div>
            <button @click="loadQueue" class="btn-refresh">🔄 Refresh Status</button>
          </div>
          <div v-else class="no-queue-card">
            <p class="no-queue-icon">🎫</p>
            <h3>Tidak Ada Antrian Aktif</h3>
            <p>Anda belum memiliki antrian hari ini.</p>
          </div>
        </div>
      </div>

      <!-- TAB: HISTORY -->
      <div v-show="activeTab === 'history'" class="history-section">
        <div v-if="loadingQueue" class="loading-container">
          <div class="loading-spinner"></div>
          <p>Memuat riwayat antrian...</p>
        </div>
        <div v-else-if="errorQueue" class="error-message">
          {{ errorQueue }}
        </div>
        <div v-else>
          <div v-if="queueHistory.length > 0" class="history-grid">
            <div v-for="item in queueHistory" :key="item.id" class="history-card">
              <div class="history-header">
                <span class="history-number">{{ item.nomor_antrian }}</span>
                <span :class="['history-status', `status-${item.status}`]">
                  {{ statusLabel(item.status) }}
                </span>
              </div>
              <div class="history-body">
                <div class="history-detail">
                  <p class="history-label">Poli</p>
                  <p class="history-value">{{ item.poli || '-' }}</p>
                </div>
                <div class="history-detail">
                  <p class="history-label">Jenis Layanan</p>
                  <p class="history-value">{{ formatServiceType(item.jenis_layanan) }}</p>
                </div>
                <div class="history-detail">
                  <p class="history-label">Tanggal</p>
                  <p class="history-value">{{ formatDate(item.tanggal) }}</p>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="empty-state">
            <p>Belum ada riwayat antrian</p>
          </div>
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
import { ref, onMounted } from 'vue'
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

const activeTab = ref('booking')
const loading = ref(false)
const loadingDoctors = ref(false)
const loadingQueue = ref(false)
const bookingSuccess = ref(false)
const queueNumber = ref('')
const errorMessage = ref('')
const errorQueue = ref('')
const doctors = ref([])
const activeQueue = ref(null)
const queueHistory = ref([])

// ── Profile completion guard ──────────────────────────────────────────
const profileComplete  = ref(true)
const profileMissing   = ref([])
const checkingProfile  = ref(true)

const form = ref({
  poli: '',
  doctor_id: '',
  type: 'consultation',
  priority: 'normal',
  notes: ''
})

const poliOptions = ref([])

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', { 
    day: 'numeric', 
    month: 'long', 
    year: 'numeric' 
  })
}

const formatDateTime = (dateStr) => {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return date.toLocaleDateString('id-ID', { 
    day: 'numeric', 
    month: 'long', 
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const statusLabel = (status) => {
  const labels = {
    'menunggu': 'Menunggu',
    'dipanggil': 'Dipanggil',
    'selesai': 'Selesai',
    'tidak_hadir': 'Tidak Hadir'
  }
  return labels[status] || status
}

const formatServiceType = (type) => {
  const types = {
    'konsultasi': 'Konsultasi Umum',
    'assessment': 'Assessment Medis',
    'terapi': 'Sesi Terapi',
    'kontrol': 'Kontrol'
  }
  return types[type] || type
}

const fetchPolis = async () => {
  try {
    const res = await patientService.getPolis()
    if (res.success) {
      poliOptions.value = res.data.map(p => ({
        value: p.kode,
        label: p.nama,
        description: p.deskripsi || ''
      }))
    }
  } catch {
    // fallback ke data lokal jika gagal
    poliOptions.value = [
      { value: 'umum',           label: 'Poli Umum',          description: '' },
      { value: 'terapi',         label: 'Poli Terapi',         description: '' },
      { value: 'psikolog',       label: 'Poli Psikolog',       description: '' },
      { value: 'tumbuh_kembang', label: 'Poli Tumbuh Kembang', description: '' },
    ]
  }
}

const loadQueue = async () => {
  loadingQueue.value = true
  errorQueue.value = ''
  try {
    const result = await patientService.getMyQueue()
    if (result.success) {
      activeQueue.value = result.data?.active_queue || null
      queueHistory.value = result.data?.history || []
    } else {
      errorQueue.value = result.error || 'Gagal memuat antrian'
    }
  } catch (e) {
    errorQueue.value = 'Terjadi kesalahan saat memuat data'
  } finally {
    loadingQueue.value = false
  }
}

const getInitials = (name) => {
  const parts = name.trim().split(' ')
  if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase()
  return name.substring(0, 2).toUpperCase()
}

const goBack = () => router.push('/pasien/dashboard')

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

const fetchDoctors = async () => {
  loadingDoctors.value = true
  try {
    const response = await patientService.getDoctors()
    if (response.success) {
      doctors.value = response.data
    } else {
      console.error('getDoctors: ', response.message)
    }
  } catch (error) {
    console.error('Error fetching doctors:', error)
  } finally {
    loadingDoctors.value = false
  }
}

const handleBooking = async () => {
  // Cegah booking jika profil belum lengkap
  if (!profileComplete.value) {
    errorMessage.value = 'Profil belum lengkap. Harap isi: ' + profileMissing.value.join(', ')
    return
  }

  loading.value = true
  errorMessage.value = ''
  bookingSuccess.value = false

  try {
    const response = await patientService.bookQueue({
      poli: form.value.poli,
      doctor_id: form.value.doctor_id,
      type: form.value.type,
      priority: form.value.priority,
      notes: form.value.notes
    })

    if (response.success) {
      queueNumber.value = response.data.queue_number
      bookingSuccess.value = true
      form.value = {
        poli: '',
        doctor_id: '',
        type: 'consultation',
        priority: 'normal',
        notes: ''
      }
      // Refresh antrian lalu pindah ke tab aktif
      await loadQueue()
      setTimeout(() => { activeTab.value = 'active' }, 1500)
    } else {
      errorMessage.value = response.message || 'Gagal booking antrian'
    }
  } catch (error) {
    const respData = error.response?.data
    // Jika backend return profil belum lengkap (422)
    if (respData?.missing) {
      profileComplete.value = false
      profileMissing.value  = respData.missing
      errorMessage.value    = respData.message || 'Profil belum lengkap'
    } else {
      errorMessage.value = respData?.message || 'Terjadi kesalahan'
    }
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  // Cek kelengkapan profil dulu
  try {
    const result = await patientService.getProfileStatus()
    if (result.success) {
      profileComplete.value = result.data?.is_complete ?? true
      profileMissing.value  = result.data?.missing     ?? []
    }
  } finally {
    checkingProfile.value = false
  }

  fetchDoctors()
  fetchPolis()
  loadQueue()
})
</script>

<style scoped>
.patient-dashboard { display: flex; min-height: 100vh; background: #f8fafc; }

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
}

.sidebar-header { padding: 1.5rem; display: flex; align-items: center; gap: 1rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
.sidebar-header .logo { width: 40px; height: 40px; }
.sidebar-header h2 { margin: 0; font-size: 1.25rem; }
.sidebar-header p { margin: 0; font-size: 0.75rem; color: #94a3b8; }

.sidebar-nav { flex: 1; padding: 1rem 0; }
.nav-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1.5rem; color: #cbd5e1; text-decoration: none; }
.nav-item:hover { background: rgba(255,255,255,0.05); color: white; }
.nav-item.active { background: #10b981; color: white; border-right: 4px solid white; }

.sidebar-footer { padding: 1rem 1.5rem; border-top: 1px solid rgba(255,255,255,0.1); }
.btn-logout {
  width: 100%;
  padding: 0.5rem;
  background: rgba(239,68,68,0.1);
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

.main-content { flex: 1; margin-left: 260px; padding: 2rem; }

.content-header { margin-bottom: 2rem; }
.btn-back { background: none; border: none; color: #059669; cursor: pointer; font-weight: 600; padding: 0; margin-bottom: 0.5rem; }
.content-header h1 { font-size: 1.75rem; color: #1e293b; margin: 0.25rem 0; }
.content-header p { color: #64748b; margin: 0; }

.booking-card { background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }

/* ── Profile Guard Banner ── */
.profile-guard-banner {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  background: #fef3c7;
  border: 1.5px solid #f59e0b;
  border-radius: 0.75rem;
  padding: 1.25rem 1.5rem;
  margin-bottom: 1.5rem;
}
.guard-icon { font-size: 1.75rem; flex-shrink: 0; }
.guard-content { flex: 1; }
.guard-content strong { display: block; color: #92400e; font-size: 1rem; margin-bottom: 0.25rem; }
.guard-content p { color: #78350f; font-size: 0.875rem; margin: 0 0 0.5rem; }
.guard-content ul { margin: 0; padding-left: 1.25rem; }
.guard-content ul li { color: #78350f; font-size: 0.875rem; line-height: 1.6; }
.btn-go-profile {
  background: #f59e0b;
  color: white;
  border: none;
  padding: 0.5rem 1.1rem;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  flex-shrink: 0;
  font-size: 0.875rem;
}
.btn-go-profile:hover { background: #d97706; }

/* Form disabled saat profil belum lengkap */
.form-disabled { opacity: 0.5; pointer-events: none; user-select: none; }
.booking-form { display: flex; flex-direction: column; gap: 2rem; }

.section-title {
  color: #1e293b;
  margin-bottom: 1rem;
  font-size: 1.125rem;
  font-weight: 700;
}

/* Poli Grid */
.poli-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
.poli-option {
  padding: 1.5rem;
  border: 2px solid #e2e8f0;
  border-radius: 0.75rem;
  cursor: pointer;
  text-align: center;
  transition: all 0.2s;
}
.poli-option:hover { border-color: #10b981; }
.poli-option.selected { border-color: #10b981; background: #f0fdf4; }
.poli-option input { display: none; }
.poli-name { font-weight: 600; color: #1e293b; margin-bottom: 0.25rem; }
.poli-desc { font-size: 0.8rem; color: #64748b; }

/* Doctor Grid */
.doctor-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; }
.doctor-option {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 0.75rem;
  cursor: pointer;
  transition: all 0.2s;
}
.doctor-option:hover { border-color: #10b981; }
.doctor-option.selected { border-color: #10b981; background: #f0fdf4; }
.doctor-option input { display: none; }
.doctor-avatar {
  width: 50px;
  height: 50px;
  background: #10b981;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  flex-shrink: 0;
}
.doctor-name { font-weight: 600; color: #1e293b; }
.doctor-nip { font-size: 0.8rem; color: #64748b; }

.form-select, .form-textarea {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 1rem;
}
.form-select:focus, .form-textarea:focus { outline: none; border-color: #10b981; }

/* Priority */
.priority-options { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.priority-option {
  padding: 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 0.5rem;
  cursor: pointer;
  text-align: center;
}
.priority-option input { display: none; }
.priority-option.selected.normal { border-color: #3b82f6; background: #eff6ff; }
.priority-option.selected.urgent { border-color: #f59e0b; background: #fffbeb; }
.priority-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 600; font-size: 0.875rem; margin-bottom: 0.25rem; }
.priority-badge.normal { background: #dbeafe; color: #1e40af; }
.priority-badge.urgent { background: #fef3c7; color: #92400e; }

.form-actions { display: flex; justify-content: flex-end; gap: 1rem; padding-top: 1rem; border-top: 1px solid #f1f5f9; }
.btn-secondary { padding: 0.625rem 1.25rem; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 0.5rem; cursor: pointer; font-weight: 600; font-size: 0.875rem; }
.btn-secondary:hover { background: #e2e8f0; }

/* ── Tab Navigation ── */
.tab-navigation { display: flex; gap: 0.5rem; margin-bottom: 2rem; border-bottom: 2px solid #e2e8f0; }
.tab-btn { padding: 1rem 1.5rem; background: none; border: none; cursor: pointer; font-weight: 600; font-size: 0.95rem; color: #64748b; border-bottom: 3px solid transparent; transition: all 0.2s; margin-bottom: -2px; }
.tab-btn:hover { color: #1e293b; }
.tab-btn.active { color: #10b981; border-bottom-color: #10b981; }

/* ── Active Queue ── */
.active-queue-card { background: linear-gradient(135deg, #059669 0%, #047857 100%); color: white; border-radius: 1.25rem; padding: 2.5rem; text-align: center; box-shadow: 0 10px 30px rgba(5, 150, 105, 0.2); position: relative; overflow: hidden; }
.active-queue-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #10b981, #6ee7b7); }
.queue-badge { display: inline-block; background: rgba(255,255,255,0.2); padding: 0.5rem 1rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; margin-bottom: 1rem; backdrop-filter: blur(10px); }
.queue-number { font-size: 5rem; font-weight: 900; line-height: 1; margin: 0.5rem 0; }
.queue-label { font-size: 1rem; opacity: 0.9; margin: 0; }
.status-badge { display: inline-block; padding: 0.5rem 1.5rem; background: rgba(255,255,255,0.2); border-radius: 9999px; font-weight: 600; margin: 1.5rem 0; font-size: 0.875rem; }
.queue-details { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin: 2rem 0; }
.detail-item { display: flex; gap: 1rem; text-align: left; background: rgba(255,255,255,0.1); padding: 1rem; border-radius: 0.75rem; }
.detail-icon { font-size: 1.5rem; }
.detail-label { font-size: 0.75rem; opacity: 0.8; margin: 0; text-transform: uppercase; }
.detail-value { font-size: 0.95rem; font-weight: 600; margin: 0.25rem 0 0 0; }
.btn-refresh { background: white; color: #059669; border: none; padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.btn-refresh:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }

.no-queue-card { background: white; border-radius: 1rem; padding: 2.5rem; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.no-queue-icon { font-size: 3rem; display: block; margin-bottom: 0.75rem; }
.no-queue-card h3 { font-size: 1.2rem; font-weight: 600; color: #1e293b; margin: 0 0 0.5rem 0; }

/* ── History ── */
.history-section { background: white; border-radius: 1rem; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.section-title { font-size: 1.125rem; font-weight: 700; color: #1e293b; margin-bottom: 1.5rem; }
.history-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem; }
.history-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; overflow: hidden; transition: all 0.2s; }
.history-card:hover { border-color: #059669; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.1); }
.history-header { display: flex; align-items: center; justify-content: space-between; padding: 1rem; border-bottom: 1px solid #e2e8f0; }
.history-number { font-size: 1.25rem; font-weight: 800; color: #059669; }
.history-status { display: inline-block; font-size: 0.7rem; padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 600; }
.history-status.status-selesai { background: #dcfce7; color: #166534; }
.history-status.status-menunggu { background: #fef3c7; color: #92400e; }
.history-status.status-dipanggil { background: #dbeafe; color: #1e40af; }
.history-status.status-tidak_hadir { background: #fee2e2; color: #991b1b; }
.history-body { padding: 1rem; }
.history-detail { margin-bottom: 0.75rem; }
.history-detail:last-child { margin-bottom: 0; }
.history-label { font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase; margin: 0; }
.history-value { font-size: 0.9rem; color: #1e293b; font-weight: 500; margin: 0.25rem 0 0 0; }

.loading-container { text-align: center; padding: 3rem; }
.loading-spinner { width: 40px; height: 40px; border: 3px solid #f1f5f9; border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { to { transform: rotate(360deg); } }

.error-message { background: #fef2f2; border: 1px solid #fecaca; padding: 1.5rem; border-radius: 0.75rem; color: #dc2626; text-align: center; }
.btn-retry { margin-top: 1rem; padding: 0.5rem 1.5rem; background: #059669; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 600; }

.empty-state { text-align: center; padding: 2rem; color: #94a3b8; background: white; border-radius: 0.75rem; }

.priority-option { padding: 1rem; border: 2px solid #e2e8f0; border-radius: 0.5rem; cursor: pointer; text-align: center; }
.priority-option input { display: none; }
.priority-option.selected.normal { border-color: #3b82f6; background: #eff6ff; }
.priority-option.selected.urgent { border-color: #f59e0b; background: #fffbeb; }
.priority-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 600; font-size: 0.875rem; margin-bottom: 0.25rem; }
.priority-badge.normal { background: #dbeafe; color: #1e40af; }
.priority-badge.urgent { background: #fef3c7; color: #92400e; }

.form-actions { display: flex; justify-content: flex-end; gap: 1rem; padding-top: 1rem; border-top: 1px solid #f1f5f9; }
.btn-secondary { padding: 0.75rem 1.5rem; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 0.5rem; cursor: pointer; }
.btn-primary { padding: 0.75rem 1.5rem; background: #10b981; color: white; border: none; border-radius: 0.5rem; font-weight: 600; cursor: pointer; }
.btn-primary:hover:not(:disabled) { background: #059669; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

.success-message {
  background: #f0fdf4;
  border: 2px solid #10b981;
  padding: 1.5rem;
  border-radius: 0.75rem;
}
.success-message h3 { color: #166534; margin-bottom: 0.5rem; }
.success-message p { color: #15803d; margin: 0.25rem 0; }
.btn-link { color: #059669; font-weight: 600; text-decoration: none; background: none; border: none; cursor: pointer; padding: 0; font-size: inherit; }

.error-message {
  background: #fef2f2;
  border: 1px solid #fecaca;
  padding: 1rem;
  border-radius: 0.5rem;
  color: #dc2626;
  text-align: center;
}

.empty-state { padding: 2rem; text-align: center; color: #64748b; background: #f8fafc; border-radius: 0.5rem; }

@media (max-width: 768px) {
  .patient-sidebar { transform: translateX(-100%); }
  .main-content { margin-left: 0; }
  .poli-grid, .doctor-grid, .priority-options { grid-template-columns: 1fr; }
}
</style>
