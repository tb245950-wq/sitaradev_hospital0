<template>
  <div class="page-container">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Manajemen User</h1>
        <p class="page-subtitle">Kelola akun Admin, Dokter, dan Terapis</p>
      </div>
      <button @click="openCreateModal" class="btn-primary">+ Tambah User</button>
    </div>

    <!-- Filters -->
    <div class="filters-card">
      <div class="filters-grid">
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Cari nama atau email..."
          @input="debouncedSearch"
          class="form-input"
        />
        <select v-model="roleFilter" @change="applyFilters" class="form-select">
          <option value="">Semua Role</option>
          <option value="admin">Admin</option>
          <option value="dokter">Dokter</option>
          <option value="terapis">Terapis</option>
        </select>
        <select v-model="statusFilter" @change="applyFilters" class="form-select">
          <option value="">Semua Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
          <option value="suspended">Suspended</option>
        </select>
      </div>
    </div>

    <!-- Error global -->
    <div v-if="globalError" class="alert-error">{{ globalError }}</div>

    <!-- Tabel -->
    <div class="content-card">
      <div v-if="userStore.loading" class="loading-state">Memuat data...</div>
      <div v-else-if="filteredUsers.length === 0" class="empty-state">
        Tidak ada user ditemukan.
      </div>
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>Nama</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Terakhir Login</th>
            <th class="text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in filteredUsers" :key="user.id">
            <td>
              <div class="user-cell">
                <div class="user-avatar">{{ initials(user.name) }}</div>
                <div>
                  <div class="user-name">{{ user.name }}</div>
                  <div class="user-nip">{{ user.nip || '—' }}</div>
                </div>
              </div>
            </td>
            <td>{{ user.email }}</td>
            <td><span class="badge" :class="`badge-${user.role}`">{{ user.role }}</span></td>
            <td><span class="badge" :class="`badge-status-${user.status}`">{{ user.status }}</span></td>
            <td>{{ formatDate(user.last_login_at) }}</td>
            <td class="text-right action-cell">
              <button @click="openEditModal(user)" class="btn-icon" title="Edit">✏️</button>
              <button @click="openResetModal(user)" class="btn-icon" title="Reset Password">🔑</button>
              <button @click="confirmDelete(user)" class="btn-icon btn-icon-danger" title="Hapus">🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- ─── MODAL: Create / Edit User ─────────────────────────── -->
    <div v-if="showFormModal" class="modal-overlay" @click.self="closeFormModal">
      <div class="modal">
        <div class="modal-header">
          <h2>{{ isEditing ? 'Edit User' : 'Tambah User Baru' }}</h2>
          <button @click="closeFormModal" class="btn-close">✕</button>
        </div>

        <form @submit.prevent="submitForm" class="modal-body">
          <!-- Nama -->
          <div class="form-group">
            <label>Nama Lengkap <span class="required">*</span></label>
            <input
              v-model="form.name"
              type="text"
              class="form-input"
              :class="{ 'input-error': formErrors.name }"
              placeholder="Dr. Ahmad Fauzi"
            />
            <span v-if="formErrors.name" class="error-msg">{{ formErrors.name }}</span>
          </div>

          <!-- Email -->
          <div class="form-group">
            <label>Email <span class="required">*</span></label>
            <input
              v-model="form.email"
              type="email"
              class="form-input"
              :class="{ 'input-error': formErrors.email }"
              placeholder="user@sitara.com"
            />
            <span v-if="formErrors.email" class="error-msg">{{ formErrors.email }}</span>
          </div>

          <!-- Role -->
          <div class="form-group">
            <label>Role <span class="required">*</span></label>
            <select
              v-model="form.role"
              class="form-select"
              :class="{ 'input-error': formErrors.role }"
            >
              <option value="">-- Pilih Role --</option>
              <option value="admin">Admin</option>
              <option value="dokter">Dokter</option>
              <option value="terapis">Terapis</option>
            </select>
            <span v-if="formErrors.role" class="error-msg">{{ formErrors.role }}</span>
          </div>

          <!-- NIP -->
          <div class="form-group">
            <label>NIP</label>
            <input
              v-model="form.nip"
              type="text"
              class="form-input"
              placeholder="NIP (opsional)"
            />
          </div>

          <!-- Status (hanya edit) -->
          <div v-if="isEditing" class="form-group">
            <label>Status <span class="required">*</span></label>
            <select v-model="form.status" class="form-select">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="suspended">Suspended</option>
            </select>
          </div>

          <!-- Password (hanya create) -->
          <template v-if="!isEditing">
            <div class="form-group">
              <label>Password <span class="required">*</span></label>
              <input
                v-model="form.password"
                type="password"
                class="form-input"
                :class="{ 'input-error': formErrors.password }"
                placeholder="Min. 8 karakter"
              />
              <span v-if="formErrors.password" class="error-msg">{{ formErrors.password }}</span>
            </div>
            <div class="form-group">
              <label>Konfirmasi Password <span class="required">*</span></label>
              <input
                v-model="form.password_confirmation"
                type="password"
                class="form-input"
                :class="{ 'input-error': formErrors.password_confirmation }"
                placeholder="Ulangi password"
              />
              <span v-if="formErrors.password_confirmation" class="error-msg">{{ formErrors.password_confirmation }}</span>
            </div>
          </template>

          <!-- API error -->
          <div v-if="formApiError" class="alert-error">{{ formApiError }}</div>

          <div class="modal-footer">
            <button type="button" @click="closeFormModal" class="btn-secondary">Batal</button>
            <button type="submit" class="btn-primary" :disabled="formLoading">
              {{ formLoading ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Buat User') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ─── MODAL: Reset Password ──────────────────────────────── -->
    <div v-if="showResetModal" class="modal-overlay" @click.self="showResetModal = false">
      <div class="modal modal-sm">
        <div class="modal-header">
          <h2>Reset Password</h2>
          <button @click="showResetModal = false" class="btn-close">✕</button>
        </div>
        <form @submit.prevent="submitReset" class="modal-body">
          <p class="modal-desc">Reset password untuk <strong>{{ selectedUser?.name }}</strong></p>
          <div class="form-group">
            <label>Password Baru <span class="required">*</span></label>
            <input
              v-model="resetForm.password"
              type="password"
              class="form-input"
              :class="{ 'input-error': resetErrors.password }"
              placeholder="Min. 8 karakter"
            />
            <span v-if="resetErrors.password" class="error-msg">{{ resetErrors.password }}</span>
          </div>
          <div class="form-group">
            <label>Konfirmasi Password <span class="required">*</span></label>
            <input
              v-model="resetForm.password_confirmation"
              type="password"
              class="form-input"
              placeholder="Ulangi password baru"
            />
          </div>
          <div v-if="resetApiError" class="alert-error">{{ resetApiError }}</div>
          <div class="modal-footer">
            <button type="button" @click="showResetModal = false" class="btn-secondary">Batal</button>
            <button type="submit" class="btn-primary" :disabled="resetLoading">
              {{ resetLoading ? 'Mereset...' : 'Reset Password' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ─── MODAL: Konfirmasi Hapus ────────────────────────────── -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal modal-sm">
        <div class="modal-header">
          <h2>Hapus User</h2>
          <button @click="showDeleteModal = false" class="btn-close">✕</button>
        </div>
        <div class="modal-body">
          <p class="modal-desc">
            Yakin ingin menghapus <strong>{{ selectedUser?.name }}</strong> ({{ selectedUser?.email }})?
            Tindakan ini tidak dapat dibatalkan.
          </p>
          <div v-if="deleteApiError" class="alert-error">{{ deleteApiError }}</div>
          <div class="modal-footer">
            <button @click="showDeleteModal = false" class="btn-secondary">Batal</button>
            <button @click="executeDelete" class="btn-danger" :disabled="deleteLoading">
              {{ deleteLoading ? 'Menghapus...' : 'Ya, Hapus' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast sukses -->
    <div v-if="successMsg" class="toast-success">✅ {{ successMsg }}</div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../auth/stores/authStore'
import { useUserStore } from '../stores/userStore'

const router    = useRouter()
const authStore = useAuthStore()
const userStore = useUserStore()

// ─── State filter ──────────────────────────────────────────────
const searchQuery  = ref('')
const roleFilter   = ref('')
const statusFilter = ref('')
const globalError  = ref('')
const successMsg   = ref('')

// ─── State modal form create/edit ──────────────────────────────
const showFormModal = ref(false)
const isEditing     = ref(false)
const selectedUser  = ref(null)
const formLoading   = ref(false)
const formApiError  = ref('')
const formErrors    = ref({})
const form          = ref(emptyForm())

// ─── State modal reset password ────────────────────────────────
const showResetModal = ref(false)
const resetLoading   = ref(false)
const resetApiError  = ref('')
const resetErrors    = ref({})
const resetForm      = ref({ password: '', password_confirmation: '' })

// ─── State modal delete ────────────────────────────────────────
const showDeleteModal = ref(false)
const deleteLoading   = ref(false)
const deleteApiError  = ref('')

// ─── Lifecycle ─────────────────────────────────────────────────
onMounted(() => {
  if (!authStore.user || authStore.user.role !== 'admin') {
    router.push('/unauthorized')
    return
  }
  loadUsers()
})

// ─── Helpers ───────────────────────────────────────────────────
function emptyForm() {
  return { name: '', email: '', role: '', nip: '', status: 'active', password: '', password_confirmation: '' }
}

function initials(name) {
  if (!name) return '??'
  const parts = name.trim().split(' ')
  return parts.length >= 2
    ? (parts[0][0] + parts[1][0]).toUpperCase()
    : name.substring(0, 2).toUpperCase()
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

function showToast(msg) {
  successMsg.value = msg
  setTimeout(() => { successMsg.value = '' }, 3000)
}

// ─── Filter & search ───────────────────────────────────────────
const filteredUsers = computed(() => {
  if (!userStore.users) return []
  const q = searchQuery.value.toLowerCase()
  return userStore.users.filter(u =>
    (u.name?.toLowerCase().includes(q) || u.email?.toLowerCase().includes(q)) &&
    (roleFilter.value   === '' || u.role   === roleFilter.value) &&
    (statusFilter.value === '' || u.status === statusFilter.value)
  )
})

function applyFilters() {
  userStore.setFilters({ search: searchQuery.value, role: roleFilter.value, status: statusFilter.value })
  loadUsers()
}

let searchTimer = null
function debouncedSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(applyFilters, 400)
}

async function loadUsers() {
  globalError.value = ''
  try {
    await userStore.fetchUsers()
  } catch {
    globalError.value = 'Gagal memuat data user.'
  }
}

// ─── Modal: Create ─────────────────────────────────────────────
function openCreateModal() {
  isEditing.value  = false
  selectedUser.value = null
  form.value       = emptyForm()
  formErrors.value = {}
  formApiError.value = ''
  showFormModal.value = true
}

// ─── Modal: Edit ───────────────────────────────────────────────
function openEditModal(user) {
  isEditing.value  = true
  selectedUser.value = user
  form.value = {
    name:   user.name,
    email:  user.email,
    role:   user.role,
    nip:    user.nip || '',
    status: user.status,
    password: '',
    password_confirmation: '',
  }
  formErrors.value   = {}
  formApiError.value = ''
  showFormModal.value = true
}

function closeFormModal() {
  showFormModal.value = false
}

// ─── Validasi form lokal ───────────────────────────────────────
function validateForm() {
  const errors = {}
  if (!form.value.name.trim())  errors.name  = 'Nama wajib diisi'
  if (!form.value.email.trim()) errors.email = 'Email wajib diisi'
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email))
    errors.email = 'Format email tidak valid'
  if (!form.value.role) errors.role = 'Role wajib dipilih'
  if (!isEditing.value) {
    if (!form.value.password || form.value.password.length < 8)
      errors.password = 'Password minimal 8 karakter'
    if (form.value.password !== form.value.password_confirmation)
      errors.password_confirmation = 'Konfirmasi password tidak cocok'
  }
  formErrors.value = errors
  return Object.keys(errors).length === 0
}

// ─── Submit create / edit ──────────────────────────────────────
async function submitForm() {
  if (!validateForm()) return
  formLoading.value  = true
  formApiError.value = ''

  try {
    const payload = {
      name:   form.value.name.trim(),
      email:  form.value.email.trim(),
      role:   form.value.role,
      nip:    form.value.nip.trim() || null,
      status: form.value.status,
    }
    if (!isEditing.value) {
      payload.password              = form.value.password
      payload.password_confirmation = form.value.password_confirmation
    }

    let result
    if (isEditing.value) {
      result = await userStore.updateUser(selectedUser.value.id, payload)
    } else {
      result = await userStore.createUser(payload)
    }

    if (result.success) {
      showFormModal.value = false
      showToast(isEditing.value ? 'User berhasil diperbarui' : 'User baru berhasil dibuat')
    } else {
      formApiError.value = result.error || 'Terjadi kesalahan'
    }
  } catch (e) {
    formApiError.value = 'Gagal menyimpan data'
  } finally {
    formLoading.value = false
  }
}

// ─── Modal: Reset Password ─────────────────────────────────────
function openResetModal(user) {
  selectedUser.value  = user
  resetForm.value     = { password: '', password_confirmation: '' }
  resetErrors.value   = {}
  resetApiError.value = ''
  showResetModal.value = true
}

async function submitReset() {
  resetErrors.value = {}
  if (!resetForm.value.password || resetForm.value.password.length < 8) {
    resetErrors.value.password = 'Password minimal 8 karakter'
    return
  }
  if (resetForm.value.password !== resetForm.value.password_confirmation) {
    resetErrors.value.password = 'Konfirmasi password tidak cocok'
    return
  }

  resetLoading.value  = true
  resetApiError.value = ''
  try {
    const result = await userStore.resetPassword(selectedUser.value.id, {
      password:              resetForm.value.password,
      password_confirmation: resetForm.value.password_confirmation,
    })
    if (result.success) {
      showResetModal.value = false
      showToast('Password berhasil direset')
    } else {
      resetApiError.value = result.error || 'Gagal reset password'
    }
  } catch {
    resetApiError.value = 'Gagal reset password'
  } finally {
    resetLoading.value = false
  }
}

// ─── Modal: Delete ─────────────────────────────────────────────
function confirmDelete(user) {
  selectedUser.value  = user
  deleteApiError.value = ''
  showDeleteModal.value = true
}

async function executeDelete() {
  deleteLoading.value  = true
  deleteApiError.value = ''
  try {
    const result = await userStore.deleteUser(selectedUser.value.id)
    if (result.success) {
      showDeleteModal.value = false
      showToast('User berhasil dihapus')
    } else {
      deleteApiError.value = result.error || 'Gagal menghapus user'
    }
  } catch {
    deleteApiError.value = 'Gagal menghapus user'
  } finally {
    deleteLoading.value = false
  }
}
</script>

<style scoped>
/* Layout */
.page-container  { padding: 2rem; max-width: 1400px; margin: 0 auto; }
.page-header     { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; }
.page-title      { font-size: 1.75rem; font-weight: 700; color: #1e293b; margin-bottom: 0.25rem; }
.page-subtitle   { color: #64748b; font-size: 0.9rem; }

/* Filters */
.filters-card    { background: white; padding: 1rem 1.25rem; border-radius: 0.75rem; margin-bottom: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.07); }
.filters-grid    { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1rem; }

/* Table card */
.content-card    { background: white; border-radius: 1rem; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.data-table      { width: 100%; border-collapse: collapse; }
.data-table th   { background: #f8fafc; padding: 0.875rem 1rem; text-align: left; font-size: 0.8rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .04em; }
.data-table td   { padding: 0.875rem 1rem; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; vertical-align: middle; }
.data-table tr:last-child td { border-bottom: none; }
.data-table tr:hover td { background: #fafafa; }
.text-right      { text-align: right; }

/* User cell */
.user-cell       { display: flex; align-items: center; gap: 0.75rem; }
.user-avatar     { width: 36px; height: 36px; background: #3b82f6; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem; flex-shrink: 0; }
.user-name       { font-weight: 600; color: #1e293b; }
.user-nip        { font-size: 0.75rem; color: #94a3b8; }

/* Badges */
.badge           { padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; text-transform: capitalize; }
.badge-admin     { background: #fee2e2; color: #991b1b; }
.badge-dokter    { background: #dbeafe; color: #1e40af; }
.badge-terapis   { background: #dcfce7; color: #166534; }
.badge-status-active    { background: #d1fae5; color: #065f46; }
.badge-status-inactive  { background: #fef3c7; color: #92400e; }
.badge-status-suspended { background: #fee2e2; color: #991b1b; }

/* Action buttons */
.action-cell     { white-space: nowrap; }
.btn-icon        { background: none; border: 1px solid #e2e8f0; border-radius: 0.375rem; padding: 0.3rem 0.5rem; cursor: pointer; margin-left: 0.25rem; transition: all .2s; }
.btn-icon:hover  { background: #f1f5f9; }
.btn-icon-danger:hover { background: #fee2e2; border-color: #fca5a5; }

/* Buttons */
.btn-primary     { background: #1e40af; color: white; padding: 0.6rem 1.25rem; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 600; font-size: 0.9rem; transition: all .2s; }
.btn-primary:hover:not(:disabled) { background: #1d4ed8; }
.btn-primary:disabled { opacity: .6; cursor: not-allowed; }
.btn-secondary   { background: #f1f5f9; color: #475569; padding: 0.6rem 1.25rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; cursor: pointer; font-weight: 600; }
.btn-secondary:hover { background: #e2e8f0; }
.btn-danger      { background: #ef4444; color: white; padding: 0.6rem 1.25rem; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 600; }
.btn-danger:hover:not(:disabled) { background: #dc2626; }
.btn-danger:disabled { opacity: .6; cursor: not-allowed; }
.btn-close       { background: none; border: none; font-size: 1.1rem; cursor: pointer; color: #64748b; padding: 0.25rem; }
.btn-close:hover { color: #1e293b; }

/* Form inputs */
.form-group      { margin-bottom: 1.1rem; }
.form-group label { display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.375rem; }
.required        { color: #ef4444; }
.form-input, .form-select {
  width: 100%;
  padding: 0.625rem 0.875rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 0.9rem;
  font-family: inherit;
  transition: border-color .2s;
  background: white;
}
.form-input:focus, .form-select:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.1); }
.input-error     { border-color: #ef4444 !important; }
.error-msg       { display: block; color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; }

/* Modal */
.modal-overlay   { position: fixed; inset: 0; background: rgba(0,0,0,.45); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 1rem; }
.modal           { background: white; border-radius: 1rem; width: 100%; max-width: 480px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 40px rgba(0,0,0,.15); }
.modal-sm        { max-width: 400px; }
.modal-header    { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9; }
.modal-header h2 { font-size: 1.1rem; font-weight: 700; color: #1e293b; margin: 0; }
.modal-body      { padding: 1.5rem; }
.modal-desc      { color: #64748b; font-size: 0.9rem; margin-bottom: 1.25rem; }
.modal-footer    { display: flex; justify-content: flex-end; gap: 0.75rem; padding-top: 1rem; margin-top: 0.5rem; border-top: 1px solid #f1f5f9; }

/* Alerts */
.alert-error     { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 0.75rem 1rem; border-radius: 0.5rem; font-size: 0.875rem; margin-bottom: 1rem; }
.loading-state   { padding: 3rem; text-align: center; color: #94a3b8; }
.empty-state     { padding: 3rem; text-align: center; color: #94a3b8; font-size: 0.9rem; }

/* Toast */
.toast-success   {
  position: fixed; bottom: 1.5rem; right: 1.5rem;
  background: #065f46; color: white;
  padding: 0.75rem 1.25rem; border-radius: 0.5rem;
  font-size: 0.9rem; font-weight: 600;
  box-shadow: 0 4px 12px rgba(0,0,0,.15);
  z-index: 2000; animation: slideIn .3s ease;
}
@keyframes slideIn { from { transform: translateY(1rem); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

@media (max-width: 768px) {
  .filters-grid { grid-template-columns: 1fr; }
  .page-header  { flex-direction: column; gap: 1rem; }
}
</style>
