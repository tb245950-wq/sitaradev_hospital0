<template>
  <div class="page-container">
    <!-- Header -->
    <div class="page-header">
      <div>
        <button @click="goBack" class="btn-back">
          <span class="arrow">←</span>
          <span>{{ backButtonText }}</span>
        </button>
        <h1 class="page-title">Detail Pasien</h1>
        <p class="page-subtitle">Informasi lengkap rekam medis anak</p>
      </div>
      <div class="page-actions">
        <router-link 
          v-if="canManage" 
          :to="`/patients/${patient?.id}/edit`" 
          class="btn-primary"
          style="display: inline-flex; align-items: center; gap: 0.5rem;"
        >
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
          </svg>
          Edit Profil
        </router-link>
      </div>
    </div>

    <div v-if="patientStore.loading" class="loading-state">
      <div class="loading-spinner"></div>
      <p>Memuat detail pasien...</p>
    </div>

    <div v-else-if="patient" class="detail-grid">
      <!-- Sidebar: Profil Ringkas -->
      <div class="sidebar">
        <div class="profile-card">
          <div class="avatar-large">
            {{ patient.nama.charAt(0) }}
          </div>
          <h2 class="patient-name">{{ patient.nama }}</h2>
          <p class="patient-nrm">{{ patient.nrm }}</p>
          <div class="patient-badge" :class="patient.jenis_kelamin === 'Laki-laki' ? 'male' : 'female'">
            {{ patient.jenis_kelamin }}
          </div>
          <div class="stats-mini">
            <div class="stat-item">
              <span class="stat-value">{{ patient.info_lahir.usia }}</span>
              <span class="stat-label">Tahun</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
              <span class="stat-value">{{ patient.statistik?.total_assessment || 0 }}</span>
              <span class="stat-label">Assessment</span>
            </div>
          </div>
        </div>

        <div class="info-card">
          <h3 class="card-title">Kontak Orang Tua</h3>
          <div class="info-list">
            <div class="info-item">
              <label>Nama Wali</label>
              <p>{{ patient.wali.nama }} ({{ patient.wali.hubungan }})</p>
            </div>
            <div class="info-item">
              <label>No. Telepon</label>
              <p>{{ patient.wali.kontak }}</p>
            </div>
            <div class="info-item">
              <label>Alamat</label>
              <p>{{ patient.alamat }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content: Riwayat & Detail -->
      <div class="main-content">
        <div class="tabs-container">
          <div class="tabs-header">
            <button 
              v-for="tab in tabs" 
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="['tab-btn', { active: activeTab === tab.id }]"
            >
              {{ tab.label }}
            </button>
          </div>

          <div class="tab-content">
            <!-- Tab: Ikhtisar -->
            <div v-if="activeTab === 'overview'" class="tab-pane">
              <div class="content-section">
                <h3 class="section-title">Identitas Lengkap</h3>
                <div class="info-grid">
                  <div class="info-item">
                    <label>NIK</label>
                    <MaskedNIK
                      :full-nik="patient.nik"
                      :masked-nik="patient.masked_nik"
                    />
                  </div>
                  <div class="info-item">
                    <label>Nama Panggilan</label>
                    <p>{{ patient.nama_panggilan || '-' }}</p>
                  </div>
                  <div class="info-item">
                    <label>Tanggal Lahir</label>
                    <p>{{ formatDate(patient.info_lahir?.tanggal) }} <span class="usia-badge">({{ patient.info_lahir?.usia }} tahun)</span></p>
                  </div>
                  <div class="info-item">
                    <label>Jenis Kelamin</label>
                    <p>{{ patient.jenis_kelamin }}</p>
                  </div>
                  <div class="info-item">
                    <label>Terdaftar Sejak</label>
                    <p>{{ formatDate(patient.created_at) }}</p>
                  </div>
                </div>
              </div>

              <div class="content-section">
                <h3 class="section-title">Data Orang Tua / Wali</h3>
                <div class="info-grid">
                  <div class="info-item">
                    <label>Nama Orang Tua / Wali</label>
                    <p>{{ patient.wali?.nama || '-' }}</p>
                  </div>
                  <div class="info-item">
                    <label>Hubungan</label>
                    <p>{{ patient.wali?.hubungan || '-' }}</p>
                  </div>
                  <div class="info-item">
                    <label>No. Telepon</label>
                    <p>{{ patient.wali?.kontak || '-' }}</p>
                  </div>
                  <div class="info-item">
                    <label>Alamat</label>
                    <p>{{ patient.alamat || '-' }}</p>
                  </div>
                </div>
              </div>

              <div class="content-section">
                <h3 class="section-title">Riwayat Medis</h3>
                <div class="memo-box">
                  {{ patient.riwayat_medis || 'Tidak ada riwayat medis tercatat.' }}
                </div>
              </div>
            </div>

            <!-- Tab: Riwayat Layanan (Placeholder for now) -->
            <div v-else class="tab-pane text-center py-12">
              <div class="empty-state">
                <div class="empty-icon">📂</div>
                <p>Data {{ tabs.find(t => t.id === activeTab).label }} akan tampil di sini.</p>
                <p class="text-sm text-gray-400">Hubungkan dengan modul terkait untuk melihat riwayat lengkap.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../auth/stores/authStore'
import { usePatientStore } from '../stores/patientStore'
import { useNavigation } from '../../../shared/composables/useNavigation'
import MaskedNIK from '../../../shared/components/MaskedNIK.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const patientStore = usePatientStore()
const { goBack, backButtonText } = useNavigation()

const activeTab = ref('overview')
const tabs = [
  { id: 'overview', label: 'Ikhtisar' },
  { id: 'assessment', label: 'Assessment' },
  { id: 'therapy', label: 'Program Terapi' },
  { id: 'monitoring', label: 'Monitoring' }
]

const patient = computed(() => patientStore.currentPatient)
const canManage = computed(() => authStore.isAdmin || authStore.isDokter)

onMounted(async () => {
  const result = await patientStore.fetchPatientById(route.params.id)
  if (!result.success) {
    alert(result.error)
    router.push('/patients')
  }
})

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric'
  })
}
</script>

<style scoped>
.page-container {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
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

.page-actions {
  display: flex;
  gap: 0.75rem;
}

.detail-grid {
  display: grid;
  grid-template-columns: 350px 1fr;
  gap: 2rem;
}

/* Sidebar Styling */
.sidebar {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.profile-card {
  background: white;
  padding: 2.5rem 1.5rem;
  border-radius: 1rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  text-align: center;
}

.avatar-large {
  width: 80px;
  height: 80px;
  background: #3b82f6;
  color: white;
  font-size: 2.5rem;
  font-weight: 700;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
}

.patient-name {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 0.25rem;
}

.patient-nrm {
  color: #64748b;
  font-weight: 600;
  margin-bottom: 1rem;
}

.patient-badge {
  display: inline-block;
  padding: 0.25rem 1rem;
  border-radius: 1rem;
  font-size: 0.75rem;
  font-weight: 600;
  margin-bottom: 2rem;
}

.patient-badge.male { background: #eff6ff; color: #3b82f6; }
.patient-badge.female { background: #fdf2f8; color: #ec4899; }

.stats-mini {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1.5rem;
  border-top: 1px solid #f1f5f9;
  padding-top: 1.5rem;
}

.stat-item {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
}

.stat-label {
  font-size: 0.75rem;
  color: #94a3b8;
}

.stat-divider {
  width: 1px;
  height: 2rem;
  background: #e2e8f0;
}

.info-card {
  background: white;
  padding: 1.5rem;
  border-radius: 1rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.card-title {
  font-size: 1rem;
  font-weight: 600;
  color: #334155;
  margin-bottom: 1.25rem;
}

.info-list {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.info-item label {
  font-size: 0.75rem;
  color: #94a3b8;
  display: block;
  margin-bottom: 0.25rem;
}

.info-item p {
  font-size: 0.9375rem;
  color: #334155;
  font-weight: 500;
}

/* Main Content Styling */
.main-content {
  background: white;
  border-radius: 1rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.tabs-header {
  display: flex;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

.tab-btn {
  padding: 1rem 1.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #64748b;
  border: none;
  background: none;
  cursor: pointer;
  transition: all 0.2s;
  border-bottom: 2px solid transparent;
}

.tab-btn:hover {
  color: #1e293b;
  background: #f1f5f9;
}

.tab-btn.active {
  color: #3b82f6;
  border-bottom-color: #3b82f6;
  background: white;
}

.tab-content {
  padding: 2rem;
  flex: 1;
}

.content-section {
  margin-bottom: 2.5rem;
}

.section-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.section-title::before {
  content: "";
  width: 4px;
  height: 1.25rem;
  background: #3b82f6;
  border-radius: 2px;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

.memo-box {
  background: #f8fafc;
  padding: 1.5rem;
  border-radius: 0.75rem;
  border: 1px solid #e2e8f0;
  color: #475569;
  line-height: 1.6;
}

.usia-badge {
  font-size: 0.8rem;
  color: #64748b;
  font-weight: 400;
}

.btn-primary {
  background: #2563eb;
  color: white;
  padding: 0.625rem 1.25rem;
  border-radius: 0.5rem;
  text-decoration: none;
  font-weight: 600;
  font-size: 0.875rem;
}

.btn-secondary {
  background: white;
  color: #475569;
  padding: 0.625rem 1.25rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  font-weight: 600;
  font-size: 0.875rem;
  text-decoration: none;
}

.loading-state {
  text-align: center;
  padding: 5rem;
}

.loading-spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #3b82f6;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
  margin: 0 auto 1.5rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@media (max-width: 1024px) {
  .detail-grid {
    grid-template-columns: 1fr;
  }
}
</style>
