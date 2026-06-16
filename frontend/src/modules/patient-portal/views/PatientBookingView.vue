<template>
  <div class="patient-dashboard">
    <!-- Sidebar (sama seperti PatientDashboardView) -->
    <aside class="patient-sidebar">
      <div class="sidebar-header">
        <img src="../../../../assets/SITARA_RM_BG.png" alt="SITARA" class="logo" />
        <div>
          <h2>SITARA</h2>
          <p>Portal Pasien</p>
        </div>
      </div>
      <nav class="sidebar-nav">
        <router-link to="/pasien/dashboard" class="nav-item"><span class="icon">📊</span><span>Dashboard</span></router-link>
        <router-link to="/pasien/booking" class="nav-item active"><span class="icon">📅</span><span>Booking Antrian</span></router-link>
        <router-link to="/pasien/antrian-saya" class="nav-item"><span class="icon">🎫</span><span>Antrian Saya</span></router-link>
        <router-link to="/pasien/jadwal" class="nav-item"><span class="icon">📆</span><span>Jadwal Terapi</span></router-link>
        <router-link to="/pasien/riwayat" class="nav-item"><span class="icon">📋</span><span>Riwayat Medis</span></router-link>
        <router-link to="/pasien/profil" class="nav-item"><span class="icon">👤</span><span>Profil Saya</span></router-link>
      </nav>
      <div class="sidebar-footer">
        <button @click="handleLogout" class="btn-logout">🚪 Logout</button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <div class="content-header">
        <button @click="goBack" class="btn-back">← Kembali</button>
        <h1>Booking Antrian</h1>
        <p>Daftar antrian untuk konsultasi dengan dokter pilihan Anda</p>
      </div>

      <!-- Booking Form -->
      <div class="booking-card">
        <form @submit.prevent="handleBooking" class="booking-form">
          <!-- Pilih Poli -->
          <div class="form-section">
            <h3>🏥 Pilih Poli</h3>
            <div class="poli-grid">
              <label v-for="poli in poliOptions" :key="poli.value" class="poli-option" :class="{ selected: form.poli === poli.value }">
                <input type="radio" :value="poli.value" v-model="form.poli" required />
                <div class="poli-icon">{{ poli.icon }}</div>
                <div class="poli-name">{{ poli.label }}</div>
                <div class="poli-desc">{{ poli.description }}</div>
              </label>
            </div>
          </div>

          <!-- Pilih Dokter -->
          <div class="form-section">
            <h3>👨‍⚕️ Pilih Dokter</h3>
            <div v-if="filteredDoctors.length === 0" class="empty-state">
              <p>Tidak ada dokter tersedia untuk poli ini. Silakan pilih poli lain.</p>
            </div>
            <div v-else class="doctor-grid">
              <label v-for="doctor in filteredDoctors" :key="doctor.id" class="doctor-option" :class="{ selected: form.doctor_id === doctor.id }">
                <input type="radio" :value="doctor.id" v-model="form.doctor_id" required />
                <div class="doctor-avatar">{{ getInitials(doctor.name) }}</div>
                <div class="doctor-info">
                  <div class="doctor-name">{{ doctor.name }}</div>
                  <div class="doctor-nip">NIP: {{ doctor.nip || '-' }}</div>
                </div>
              </label>
            </div>
          </div>

          <!-- Pilih Jenis Layanan -->
          <div class="form-section">
            <h3>📋 Jenis Layanan</h3>
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
            <h3>⚡ Prioritas</h3>
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
            <h3>📝 Keluhan / Catatan (Opsional)</h3>
            <textarea v-model="form.notes" rows="3" class="form-textarea" placeholder="Jelaskan keluhan atau catatan tambahan..."></textarea>
          </div>

          <!-- Submit -->
          <div class="form-actions">
            <button type="button" @click="goBack" class="btn-secondary">Batal</button>
            <button type="submit" :disabled="loading" class="btn-primary">
              <span v-if="loading">Memproses...</span>
              <span v-else>📅 Booking Antrian</span>
            </button>
          </div>

          <!-- Success Message -->
          <div v-if="bookingSuccess" class="success-message">
            <div class="success-icon">✅</div>
            <div>
              <h3>Booking Berhasil!</h3>
              <p>Nomor Antrian: <strong>{{ queueNumber }}</strong></p>
              <p>Silakan tunggu panggilan dari admin klinik.</p>
              <router-link to="/pasien/antrian-saya" class="btn-link">Lihat Antrian Saya →</router-link>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="errorMessage" class="error-message">
            {{ errorMessage }}
          </div>
        </form>
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

const loading = ref(false)
const bookingSuccess = ref(false)
const queueNumber = ref('')
const errorMessage = ref('')
const doctors = ref([])

const form = ref({
  poli: '',
  doctor_id: '',
  type: 'consultation',
  priority: 'normal',
  notes: ''
})

const poliOptions = [
  { 
    value: 'umum', 
    label: 'Poli Umum', 
    icon: '🏥',
    description: 'Konsultasi umum dan pemeriksaan awal'
  },
  { 
    value: 'psikolog', 
    label: 'Poli Psikolog', 
    icon: '🧠',
    description: 'Konsultasi psikologi anak dan keluarga'
  },
  { 
    value: 'terapi', 
    label: 'Poli Terapi', 
    icon: '💪',
    description: 'Terapi wicara, okupasi, dan fisio'
  },
  { 
    value: 'tumbuh_kembang', 
    label: 'Poli Tumbuh Kembang', 
    icon: '🌱',
    description: 'Pemantauan tumbuh kembang anak'
  }
]

const filteredDoctors = computed(() => {
  if (!form.value.poli) return doctors.value
  return doctors.value
})

const getInitials = (name) => {
  const parts = name.split(' ')
  if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase()
  return name.substring(0, 2).toUpperCase()
}

const goBack = () => router.push('/pasien/dashboard')

const handleLogout = async () => {
  if (confirm('Yakin ingin keluar?')) {
    await patientStore.logout()
    router.push('/pasien/login')
  }
}

const fetchDoctors = async () => {
  try {
    const response = await patientService.getDoctors()
    if (response.success) {
      doctors.value = response.data
    }
  } catch (error) {
    console.error('Error fetching doctors:', error)
  }
}

const handleBooking = async () => {
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
    } else {
      errorMessage.value = response.message || 'Gagal booking antrian'
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Terjadi kesalahan'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchDoctors()
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
.icon { font-size: 1.25rem; }

.sidebar-footer { padding: 1rem 1.5rem; border-top: 1px solid rgba(255,255,255,0.1); }
.btn-logout { width: 100%; padding: 0.5rem; background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid #ef4444; border-radius: 0.5rem; cursor: pointer; font-weight: 600; }
.btn-logout:hover { background: #ef4444; color: white; }

.main-content { flex: 1; margin-left: 260px; padding: 2rem; }

.content-header { margin-bottom: 2rem; }
.btn-back { background: none; border: none; color: #059669; cursor: pointer; font-weight: 600; padding: 0; margin-bottom: 0.5rem; }
.content-header h1 { font-size: 1.75rem; color: #1e293b; margin: 0.25rem 0; }
.content-header p { color: #64748b; margin: 0; }

.booking-card { background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.booking-form { display: flex; flex-direction: column; gap: 2rem; }

.form-section h3 { color: #1e293b; margin-bottom: 1rem; font-size: 1.125rem; }

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
.poli-icon { font-size: 2.5rem; margin-bottom: 0.5rem; }
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
.btn-secondary { padding: 0.75rem 1.5rem; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 0.5rem; cursor: pointer; }
.btn-primary { padding: 0.75rem 1.5rem; background: #10b981; color: white; border: none; border-radius: 0.5rem; font-weight: 600; cursor: pointer; }
.btn-primary:hover:not(:disabled) { background: #059669; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

.success-message {
  background: #f0fdf4;
  border: 2px solid #10b981;
  padding: 1.5rem;
  border-radius: 0.75rem;
  display: flex;
  gap: 1rem;
  align-items: flex-start;
}
.success-icon { font-size: 2rem; }
.success-message h3 { color: #166534; margin-bottom: 0.5rem; }
.success-message p { color: #15803d; margin: 0.25rem 0; }
.btn-link { color: #059669; font-weight: 600; text-decoration: none; }

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
