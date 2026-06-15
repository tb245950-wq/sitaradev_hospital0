<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h1 class="page-title">Edit Data Pasien</h1>
        <p class="page-subtitle">Perbarui informasi rekam medis pasien</p>
      </div>
      <router-link to="/patients" class="btn-secondary">
        ← Kembali
      </router-link>
    </div>

    <div v-if="patientStore.loading && !form.nama_lengkap" class="loading-state">
      <div class="loading-spinner"></div>
      <p>Memuat data pasien...</p>
    </div>

    <div v-else class="form-card">
      <form @submit.prevent="handleSubmit">
        <div class="form-section">
          <h2 class="section-title">Data Identitas Anak</h2>
          <div class="form-grid">
            <div class="form-group">
              <label>Nomor Rekam Medis (NRM)</label>
              <input 
                v-model="form.nrm" 
                type="text" 
                required 
                class="form-input"
              />
            </div>
            <div class="form-group">
              <label>NIK Anak</label>
              <input 
                v-model="form.nik" 
                type="text" 
                required 
                class="form-input"
              />
            </div>
            <div class="form-group span-2">
              <label>Nama Lengkap</label>
              <input 
                v-model="form.nama_lengkap" 
                type="text" 
                required 
                class="form-input"
              />
            </div>
            <div class="form-group">
              <label>Nama Panggilan</label>
              <input 
                v-model="form.nama_panggilan" 
                type="text" 
                class="form-input"
              />
            </div>
            <div class="form-group">
              <label>Jenis Kelamin</label>
              <select v-model="form.jenis_kelamin" required class="form-input">
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
              </select>
            </div>
            <div class="form-group">
              <label>Tanggal Lahir</label>
              <input 
                v-model="form.tanggal_lahir" 
                type="date" 
                required 
                class="form-input"
              />
            </div>
          </div>
        </div>

        <div class="form-section">
          <h2 class="section-title">Data Orang Tua / Wali</h2>
          <div class="form-grid">
            <div class="form-group">
              <label>Nama Wali</label>
              <input 
                v-model="form.nama_wali" 
                type="text" 
                required 
                class="form-input"
              />
            </div>
            <div class="form-group">
              <label>Hubungan Wali</label>
              <input 
                v-model="form.hubungan_wali" 
                type="text" 
                required 
                class="form-input"
              />
            </div>
            <div class="form-group">
              <label>No. Telepon Wali</label>
              <input 
                v-model="form.no_telepon_wali" 
                type="text" 
                required 
                class="form-input"
              />
            </div>
            <div class="form-group span-3">
              <label>Alamat Lengkap</label>
              <textarea 
                v-model="form.alamat" 
                required 
                rows="3"
                class="form-input"
              ></textarea>
            </div>
          </div>
        </div>

        <div class="form-section">
          <h2 class="section-title">Informasi Medis Tambahan</h2>
          <div class="form-group">
            <label>Riwayat Medis (Opsional)</label>
            <textarea 
              v-model="form.riwayat_medis" 
              rows="4"
              class="form-input"
            ></textarea>
          </div>
        </div>

        <div class="form-actions">
          <button 
            type="button" 
            @click="$router.push('/patients')" 
            class="btn-secondary"
            :disabled="patientStore.loading"
          >
            Batal
          </button>
          <button 
            type="submit" 
            class="btn-primary" 
            :disabled="patientStore.loading"
          >
            {{ patientStore.loading ? 'Menyimpan...' : 'Perbarui Data' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../../auth/stores/authStore'
import { usePatientStore } from '../stores/patientStore'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const patientStore = usePatientStore()

const form = ref({
  nrm: '',
  nik: '',
  nama_lengkap: '',
  nama_panggilan: '',
  tanggal_lahir: '',
  jenis_kelamin: '',
  alamat: '',
  no_telepon_wali: '',
  nama_wali: '',
  hubungan_wali: '',
  riwayat_medis: ''
})

onMounted(async () => {
  // RBAC Check
  if (authStore.isTerapis) {
    router.push('/unauthorized')
    return
  }

  const patientId = route.params.id
  const result = await patientStore.fetchPatientById(patientId)
  
  if (result.success) {
    const data = result.data
    form.value = {
      nrm: data.nrm,
      nik: data.nik,
      nama_lengkap: data.nama,
      nama_panggilan: data.nama_panggilan,
      tanggal_lahir: data.info_lahir.tanggal,
      jenis_kelamin: data.jenis_kelamin === 'Laki-laki' ? 'L' : 'P',
      alamat: data.alamat,
      no_telepon_wali: data.wali.kontak,
      nama_wali: data.wali.nama,
      hubungan_wali: data.wali.hubungan,
      riwayat_medis: data.riwayat_medis
    }
  } else {
    alert(result.error)
    router.push('/patients')
  }
})

const handleSubmit = async () => {
  const result = await patientStore.updatePatient(route.params.id, form.value)
  if (result.success) {
    alert('Data pasien berhasil diperbarui!')
    router.push('/patients')
  } else {
    alert(result.error || 'Terjadi kesalahan saat memperbarui data')
  }
}
</script>

<style scoped>
/* Same styles as CreateView */
.page-container {
  padding: 2rem;
  max-width: 1000px;
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

.form-card {
  background: white;
  padding: 2rem;
  border-radius: 1rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.form-section {
  margin-bottom: 2.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #f1f5f9;
}

.form-section:last-of-type {
  border-bottom: none;
}

.section-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #334155;
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.section-title::before {
  content: "";
  display: block;
  width: 4px;
  height: 1.25rem;
  background: #3b82f6;
  border-radius: 2px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.span-2 {
  grid-column: span 2;
}

.span-3 {
  grid-column: span 3;
}

label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #64748b;
}

.form-input {
  padding: 0.625rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  transition: all 0.2s;
}

.form-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

textarea.form-input {
  resize: vertical;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1rem;
}

.btn-primary {
  background: #2563eb;
  color: white;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-primary:hover:not(:disabled) {
  background: #1d4ed8;
}

.btn-secondary {
  background: white;
  color: #475569;
  padding: 0.75rem 1.5rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  transition: background 0.2s;
  display: inline-flex;
  align-items: center;
}

.btn-secondary:hover:not(:disabled) {
  background: #f8fafc;
}

.loading-state {
  text-align: center;
  padding: 4rem;
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

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  .span-2, .span-3 {
    grid-column: span 1;
  }
}
</style>
