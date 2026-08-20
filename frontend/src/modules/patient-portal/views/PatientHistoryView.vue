<template>
  <div class="patient-dashboard">
    <aside class="patient-sidebar">
      <div class="sidebar-header">
        <img src="@/assets/SITARA_RM_BG.png" alt="SITARA" class="logo" />
        <div><h2>SITARA</h2><p>Portal Pasien</p></div>
      </div>
      <nav class="sidebar-nav">
        <router-link to="/pasien/dashboard" class="nav-item">Dashboard</router-link>
        <router-link to="/pasien/antrian" class="nav-item">Antrian</router-link>
        <router-link to="/pasien/jadwal" class="nav-item">Jadwal Terapi</router-link>
        <router-link to="/pasien/riwayat" class="nav-item active">Riwayat Medis</router-link>
        <router-link to="/pasien/profil" class="nav-item">Profil Saya</router-link>
      </nav>
      <div class="sidebar-footer">
        <button @click="handleLogout" class="btn-logout">Logout</button>
      </div>
    </aside>

    <main class="main-content">
      <div class="content-header">
        <button @click="$router.push('/pasien/dashboard')" class="btn-back">← Kembali</button>
        <h1>Riwayat Medis</h1>
        <p>Riwayat assessment dan program terapi Anda</p>
      </div>

      <div v-if="loading" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Memuat riwayat medis...</p>
      </div>

      <div v-else-if="error" class="error-message">
        {{ error }}
        <button @click="loadHistory" class="btn-retry">Coba Lagi</button>
      </div>

      <div v-else>
        <!-- Assessment Section -->
        <section class="section-card">
          <h2 class="section-title">Assessment Medis</h2>
          <div v-if="assessments.length === 0" class="empty-state">
            <p>Belum ada data assessment medis</p>
          </div>
          <div v-else class="card-list">
            <div v-for="item in assessments" :key="item.id" class="record-card">
              <div class="record-header" @click="toggleAssessment(item.id)" style="cursor:pointer;">
                <span class="badge blue">Assessment</span>
                <span class="record-date">{{ formatDate(item.tanggal_assessment || item.created_at) }}</span>
                <span class="expand-icon">{{ expandedAssessments.has(item.id) ? '▲' : '▼' }}</span>
              </div>
              <!-- Ringkasan selalu tampil -->
              <div class="record-body">
                <div class="record-row"><span>Diagnosis</span><strong>{{ item.diagnosis || '-' }}</strong></div>
                <div class="record-row"><span>ICD-10</span><strong>{{ item.icd10_code || '-' }}</strong></div>
                <div class="record-row"><span>Dokter</span><strong>{{ item.dokter?.name || '-' }}</strong></div>
              </div>
              <!-- Detail expandable -->
              <div v-if="expandedAssessments.has(item.id)" class="record-detail">
                <div v-if="item.keluhan_utama" class="detail-section">
                  <div class="detail-label">Keluhan Utama</div>
                  <div class="detail-value">{{ item.keluhan_utama }}</div>
                </div>
                <div v-if="item.riwayat_penyakit" class="detail-section">
                  <div class="detail-label">Riwayat Penyakit</div>
                  <div class="detail-value">{{ item.riwayat_penyakit }}</div>
                </div>
                <div v-if="item.hasil_pemeriksaan && Object.keys(item.hasil_pemeriksaan).length" class="detail-section">
                  <div class="detail-label">Hasil Pemeriksaan</div>
                  <div class="pemeriksaan-grid">
                    <div v-if="item.hasil_pemeriksaan.tensi" class="pemeriksaan-item">
                      <span>Tensi</span><strong>{{ item.hasil_pemeriksaan.tensi }} mmHg</strong>
                    </div>
                    <div v-if="item.hasil_pemeriksaan.nadi" class="pemeriksaan-item">
                      <span>Nadi</span><strong>{{ item.hasil_pemeriksaan.nadi }} bpm</strong>
                    </div>
                    <div v-if="item.hasil_pemeriksaan.suhu" class="pemeriksaan-item">
                      <span>Suhu</span><strong>{{ item.hasil_pemeriksaan.suhu }} °C</strong>
                    </div>
                    <div v-if="item.hasil_pemeriksaan.berat_badan" class="pemeriksaan-item">
                      <span>Berat Badan</span><strong>{{ item.hasil_pemeriksaan.berat_badan }} kg</strong>
                    </div>
                    <div v-if="item.hasil_pemeriksaan.tinggi_badan" class="pemeriksaan-item">
                      <span>Tinggi Badan</span><strong>{{ item.hasil_pemeriksaan.tinggi_badan }} cm</strong>
                    </div>
                  </div>
                </div>
                <div v-if="item.rencana_terapi" class="detail-section">
                  <div class="detail-label">Rencana Terapi</div>
                  <div class="detail-value">{{ item.rencana_terapi }}</div>
                </div>
                <div v-if="item.obat_diresepkan && item.obat_diresepkan.length" class="detail-section">
                  <div class="detail-label">Obat Diresepkan</div>
                  <ul class="obat-list">
                    <li v-for="(obat, i) in item.obat_diresepkan" :key="i">{{ obat }}</li>
                  </ul>
                </div>
                <div v-if="item.catatan_medis" class="detail-section">
                  <div class="detail-label">Catatan Medis</div>
                  <div class="detail-value">{{ item.catatan_medis }}</div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Therapy Section -->
        <section class="section-card">
          <h2 class="section-title">Program Terapi</h2>
          <div v-if="therapies.length === 0" class="empty-state">
            <p>Belum ada program terapi</p>
          </div>
          <div v-else class="card-list">
            <div v-for="item in therapies" :key="item.id" class="record-card">
              <div class="record-header">
                <span class="badge yellow">Terapi</span>
                <span :class="['status-chip', `status-${item.status}`]">{{ item.status }}</span>
                <span class="record-date">{{ formatDate(item.created_at) }}</span>
              </div>
              <div class="record-body">
                <div class="record-row"><span>Jenis</span><strong>{{ item.jenis_terapi || '-' }}</strong></div>
                <div class="record-row"><span>Terapis</span><strong>{{ item.terapis?.name || '-' }}</strong></div>
                <div class="record-row">
                  <span>Progress Sesi</span>
                  <div class="progress-wrap">
                    <div class="progress-bar">
                      <div class="progress-fill" :style="{ width: item.total_sesi > 0 ? (item.sesi_selesai / item.total_sesi * 100) + '%' : '0%' }"></div>
                    </div>
                    <span>{{ item.sesi_selesai }}/{{ item.total_sesi }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
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
const loading = ref(false)
const error = ref(null)
const assessments = ref([])
const therapies = ref([])
const expandedAssessments = ref(new Set())

const toggleAssessment = (id) => {
  const set = new Set(expandedAssessments.value)
  if (set.has(id)) {
    set.delete(id)
  } else {
    set.add(id)
  }
  expandedAssessments.value = set
}

const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) : '-'

const loadHistory = async () => {
  loading.value = true
  error.value = null
  try {
    const result = await patientService.getMedicalHistory()
    if (result.success) {
      assessments.value = result.data?.assessments || []
      therapies.value = result.data?.therapies || []
    } else {
      error.value = result.error || 'Gagal memuat riwayat'
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

onMounted(loadHistory)
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

.section-card { background: white; border-radius: 0.75rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
.section-title { font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0 0 1rem 0; padding-bottom: 0.75rem; border-bottom: 2px solid #f1f5f9; }
.card-list { display: flex; flex-direction: column; gap: 0.75rem; }

.record-card { border: 1px solid #e2e8f0; border-radius: 0.5rem; overflow: hidden; }
.record-header { display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
.record-date { margin-left: auto; font-size: 0.75rem; color: #94a3b8; }
.badge { font-size: 0.7rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 9999px; }
.badge.blue { background: #dbeafe; color: #1e40af; }
.badge.yellow { background: #fef3c7; color: #92400e; }
.status-chip { font-size: 0.7rem; padding: 0.2rem 0.6rem; border-radius: 9999px; font-weight: 600; background: #f1f5f9; color: #64748b; }
.status-chip.status-berjalan { background: #dcfce7; color: #166534; }
.status-chip.status-selesai { background: #f1f5f9; color: #64748b; }

.record-body { padding: 1rem; display: flex; flex-direction: column; gap: 0.5rem; }
.record-row { display: flex; justify-content: space-between; font-size: 0.875rem; }
.record-row span { color: #64748b; }
.record-row strong { color: #1e293b; text-align: right; max-width: 60%; }

.progress-wrap { display: flex; align-items: center; gap: 0.5rem; }
.progress-bar { width: 80px; height: 6px; background: #e2e8f0; border-radius: 9999px; overflow: hidden; }
.progress-fill { height: 100%; background: #10b981; border-radius: 9999px; }

.empty-state { text-align: center; padding: 1.5rem; color: #94a3b8; font-size: 0.875rem; }

.expand-icon { margin-left: 0.5rem; font-size: 0.65rem; color: #94a3b8; }

.record-detail { border-top: 1px solid #e2e8f0; padding: 1rem; background: #f8fafc; display: flex; flex-direction: column; gap: 0.75rem; }
.detail-section { display: flex; flex-direction: column; gap: 0.25rem; }
.detail-label { font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
.detail-value { font-size: 0.875rem; color: #1e293b; line-height: 1.5; }

.pemeriksaan-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 0.5rem; margin-top: 0.25rem; }
.pemeriksaan-item { background: white; border: 1px solid #e2e8f0; border-radius: 0.375rem; padding: 0.5rem 0.75rem; display: flex; flex-direction: column; gap: 0.1rem; }
.pemeriksaan-item span { font-size: 0.7rem; color: #94a3b8; }
.pemeriksaan-item strong { font-size: 0.875rem; color: #1e293b; }

.obat-list { margin: 0.25rem 0 0 1.25rem; padding: 0; font-size: 0.875rem; color: #1e293b; line-height: 1.8; }

@media (max-width: 768px) {
  .patient-sidebar { transform: translateX(-100%); }
  .main-content { margin-left: 0; padding: 1rem; }
}
</style>
