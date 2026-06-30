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
        <router-link to="/pasien/riwayat" class="nav-item">Riwayat Medis</router-link>
        <router-link to="/pasien/profil" class="nav-item active">Profil Saya</router-link>
      </nav>
      <div class="sidebar-footer">
        <button @click="handleLogout" class="btn-logout">Logout</button>
      </div>
    </aside>

    <main class="main-content">
      <div class="content-header">
        <button @click="$router.push('/pasien/dashboard')" class="btn-back">← Kembali</button>
        <h1>Profil Saya</h1>
        <p>Lihat dan perbarui informasi akun Anda</p>
      </div>

      <div v-if="loading" class="loading-container">
        <div class="loading-spinner"></div>
      </div>

      <div v-else class="profile-content">
        <!-- Avatar Card -->
        <div class="avatar-card">
          <div class="avatar">{{ initials }}</div>
          <div>
            <h2>{{ form.name }}</h2>
            <span class="role-badge">Pasien</span>
          </div>
        </div>

        <!-- Form -->
        <div class="form-card">
          <h3>Informasi Pribadi</h3>
          <form @submit.prevent="handleSave">
            <div class="form-grid">
              <div class="form-group">
                <label>Nama Lengkap</label>
                <input v-model="form.name" :readonly="!editing" :class="{ editable: editing }" />
              </div>
              <div class="form-group">
                <label>Email</label>
                <input v-model="form.email" readonly class="readonly" />
              </div>
              <div class="form-group">
                <label>Tanggal Lahir</label>
                <input v-model="form.date_of_birth" type="date" :readonly="!editing" :class="{ editable: editing }" />
              </div>
              <div class="form-group">
                <label>Nama Wali</label>
                <input v-model="form.parent_name" :readonly="!editing" :class="{ editable: editing }" />
              </div>
              <div class="form-group">
                <label>No. Telepon Wali</label>
                <input v-model="form.parent_phone" :readonly="!editing" :class="{ editable: editing }" />
              </div>
            </div>
            <div class="form-group full">
              <label>Alamat</label>
              <textarea v-model="form.address" :readonly="!editing" :class="{ editable: editing }" rows="3"></textarea>
            </div>

            <div v-if="success" class="alert-success">{{ success }}</div>
            <div v-if="error" class="alert-error">{{ error }}</div>

            <div class="form-actions">
              <button v-if="!editing" type="button" @click="editing = true" class="btn-primary">Edit Profil</button>
              <template v-else>
                <button type="submit" class="btn-primary" :disabled="saving">{{ saving ? 'Menyimpan...' : 'Simpan' }}</button>
                <button type="button" @click="cancelEdit" class="btn-secondary">Batal</button>
              </template>
            </div>
          </form>
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
const loading = ref(false)
const saving = ref(false)
const editing = ref(false)
const error = ref(null)
const success = ref(null)
const original = ref({})

const form = ref({ name: '', email: '', date_of_birth: '', address: '', parent_name: '', parent_phone: '' })

const initials = computed(() => {
  return (form.value.name || '?').split(' ').map(p => p[0]).slice(0, 2).join('').toUpperCase()
})

const loadProfile = async () => {
  loading.value = true
  try {
    const result = await patientService.getProfile ? patientService.getProfile() : null
    const stored = patientService.getStoredUser()
    if (stored) {
      form.value = {
        name: stored.name || '',
        email: stored.email || '',
        date_of_birth: '',
        address: '',
        parent_name: '',
        parent_phone: ''
      }
      original.value = { ...form.value }
    }
  } finally {
    loading.value = false
  }
}

const cancelEdit = () => {
  form.value = { ...original.value }
  editing.value = false
  error.value = null
  success.value = null
}

const handleSave = async () => {
  saving.value = true
  error.value = null
  success.value = null
  try {
    const result = await patientService.updateProfile(form.value)
    if (result.success) {
      success.value = 'Profil berhasil diperbarui!'
      original.value = { ...form.value }
      editing.value = false
    } else {
      error.value = result.error || 'Gagal menyimpan perubahan'
    }
  } finally {
    saving.value = false
  }
}

const handleLogout = async () => {
  if (confirm('Yakin ingin keluar?')) {
    await patientStore.logout()
    router.push('/pasien/login')
  }
}

onMounted(loadProfile)
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

.main-content { flex: 1; margin-left: 260px; padding: 2rem; max-width: 760px; }
.content-header { margin-bottom: 2rem; }
.btn-back { background: none; border: none; color: #059669; cursor: pointer; font-weight: 600; padding: 0; margin-bottom: 0.5rem; display: block; }
.content-header h1 { font-size: 1.75rem; color: #1e293b; margin: 0.25rem 0; }
.content-header p { color: #64748b; margin: 0; }

.loading-container { text-align: center; padding: 3rem; }
.loading-spinner { width: 40px; height: 40px; border: 3px solid #f1f5f9; border-top-color: #10b981; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto; }
@keyframes spin { to { transform: rotate(360deg); } }

.profile-content { display: flex; flex-direction: column; gap: 1.5rem; }
.avatar-card { background: white; border-radius: 0.75rem; padding: 1.5rem 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08); display: flex; align-items: center; gap: 1.5rem; }
.avatar { width: 72px; height: 72px; background: #10b981; color: white; font-size: 1.5rem; font-weight: 700; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.avatar-card h2 { font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 0 0 0.25rem; }
.role-badge { background: #dcfce7; color: #166534; font-size: 0.75rem; padding: 0.2rem 0.6rem; border-radius: 9999px; font-weight: 600; }

.form-card { background: white; border-radius: 0.75rem; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.form-card h3 { font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0 0 1.5rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
.form-group { display: flex; flex-direction: column; gap: 0.25rem; }
.form-group.full { margin-bottom: 1rem; }
.form-group label { font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; }
.form-group input, .form-group textarea { padding: 0.625rem 0.875rem; border: 1px solid #e5e7eb; border-radius: 0.5rem; font-size: 0.9375rem; background: #f9fafb; color: #1e293b; resize: none; }
.form-group input.editable, .form-group textarea.editable { background: white; border-color: #10b981; }
.form-group input.editable:focus, .form-group textarea.editable:focus { outline: none; box-shadow: 0 0 0 3px rgba(16,185,129,0.1); }
.form-group input.readonly { background: #f3f4f6; color: #9ca3af; cursor: not-allowed; }

.alert-success { background: #dcfce7; color: #16a34a; padding: 0.75rem; border-radius: 0.5rem; font-size: 0.875rem; margin-bottom: 1rem; }
.alert-error { background: #fee2e2; color: #dc2626; padding: 0.75rem; border-radius: 0.5rem; font-size: 0.875rem; margin-bottom: 1rem; }

.form-actions { display: flex; gap: 0.75rem; }
.btn-primary { background: #10b981; color: white; border: none; padding: 0.625rem 1.5rem; border-radius: 0.5rem; font-weight: 600; cursor: pointer; }
.btn-primary:hover:not(:disabled) { background: #059669; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secondary { background: white; color: #6b7280; border: 1px solid #d1d5db; padding: 0.625rem 1.5rem; border-radius: 0.5rem; font-weight: 600; cursor: pointer; }

@media (max-width: 768px) {
  .patient-sidebar { transform: translateX(-100%); }
  .main-content { margin-left: 0; padding: 1rem; }
  .form-grid { grid-template-columns: 1fr; }
}
</style>
