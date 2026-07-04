<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <button @click="goBackHistory" class="btn-back">
          <span class="arrow">←</span>
          <span>Kembali</span>
        </button>
        <h1 class="page-title">Tambah Pasien Baru</h1>
        <p class="page-subtitle">Daftarkan pasien rekam medis baru ke sistem</p>
      </div>
    </div>

    <div class="form-card">
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
                placeholder="Contoh: RM-2026-0001"
                class="form-input"
              />
            </div>
            <div class="form-group">
              <label>NIK Anak</label>
              <input 
                v-model="form.nik" 
                type="text" 
                required 
                placeholder="16 digit NIK"
                maxlength="16"
                class="form-input"
                @input="form.nik = form.nik.replace(/\D/g, '')"
              />
              <div class="field-hint">
                <span class="hint-icon">🔒</span>
                NIK akan disimpan terenkripsi. Tampil sebagai:
                <span class="nik-preview">{{ nikPreview }}</span>
              </div>
            </div>
            <div class="form-group span-2">
              <label>Nama Lengkap</label>
              <input 
                v-model="form.nama_lengkap" 
                type="text" 
                required 
                placeholder="Nama sesuai akta kelahiran"
                class="form-input"
              />
            </div>
            <div class="form-group">
              <label>Nama Panggilan</label>
              <input 
                v-model="form.nama_panggilan" 
                type="text" 
                placeholder="Nama panggilan anak"
                class="form-input"
              />
            </div>
            <div class="form-group">
              <label>Jenis Kelamin</label>
              <select v-model="form.jenis_kelamin" required class="form-input" :class="{ 'input-error': errors.jenis_kelamin }">
                <option value="">Pilih Jenis Kelamin</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
              </select>
              <span v-if="errors.jenis_kelamin" class="error-msg">{{ errors.jenis_kelamin[0] }}</span>
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
                placeholder="Nama ayah/ibu/wali"
                class="form-input"
              />
            </div>
            <div class="form-group">
              <label>Hubungan Wali</label>
              <input 
                v-model="form.hubungan_wali" 
                type="text" 
                required 
                placeholder="Ayah / Ibu / Kakek / dll"
                class="form-input"
              />
            </div>
            <div class="form-group">
              <label>No. Telepon Wali</label>
              <input 
                v-model="form.no_telepon_wali" 
                type="text" 
                required 
                placeholder="Contoh: 0812XXXXXXXX"
                class="form-input"
              />
            </div>
            <div class="form-group span-3">
              <label>Alamat Lengkap</label>
              <textarea 
                v-model="form.alamat" 
                required 
                rows="3"
                placeholder="Alamat domisili saat ini"
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
              placeholder="Catatan riwayat penyakit atau kondisi khusus..."
              class="form-input"
            ></textarea>
          </div>
        </div>

        <div class="form-actions">
          <button 
            type="button" 
            @click="cancelForm" 
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
            {{ patientStore.loading ? 'Menyimpan...' : 'Simpan Pasien' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../auth/stores/authStore'
import { usePatientStore } from '../stores/patientStore'
import { useNavigation } from '../../../shared/composables/useNavigation'

const router = useRouter()
const authStore = useAuthStore()
const patientStore = usePatientStore()
const { goBackHistory, cancelForm } = useNavigation()

// RBAC Check
onMounted(() => {
  if (authStore.isTerapis) {
    router.push('/unauthorized')
  }
})

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

// Preview NIK masking secara realtime
const nikPreview = computed(() => {
  const nik = form.value.nik
  if (!nik) return '—'
  const len   = nik.length
  const last4 = nik.slice(-4)
  const stars = '*'.repeat(Math.max(len - 4, 0))
  return len <= 4 ? nik : stars + last4
})

const errors = ref({})

const handleSubmit = async () => {
  errors.value = {}
  const result = await patientStore.createPatient(form.value)
  if (result.success) {
    alert('Data pasien berhasil ditambahkan!')
    router.push('/patients')
  } else {
    // Tampilkan validasi error dari Laravel per field
    if (result.validationErrors) {
      errors.value = result.validationErrors
    } else {
      alert(result.error || 'Terjadi kesalahan saat menyimpan data')
    }
  }
}
</script>

<style scoped>
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

.field-hint {
  font-size: 0.78rem;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 0.35rem;
  margin-top: 0.3rem;
}

.nik-preview {
  font-family: 'Courier New', Courier, monospace;
  font-weight: 600;
  color: #475569;
  background: #f1f5f9;
  padding: 0.1rem 0.4rem;
  border-radius: 0.25rem;
  border: 1px solid #e2e8f0;
  letter-spacing: 0.04em;
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

button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.error-msg {
  color: #dc2626;
  font-size: 0.8rem;
  margin-top: 0.25rem;
}

.input-error {
  border-color: #dc2626 !important;
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1) !important;
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
