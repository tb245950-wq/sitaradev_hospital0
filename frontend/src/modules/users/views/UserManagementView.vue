<script setup>
import { ref, computed, onMounted, onErrorCaptured } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../auth/stores/authStore'
import { useUserStore } from '../stores/userStore' // Assuming userStore exists
import { useNavigation } from '../../../shared/composables/useNavigation'

const router = useRouter()
const authStore = useAuthStore()
const userStore = useUserStore()
const { goToDashboard } = useNavigation()

const searchQuery = ref('')
const roleFilter = ref('')
const statusFilter = ref('')
const error = ref(null)

onErrorCaptured((err) => {
  error.value = 'Terjadi kesalahan pada komponen Manajemen User.'
  console.error('UserManagementView Error:', err)
  return false
})

onMounted(() => {
  if (!authStore.user || authStore.user.role !== 'admin') {
    router.push('/unauthorized')
    return
  }
  refreshData()
})

const refreshData = async () => {
  try {
    await userStore.fetchUsers()
  } catch (err) {
    error.value = 'Gagal memuat data user.'
  }
}

const filteredUsers = computed(() => {
  if (!userStore.users) return []
  const q = searchQuery.value?.toLowerCase() || ''
  return userStore.users.filter(u => 
    (u.name?.toLowerCase().includes(q) || u.email?.toLowerCase().includes(q)) &&
    (roleFilter.value === '' || u.role === roleFilter.value) &&
    (statusFilter.value === '' || u.status === statusFilter.value)
  )
})

const applyFilters = () => {
  userStore.setFilters({ search: searchQuery.value, role: roleFilter.value, status: statusFilter.value })
  userStore.fetchUsers()
}

const debouncedSearch = () => {
  clearTimeout(window.searchTimeout)
  window.searchTimeout = setTimeout(applyFilters, 500)
}

const openCreateModal = () => alert('Fitur tambah user akan segera hadir')
const editUser = (user) => alert(`Edit User: ${user.name}`)
const resetPassword = (user) => alert(`Reset Password: ${user.name}`)
const updateStatus = async (user, status) => {
  if (confirm(`Ubah status ${user.name} menjadi ${status}?`)) {
    try {
      await userStore.updateUserStatus(user.id, status)
      alert('Status berhasil diupdate')
      refreshData()
    } catch (e) {
      alert('Gagal update status')
    }
  }
}

const R = (name) => name?.substring(0, 2).toUpperCase() || '??'
const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>

<template>
  <div v-if="error" class="error-page">{{ error }}</div>
  <div v-else class="page-container">
    <div class="page-header">
      <div>
        <button @click="goToDashboard" class="btn-back">
          <span class="arrow">←</span>
          <span>Kembali ke Dashboard</span>
        </button>
        <h1 class="page-title">Manajemen User</h1>
        <p class="page-subtitle">Kelola akun Admin, Dokter, dan Terapis</p>
      </div>
      <button @click="openCreateModal" class="btn-primary">+ Tambah User</button>
    </div>

    <!-- Filters -->
    <div class="filters-card">
      <div class="filters-grid">
        <input type="text" v-model="searchQuery" placeholder="Cari nama atau email..." @input="debouncedSearch" class="form-input" />
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
        </select>
      </div>
    </div>

    <!-- Table -->
    <div class="content-card">
      <table class="data-table">
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
          <tr v-if="userStore.loading">
            <td colspan="6" class="text-center py-8">Memuat data...</td>
          </tr>
          <tr v-for="user in filteredUsers" :key="user.id">
            <td>{{ user.name }}</td>
            <td>{{ user.email }}</td>
            <td><span class="badge" :class="`badge-${user.role}`">{{ user.role }}</span></td>
            <td><span class="badge" :class="`badge-${user.status}`">{{ user.status }}</span></td>
            <td>{{ formatDate(user.last_login_at) }}</td>
            <td class="text-right">
              <button @click="editUser(user)" class="btn-icon">✏️</button>
              <button @click="resetPassword(user)" class="btn-icon">🔑</button>
              <select :value="user.status" @change="updateStatus(user, $event.target.value)" class="form-select-sm">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.page-container { padding: 2rem; max-width: 1400px; margin: 0 auto; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
.page-title { font-size: 1.75rem; font-weight: 700; }
.filters-card { background: white; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; }
.filters-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
.content-card { background: white; border-radius: 1rem; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th, .data-table td { padding: 1rem; border-bottom: 1px solid #f1f5f9; }
.btn-primary { background: #2563eb; color: white; padding: 0.625rem 1.25rem; border-radius: 0.5rem; border: none; cursor: pointer; }
.badge { padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; text-transform: capitalize; }
.badge-admin { background: #fee2e2; color: #991b1b; }
.badge-dokter { background: #dbeafe; color: #1e40af; }
.badge-terapis { background: #dcfce7; color: #166534; }
.btn-icon { background: none; border: none; cursor: pointer; padding: 0.25rem; }
.form-input, .form-select, .form-select-sm { padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.375rem; }
</style>
