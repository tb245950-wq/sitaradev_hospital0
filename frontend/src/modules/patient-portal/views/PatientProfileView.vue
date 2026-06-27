<template>
  <div class="patient-page">
    <div class="page-header">
      <h1>👤 Profil Saya</h1>
      <p>Lihat dan perbarui informasi akun Anda.</p>
    </div>

    <div v-if="loading" class="loading-state">Memuat data profil...</div>

    <div v-else class="profile-content">
      <!-- Avatar & Name -->
      <div class="profile-card">
        <div class="avatar">{{ initials }}</div>
        <div class="profile-meta">
          <h2>{{ form.name }}</h2>
          <span class="role-badge">Pasien</span>
        </div>
      </div>

      <!-- Edit Form -->
      <div class="form-card">
        <h3>Informasi Pribadi</h3>
        <form @submit.prevent="handleSave" class="profile-form">
          <div class="form-group">
            <label>Nama Lengkap</label>
            <input v-model="form.name" type="text" :readonly="!editing" :class="{ editable: editing }" />
          </div>
          <div class="form-group">
            <label>Email</label>
            <input v-model="form.email" type="email" readonly class="readonly" />
            <small>Email tidak dapat diubah.</small>
          </div>
          <div class="form-group">
            <label>No. Telepon</label>
            <input v-model="form.phone" type="text" :readonly="!editing" :class="{ editable: editing }" placeholder="-" />
          </div>
          <div class="form-group">
            <label>NIK</label>
            <input v-model="form.nik" type="text" :readonly="!editing" :class="{ editable: editing }" placeholder="-" />
          </div>
          <div class="form-group">
            <label>Tanggal Lahir</label>
            <input v-model="form.tanggal_lahir" type="date" :readonly="!editing" :class="{ editable: editing }" />
          </div>
          <div class="form-group">
            <label>Alamat</label>
            <textarea v-model="form.alamat" :readonly="!editing" :class="{ editable: editing }" placeholder="-" rows="3"></textarea>
          </div>

          <div v-if="success" class="success-msg">{{ success }}</div>
          <div v-if="error" class="error-msg">{{ error }}</div>

          <div class="form-actions">
            <button v-if="!editing" type="button" @click="editing = true" class="btn-edit">Edit Profil</button>
            <template v-else>
              <button type="submit" class="btn-save" :disabled="saving">{{ saving ? 'Menyimpan...' : 'Simpan' }}</button>
              <button type="button" @click="cancelEdit" class="btn-cancel">Batal</button>
            </template>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { patientService } from '../services/patientService'

const loading = ref(false)
const saving = ref(false)
const editing = ref(false)
const error = ref(null)
const success = ref(null)
const originalForm = ref({})

const form = ref({ name: '', email: '', phone: '', nik: '', tanggal_lahir: '', alamat: '' })

const initials = computed(() => {
  const parts = (form.value.name || '?').split(' ')
  return parts.map(p => p[0]).slice(0, 2).join('').toUpperCase()
})

async function loadProfile() {
  loading.value = true
  try {
    const storedUser = patientService.getStoredUser()
    if (storedUser) {
      form.value = {
        name: storedUser.name || '',
        email: storedUser.email || '',
        phone: storedUser.phone || '',
        nik: storedUser.nik || '',
        tanggal_lahir: storedUser.tanggal_lahir || '',
        alamat: storedUser.alamat || ''
      }
      originalForm.value = { ...form.value }
    }
  } finally {
    loading.value = false
  }
}

function cancelEdit() {
  form.value = { ...originalForm.value }
  editing.value = false
  error.value = null
  success.value = null
}

async function handleSave() {
  saving.value = true
  error.value = null
  success.value = null
  try {
    const result = await patientService.updateProfile(form.value)
    if (result.success) {
      success.value = 'Profil berhasil diperbarui!'
      originalForm.value = { ...form.value }
      editing.value = false
    } else {
      error.value = result.error || 'Gagal menyimpan perubahan.'
    }
  } finally {
    saving.value = false
  }
}

onMounted(loadProfile)
</script>

<style scoped>
.patient-page { padding: 2rem; max-width: 680px; }
.page-header { margin-bottom: 2rem; }
.page-header h1 { font-size: 1.5rem; font-weight: 700; color: #1e293b; }
.page-header p { color: #64748b; margin-top: 0.25rem; }
.loading-state { color: #94a3b8; padding: 2rem; text-align: center; }
.profile-card { background: white; border-radius: 1rem; padding: 1.5rem 2rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08); display: flex; align-items: center; gap: 1.5rem; }
.avatar { width: 72px; height: 72px; background: #1a73e8; color: white; font-size: 1.5rem; font-weight: 700; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.profile-meta h2 { font-size: 1.2rem; font-weight: 700; color: #1e293b; }
.role-badge { display: inline-block; margin-top: 0.25rem; background: #dcfce7; color: #166534; font-size: 0.75rem; padding: 0.15rem 0.6rem; border-radius: 9999px; font-weight: 600; }
.form-card { background: white; border-radius: 1rem; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.form-card h3 { font-size: 1rem; font-weight: 600; color: #374151; margin-bottom: 1.5rem; }
.profile-form { display: flex; flex-direction: column; gap: 1rem; }
.form-group { display: flex; flex-direction: column; gap: 0.25rem; }
.form-group label { font-size: 0.8rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; }
.form-group input, .form-group textarea {
  padding: 0.625rem 0.875rem;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  font-size: 0.95rem;
  background: #f9fafb;
  color: #1e293b;
  resize: none;
}
.form-group input.editable, .form-group textarea.editable { background: white; border-color: #1a73e8; }
.form-group input.editable:focus, .form-group textarea.editable:focus { outline: none; box-shadow: 0 0 0 3px rgba(26,115,232,0.15); }
.form-group input.readonly { background: #f3f4f6; color: #9ca3af; cursor: not-allowed; }
.form-group small { color: #9ca3af; font-size: 0.78rem; }
.success-msg { background: #dcfce7; color: #16a34a; padding: 0.75rem; border-radius: 0.5rem; font-size: 0.875rem; }
.error-msg { background: #fee2e2; color: #dc2626; padding: 0.75rem; border-radius: 0.5rem; font-size: 0.875rem; }
.form-actions { display: flex; gap: 0.75rem; margin-top: 0.5rem; }
.btn-edit { background: #1a73e8; color: white; border: none; padding: 0.625rem 1.5rem; border-radius: 0.5rem; font-weight: 600; cursor: pointer; }
.btn-save { background: #16a34a; color: white; border: none; padding: 0.625rem 1.5rem; border-radius: 0.5rem; font-weight: 600; cursor: pointer; }
.btn-save:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-cancel { background: white; color: #6b7280; border: 1px solid #d1d5db; padding: 0.625rem 1.5rem; border-radius: 0.5rem; font-weight: 600; cursor: pointer; }
</style>
