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
        <!-- Banner profil belum lengkap -->
        <div v-if="!profileComplete" class="alert-incomplete">
          <span class="alert-icon">⚠️</span>
          <div>
            <strong>Profil belum lengkap!</strong>
            <p>Lengkapi data berikut sebelum bisa melakukan booking: <strong>{{ missingFields.join(', ') }}</strong></p>
          </div>
          <button @click="editing = true" class="btn-complete-now">Lengkapi Sekarang</button>
        </div>

        <!-- Avatar Card -->
        <div class="avatar-card">
          <div class="avatar">{{ initials }}</div>
          <div class="avatar-info">
            <h2>{{ form.name }}</h2>
            <span class="role-badge">Pasien</span>
            <span class="nrm-badge" v-if="nrm">NRM: {{ nrm }}</span>
          </div>
          <div class="profile-status-badge" :class="profileComplete ? 'complete' : 'incomplete'">
            {{ profileComplete ? '✅ Profil Lengkap' : '⚠️ Belum Lengkap' }}
          </div>
        </div>

        <!-- Form -->
        <div class="form-card">
          <div class="form-card-header">
            <h3>Informasi Pribadi</h3>
            <span class="required-note">* Wajib diisi untuk booking</span>
          </div>

          <form @submit.prevent="handleSave">
            <!-- Data Diri -->
            <div class="section-label">Data Diri</div>
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
                <label>NIK <span class="required-star">*</span></label>
                <input
                  v-model="form.nik"
                  :readonly="!editing"
                  :class="{ editable: editing, 'field-missing': !form.nik && !editing }"
                  placeholder="16 digit NIK"
                  maxlength="16"
                />
              </div>
              <div class="form-group">
                <label>Tanggal Lahir <span class="required-star">*</span></label>
                <input
                  v-model="form.date_of_birth"
                  type="date"
                  :readonly="!editing"
                  :class="{ editable: editing, 'field-missing': !form.date_of_birth && !editing }"
                />
              </div>
              <div class="form-group">
                <label>Jenis Kelamin <span class="required-star">*</span></label>
                <select
                  v-if="editing"
                  v-model="form.gender"
                  class="editable"
                >
                  <option value="">-- Pilih --</option>
                  <option value="L">Laki-laki</option>
                  <option value="P">Perempuan</option>
                </select>
                <input
                  v-else
                  :value="form.gender === 'L' ? 'Laki-laki' : form.gender === 'P' ? 'Perempuan' : '-'"
                  readonly
                  :class="{ 'field-missing': !form.gender }"
                />
              </div>
            </div>

            <div class="form-group full">
              <label>Alamat <span class="required-star">*</span></label>
              <textarea
                v-model="form.address"
                :readonly="!editing"
                :class="{ editable: editing, 'field-missing': !form.address && !editing }"
                rows="3"
                placeholder="Alamat lengkap"
              ></textarea>
            </div>

            <!-- Data Wali -->
            <div class="section-label" style="margin-top: 1.5rem;">Data Wali / Orang Tua</div>
            <div class="form-grid">
              <div class="form-group">
                <label>Nama Wali <span class="required-star">*</span></label>
                <input
                  v-model="form.parent_name"
                  :readonly="!editing"
                  :class="{ editable: editing, 'field-missing': !form.parent_name && !editing }"
                  placeholder="Nama orang tua / wali"
                />
              </div>
              <div class="form-group">
                <label>No. Telepon Wali <span class="required-star">*</span></label>
                <input
                  v-model="form.parent_phone"
                  :readonly="!editing"
                  :class="{ editable: editing, 'field-missing': !form.parent_phone && !editing }"
                  placeholder="08xxxxxxxxxx"
                />
              </div>
              <div class="form-group">
                <label>Hubungan Wali <span class="required-star">*</span></label>
                <select
                  v-if="editing"
                  v-model="form.parent_relation"
                  class="editable"
                >
                  <option value="">-- Pilih --</option>
                  <option value="Ayah">Ayah</option>
                  <option value="Ibu">Ibu</option>
                  <option value="Kakak">Kakak</option>
                  <option value="Paman">Paman</option>
                  <option value="Bibi">Bibi</option>
                  <option value="Wali">Wali</option>
                  <option value="Lainnya">Lainnya</option>
                </select>
                <input
                  v-else
                  :value="form.parent_relation || '-'"
                  readonly
                  :class="{ 'field-missing': !form.parent_relation }"
                />
              </div>
            </div>

            <div v-if="success" class="alert-success">✅ {{ success }}</div>
            <div v-if="error" class="alert-error">❌ {{ error }}</div>

            <div class="form-actions">
              <button v-if="!editing" type="button" @click="editing = true" class="btn-primary">✏️ Edit Profil</button>
              <template v-else>
                <button type="submit" class="btn-primary" :disabled="saving">
                  {{ saving ? 'Menyimpan...' : '💾 Simpan' }}
                </button>
                <button type="button" @click="cancelEdit" class="btn-secondary">Batal</button>
              </template>
            </div>
          </form>
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

const loading       = ref(false)
const saving        = ref(false)
const editing       = ref(false)
const error         = ref(null)
const success       = ref(null)
const nrm           = ref('')
const profileComplete = ref(true)
const missingFields   = ref([])
const original      = ref({})

const form = ref({
  name:            '',
  email:           '',
  nik:             '',
  date_of_birth:   '',
  gender:          '',
  address:         '',
  parent_name:     '',
  parent_phone:    '',
  parent_relation: '',
})

const initials = computed(() =>
  (form.value.name || '?').split(' ').map(p => p[0]).slice(0, 2).join('').toUpperCase()
)

const loadProfile = async () => {
  loading.value = true
  try {
    // Ambil data profil dari API
    const result = await patientService.getProfile()
    if (result.success && result.data) {
      const { user, patient } = result.data

      nrm.value = patient?.nrm || ''

      form.value = {
        name:            user?.name             || '',
        email:           user?.email            || '',
        nik:             patient?.nik           || '',
        date_of_birth:   patient?.tanggal_lahir || '',
        gender:          patient?.jenis_kelamin || '',
        address:         patient?.alamat        || '',
        parent_name:     patient?.nama_wali     || '',
        parent_phone:    patient?.no_telepon_wali || '',
        parent_relation: patient?.hubungan_wali || '',
      }
    } else {
      // Fallback ke localStorage jika API gagal
      const stored = patientService.getStoredUser()
      if (stored) {
        form.value.name  = stored.name  || ''
        form.value.email = stored.email || ''
      }
    }

    original.value = { ...form.value }

    // Cek kelengkapan profil
    const statusResult = await patientService.getProfileStatus()
    if (statusResult.success) {
      profileComplete.value = statusResult.data?.is_complete ?? true
      missingFields.value   = statusResult.data?.missing     ?? []
    }
  } finally {
    loading.value = false
  }
}

const cancelEdit = () => {
  form.value = { ...original.value }
  editing.value = false
  error.value   = null
  success.value = null
}

const handleSave = async () => {
  saving.value  = true
  error.value   = null
  success.value = null

  try {
    const result = await patientService.updateProfile({
      name:            form.value.name,
      nik:             form.value.nik,
      date_of_birth:   form.value.date_of_birth,
      gender:          form.value.gender,
      address:         form.value.address,
      parent_name:     form.value.parent_name,
      parent_phone:    form.value.parent_phone,
      parent_relation: form.value.parent_relation,
    })

    if (result.success) {
      success.value     = 'Profil berhasil diperbarui!'
      original.value    = { ...form.value }
      editing.value     = false

      // Refresh status kelengkapan profil
      const statusResult = await patientService.getProfileStatus()
      if (statusResult.success) {
        profileComplete.value = statusResult.data?.is_complete ?? true
        missingFields.value   = statusResult.data?.missing     ?? []
      }
    } else {
      error.value = result.error || 'Gagal menyimpan perubahan'
    }
  } finally {
    saving.value = false
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

onMounted(loadProfile)
</script>

<style scoped>
.patient-dashboard { display: flex; min-height: 100vh; background: #f8fafc; }

/* ── Sidebar ── */
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

/* ── Main ── */
.main-content { flex: 1; margin-left: 260px; padding: 2rem; max-width: 800px; }
.content-header { margin-bottom: 2rem; }
.btn-back { background: none; border: none; color: #059669; cursor: pointer; font-weight: 600; padding: 0; margin-bottom: 0.5rem; display: block; }
.content-header h1 { font-size: 1.75rem; color: #1e293b; margin: 0.25rem 0; }
.content-header p { color: #64748b; margin: 0; }

/* ── Loading ── */
.loading-container { text-align: center; padding: 3rem; }
.loading-spinner { width: 40px; height: 40px; border: 3px solid #f1f5f9; border-top-color: #10b981; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Profile content ── */
.profile-content { display: flex; flex-direction: column; gap: 1.25rem; }

/* ── Incomplete banner ── */
.alert-incomplete {
  background: #fffbeb;
  border: 1.5px solid #f59e0b;
  border-radius: 0.75rem;
  padding: 1rem 1.25rem;
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
}
.alert-incomplete .alert-icon { font-size: 1.5rem; flex-shrink: 0; margin-top: 0.1rem; }
.alert-incomplete div { flex: 1; }
.alert-incomplete strong { color: #92400e; display: block; margin-bottom: 0.25rem; }
.alert-incomplete p { margin: 0; font-size: 0.875rem; color: #78350f; }
.btn-complete-now { background: #f59e0b; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 600; cursor: pointer; white-space: nowrap; flex-shrink: 0; }
.btn-complete-now:hover { background: #d97706; }

/* ── Avatar card ── */
.avatar-card { background: white; border-radius: 0.75rem; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08); display: flex; align-items: center; gap: 1.25rem; }
.avatar { width: 64px; height: 64px; background: #10b981; color: white; font-size: 1.4rem; font-weight: 700; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.avatar-info { flex: 1; }
.avatar-info h2 { font-size: 1.15rem; font-weight: 700; color: #1e293b; margin: 0 0 0.4rem; }
.role-badge { background: #dcfce7; color: #166534; font-size: 0.7rem; padding: 0.15rem 0.5rem; border-radius: 9999px; font-weight: 600; margin-right: 0.5rem; }
.nrm-badge { background: #e0f2fe; color: #0369a1; font-size: 0.7rem; padding: 0.15rem 0.5rem; border-radius: 9999px; font-weight: 600; }
.profile-status-badge { padding: 0.35rem 0.75rem; border-radius: 0.5rem; font-size: 0.8rem; font-weight: 600; white-space: nowrap; }
.profile-status-badge.complete { background: #dcfce7; color: #15803d; }
.profile-status-badge.incomplete { background: #fef3c7; color: #92400e; }

/* ── Form card ── */
.form-card { background: white; border-radius: 0.75rem; padding: 1.75rem 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.form-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
.form-card-header h3 { font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0; }
.required-note { font-size: 0.75rem; color: #ef4444; }
.required-star { color: #ef4444; }

.section-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: #6b7280; letter-spacing: 0.08em; margin-bottom: 0.75rem; }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
.form-group { display: flex; flex-direction: column; gap: 0.3rem; }
.form-group.full { margin-bottom: 1rem; }
.form-group label { font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; }

.form-group input,
.form-group select,
.form-group textarea {
  padding: 0.6rem 0.875rem;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  font-size: 0.9375rem;
  background: #f9fafb;
  color: #1e293b;
  resize: none;
}
.form-group select { cursor: pointer; }
.form-group input.editable,
.form-group select.editable,
.form-group textarea.editable {
  background: white;
  border-color: #10b981;
}
.form-group input.editable:focus,
.form-group select.editable:focus,
.form-group textarea.editable:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(16,185,129,0.12);
}
.form-group input.readonly { background: #f3f4f6; color: #9ca3af; cursor: not-allowed; }
/* Field yang belum diisi saat view mode */
.field-missing { border-color: #fbbf24 !important; background: #fffbeb !important; }

.alert-success { background: #dcfce7; color: #16a34a; padding: 0.75rem; border-radius: 0.5rem; font-size: 0.875rem; margin-bottom: 1rem; }
.alert-error { background: #fee2e2; color: #dc2626; padding: 0.75rem; border-radius: 0.5rem; font-size: 0.875rem; margin-bottom: 1rem; }

.form-actions { display: flex; gap: 0.75rem; margin-top: 1.5rem; }
.btn-primary { background: #10b981; color: white; border: none; padding: 0.625rem 1.5rem; border-radius: 0.5rem; font-weight: 600; cursor: pointer; font-size: 0.9rem; }
.btn-primary:hover:not(:disabled) { background: #059669; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secondary { background: white; color: #6b7280; border: 1px solid #d1d5db; padding: 0.625rem 1.5rem; border-radius: 0.5rem; font-weight: 600; cursor: pointer; font-size: 0.9rem; }

@media (max-width: 768px) {
  .patient-sidebar { transform: translateX(-100%); }
  .main-content { margin-left: 0; padding: 1rem; }
  .form-grid { grid-template-columns: 1fr; }
  .avatar-card { flex-wrap: wrap; }
}
</style>
