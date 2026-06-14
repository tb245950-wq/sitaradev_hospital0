<template>
  <div class="user-management">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Manajemen User</h1>
        <p class="page-subtitle">Kelola akun Admin, Dokter, dan Terapis</p>
      </div>
      <button @click="openCreateModal" class="btn-primary">
        <span class="btn-icon">+</span>
        Tambah User
      </button>
    </div>

    <!-- Filters -->
    <div class="filters-card">
      <div class="filters-grid">
        <div class="filter-group">
          <label>Pencarian</label>
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Cari nama, email, atau NIP..."
            @input="debouncedSearch"
            class="form-input"
          />
        </div>

        <div class="filter-group">
          <label>Filter Role</label>
          <select v-model="roleFilter" @change="applyFilters" class="form-select">
            <option value="">Semua Role</option>
            <option value="admin">Admin</option>
            <option value="dokter">Dokter</option>
            <option value="terapis">Terapis</option>
          </select>
        </div>

        <div class="filter-group">
          <label>Filter Status</label>
          <select v-model="statusFilter" @change="applyFilters" class="form-select">
            <option value="">Semua Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="suspended">Suspended</option>
          </select>
        </div>

        <div class="filter-group">
          <button @click="resetFilters" class="btn-secondary">
            Reset Filter
          </button>
        </div>
      </div>
    </div>

    <!-- Users Table -->
    <div class="table-card">
      <div class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>Nama</th>
              <th>Email</th>
              <th>NIP</th>
              <th>Role</th>
              <th>Status</th>
              <th>Last Login</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="userStore.loading">
              <td colspan="7" class="text-center">
                <div class="loading-spinner">Loading...</div>
              </td>
            </tr>
            <tr v-else-if="userStore.users.length === 0">
              <td colspan="7" class="text-center">
                <div class="empty-state">
                  <p>Tidak ada data user</p>
                </div>
              </td>
            </tr>
            <tr v-for="user in userStore.users" :key="user.id">
              <td>
                <div class="user-info">
                  <div class="user-avatar">{{ getInitials(user.name) }}</div>
                  <div>
                    <div class="user-name">{{ user.name }}</div>
                    <div class="user-id">ID: {{ user.id }}</div>
                  </div>
                </div>
              </td>
              <td>{{ user.email }}</td>
              <td>{{ user.nip || '-' }}</td>
              <td>
                <span :class="['role-badge', `role-${user.role}`]">
                  {{ user.role }}
                </span>
              </td>
              <td>
                <span :class="['status-badge', `status-${user.status}`]">
                  {{ user.status }}
                </span>
              </td>
              <td>{{ formatDate(user.last_login_at) }}</td>
              <td>
                <div class="action-buttons">
                  <button @click="openEditModal(user)" class="btn-icon-sm" title="Edit">
                    ✏️
                  </button>
                  <button @click="openResetPasswordModal(user)" class="btn-icon-sm" title="Reset Password">
                    🔑
                  </button>
                  <select 
                    v-if="user.id !== authStore.user?.id"
                    @change="handleStatusChange(user, $event.target.value)"
                    :class="['status-select', `status-${user.status}`]"
                  >
                    <option value="active" :selected="user.status === 'active'">Active</option>
                    <option value="inactive" :selected="user.status === 'inactive'">Inactive</option>
                    <option value="suspended" :selected="user.status === 'suspended'">Suspended</option>
                  </select>
                  <span v-else class="current-user-badge">You</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="userStore.pagination.last_page > 1" class="pagination">
        <button 
          @click="changePage(userStore.pagination.current_page - 1)"
          :disabled="userStore.pagination.current_page === 1"
          class="btn-pagination"
        >
          ← Prev
        </button>
        
        <span class="pagination-info">
          Halaman {{ userStore.pagination.current_page }} dari {{ userStore.pagination.last_page }}
          ({{ userStore.pagination.total }} data)
        </span>

        <button 
          @click="changePage(userStore.pagination.current_page + 1)"
          :disabled="userStore.pagination.current_page === userStore.pagination.last_page"
          class="btn-pagination"
        >
          Next →
        </button>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>{{ isEditing ? 'Edit User' : 'Tambah User Baru' }}</h2>
          <button @click="closeModal" class="btn-close">×</button>
        </div>

        <form @submit.prevent="handleSubmit" class="modal-form">
          <div class="form-row">
            <div class="form-group">
              <label>Nama Lengkap <span class="required">*</span></label>
              <input
                v-model="formData.name"
                type="text"
                required
                class="form-input"
                placeholder="Nama lengkap"
              />
            </div>

            <div class="form-group">
              <label>Email <span class="required">*</span></label>
              <input
                v-model="formData.email"
                type="email"
                required
                class="form-input"
                placeholder="email@example.com"
              />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>NIP</label>
              <input
                v-model="formData.nip"
                type="text"
                class="form-input"
                placeholder="Nomor Induk Pegawai (opsional)"
              />
            </div>

            <div class="form-group">
              <label>Role <span class="required">*</span></label>
              <select v-model="formData.role" required class="form-select">
                <option value="">Pilih Role</option>
                <option value="admin">Admin</option>
                <option value="dokter">Dokter</option>
                <option value="terapis">Terapis</option>
              </select>
            </div>
          </div>

          <div class="form-row" v-if="!isEditing">
            <div class="form-group">
              <label>Password <span class="required">*</span></label>
              <input
                v-model="formData.password"
                type="password"
                required
                :minlength="6"
                class="form-input"
                placeholder="Minimal 6 karakter"
              />
            </div>

            <div class="form-group">
              <label>Konfirmasi Password <span class="required">*</span></label>
              <input
                v-model="formData.password_confirmation"
                type="password"
                required
                :minlength="6"
                class="form-input"
                placeholder="Ulangi password"
              />
            </div>
          </div>

          <div class="form-group">
            <label>Status</label>
            <select v-model="formData.status" class="form-select">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="suspended">Suspended</option>
            </select>
          </div>

          <div v-if="userStore.error" class="error-message">
            {{ userStore.error }}
          </div>

          <div class="modal-actions">
            <button type="button" @click="closeModal" class="btn-secondary">
              Batal
            </button>
            <button type="submit" :disabled="userStore.loading" class="btn-primary">
              {{ userStore.loading ? 'Menyimpan...' : (isEditing ? 'Update' : 'Simpan') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Reset Password Modal -->
    <div v-if="showResetPasswordModal" class="modal-overlay" @click.self="closeResetPasswordModal">
      <div class="modal-content modal-small">
        <div class="modal-header">
          <h2>Reset Password</h2>
          <button @click="closeResetPasswordModal" class="btn-close">×</button>
        </div>

        <form @submit.prevent="handleResetPassword" class="modal-form">
          <div class="form-group">
            <label>User</label>
            <input
              :value="selectedUser?.name"
              type="text"
              disabled
              class="form-input"
            />
          </div>

          <div class="form-group">
            <label>Password Baru <span class="required">*</span></label>
            <input
              v-model="passwordData.password"
              type="password"
              required
              :minlength="6"
              class="form-input"
              placeholder="Minimal 6 karakter"
            />
          </div>

          <div class="form-group">
            <label>Konfirmasi Password <span class="required">*</span></label>
            <input
              v-model="passwordData.password_confirmation"
              type="password"
              required
              :minlength="6"
              class="form-input"
              placeholder="Ulangi password"
            />
          </div>

          <div v-if="userStore.error" class="error-message">
            {{ userStore.error }}
          </div>

          <div class="modal-actions">
            <button type="button" @click="closeResetPasswordModal" class="btn-secondary">
              Batal
            </button>
            <button type="submit" :disabled="userStore.loading" class="btn-primary">
              {{ userStore.loading ? 'Menyimpan...' : 'Reset Password' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useUserStore } from '../stores/userStore'
import { useAuthStore } from '../../auth/stores/authStore'

const userStore = useUserStore()
const authStore = useAuthStore()

// Modal states
const showModal = ref(false)
const showResetPasswordModal = ref(false)
const isEditing = ref(false)
const selectedUser = ref(null)

// Form data
const formData = ref({
  name: '',
  email: '',
  nip: '',
  role: '',
  password: '',
  password_confirmation: '',
  status: 'active'
})

// Password reset data
const passwordData = ref({
  password: '',
  password_confirmation: ''
})

// Filters
const searchQuery = ref('')
const roleFilter = ref('')
const statusFilter = ref('')
let searchTimeout = null

// Computed
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}

const applyFilters = () => {
  userStore.setFilters({
    search: searchQuery.value,
    role: roleFilter.value,
    status: statusFilter.value
  })
  userStore.fetchUsers(1)
}

const resetFilters = () => {
  searchQuery.value = ''
  roleFilter.value = ''
  statusFilter.value = ''
  userStore.resetFilters()
  userStore.fetchUsers(1)
}

// Methods
const openCreateModal = () => {
  isEditing.value = false
  selectedUser.value = null
  formData.value = {
    name: '',
    email: '',
    nip: '',
    role: '',
    password: '',
    password_confirmation: '',
    status: 'active'
  }
  showModal.value = true
}

const openEditModal = (user) => {
  isEditing.value = true
  selectedUser.value = user
  formData.value = {
    name: user.name,
    email: user.email,
    nip: user.nip || '',
    role: user.role,
    password: '',
    password_confirmation: '',
    status: user.status
  }
  showModal.value = true
}

const openResetPasswordModal = (user) => {
  selectedUser.value = user
  passwordData.value = {
    password: '',
    password_confirmation: ''
  }
  showResetPasswordModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedUser.value = null
}

const closeResetPasswordModal = () => {
  showResetPasswordModal.value = false
  selectedUser.value = null
}

const handleSubmit = async () => {
  if (isEditing.value && selectedUser.value) {
    const payload = { ...formData.value }
    if (!payload.password) {
      delete payload.password
      delete payload.password_confirmation
    }
    
    const result = await userStore.updateUser(selectedUser.value.id, payload)
    if (result.success) {
      closeModal()
      alert('User berhasil diupdate')
    }
  } else {
    const result = await userStore.createUser(formData.value)
    if (result.success) {
      closeModal()
      alert('User berhasil ditambahkan')
    }
  }
}

const handleResetPassword = async () => {
  if (selectedUser.value) {
    const result = await userStore.resetPassword(selectedUser.value.id, passwordData.value)
    if (result.success) {
      closeResetPasswordModal()
      alert('Password berhasil direset')
    }
  }
}

const handleStatusChange = async (user, newStatus) => {
  if (user.id === authStore.user?.id) {
    alert('Anda tidak dapat mengubah status akun sendiri')
    return
  }

  const confirmed = confirm(`Ubah status ${user.name} menjadi ${newStatus}?`)
  if (confirmed) {
    const result = await userStore.updateUserStatus(user.id, newStatus)
    if (result.success) {
      alert(result.message)
    } else {
      alert(result.error)
    }
  }
}

const changePage = (page) => {
  if (page >= 1 && page <= userStore.pagination.last_page) {
    userStore.fetchUsers(page)
  }
}

const getInitials = (name) => {
  if (!name) return '?'
  const names = name.split(' ')
  if (names.length >= 2) {
    return `${names[0][0]}${names[names.length - 1][0]}`.toUpperCase()
  }
  return name.substring(0, 2).toUpperCase()
}

const formatDate = (date) => {
  if (!date) return 'Never'
  return new Date(date).toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Fetch users on mount
onMounted(() => {
  userStore.fetchUsers()
})
</script>

<style scoped>
/* Styles akan saya berikan di message berikutnya karena terlalu panjang */
</style>