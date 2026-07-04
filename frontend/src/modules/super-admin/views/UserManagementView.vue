<template>
  <div class="users-management">
    <!-- Header -->
    <div class="page-header">
      <h1>Manajemen User</h1>
      <button @click="showCreateModal = true" class="btn-primary">+ Tambah User</button>
    </div>

    <!-- Filter & Stats -->
    <div class="filter-section">
      <div class="filter-group">
        <label>Role:</label>
        <select v-model="filterRole" @change="onFilterChange" class="filter-input">
          <option value="">Semua Role</option>
          <option value="super_admin">Super Admin</option>
          <option value="admin">Admin Klinik</option>
          <option value="dokter">Dokter</option>
          <option value="terapis">Terapis</option>
          <option value="pasien">Pasien</option>
        </select>
      </div>

      <div class="filter-group">
        <label>Status:</label>
        <select v-model="filterStatus" @change="onFilterChange" class="filter-input">
          <option value="">Semua Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>

      <div class="stats-group">
        <div class="stat">
          <span class="stat-label">Total Users:</span>
          <span class="stat-value">{{ totalUsers }}</span>
        </div>
        <div class="stat">
          <span class="stat-label">Active:</span>
          <span class="stat-value stat-active">{{ activeUsers }}</span>
        </div>
        <div class="stat">
          <span class="stat-label">Inactive:</span>
          <span class="stat-value stat-inactive">{{ inactiveUsers }}</span>
        </div>
      </div>
    </div>

    <div v-if="loading" class="loading">Memuat...</div>
    <div v-if="error" class="error">{{ error }}</div>

    <div v-if="!loading && users.length" class="table-wrapper">
      <table class="users-table">
        <thead>
          <tr>
            <th>Nama</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Last Login</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td>{{ user.name }}</td>
            <td>{{ user.email }}</td>
            <td><span class="badge" :class="`badge-${user.role}`">{{ formatRole(user.role) }}</span></td>
            <td><span :class="`status-${user.status}`">{{ user.status }}</span></td>
            <td>{{ formatDate(user.last_login_at) }}</td>
            <td class="actions">
              <button v-if="user.role !== 'super_admin'" @click="editUser(user)" class="btn-small">Edit</button>
              <button v-if="user.role !== 'super_admin'" @click="resetPwd(user)" class="btn-small">Reset PWD</button>
              <button v-if="user.role !== 'super_admin'" @click="deleteUserConfirm(user)" class="btn-small danger">Hapus</button>
              <span v-else class="text-muted">-</span>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="pagination-wrapper">
        <div class="pagination-info">
          Menampilkan {{ pagination.from }}–{{ pagination.to }} dari {{ pagination.total }} user
        </div>
        <div class="pagination-controls">
          <button
            class="page-btn"
            :disabled="pagination.currentPage === 1"
            @click="goToPage(1)"
            title="Halaman pertama"
          >«</button>
          <button
            class="page-btn"
            :disabled="pagination.currentPage === 1"
            @click="goToPage(pagination.currentPage - 1)"
            title="Sebelumnya"
          >‹</button>

          <button
            v-for="page in visiblePages"
            :key="page"
            class="page-btn"
            :class="{ active: page === pagination.currentPage, ellipsis: page === '...' }"
            :disabled="page === '...'"
            @click="page !== '...' && goToPage(page)"
          >{{ page }}</button>

          <button
            class="page-btn"
            :disabled="pagination.currentPage === pagination.lastPage"
            @click="goToPage(pagination.currentPage + 1)"
            title="Berikutnya"
          >›</button>
          <button
            class="page-btn"
            :disabled="pagination.currentPage === pagination.lastPage"
            @click="goToPage(pagination.lastPage)"
            title="Halaman terakhir"
          >»</button>
        </div>
        <div class="pagination-perpage">
          <label>Per halaman:</label>
          <select v-model="perPage" @change="onPerPageChange" class="filter-input" style="min-width:80px">
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
          </select>
        </div>
      </div>
    </div>

    <div v-if="!loading && !users.length" class="empty">Tidak ada user</div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal" class="modal-overlay" @click="showCreateModal = false">
      <div class="modal" @click.stop>
        <h2>{{ editingUser ? 'Edit User' : 'Tambah User' }}</h2>
        
        <input v-model="form.name" placeholder="Nama" class="input">
        <input v-model="form.email" placeholder="Email" class="input" :disabled="editingUser">
        <select v-model="form.role" class="input">
          <option value="">Pilih Role</option>
          <option value="admin">Admin Klinik</option>
          <option value="dokter">Dokter</option>
          <option value="terapis">Terapis</option>
        </select>

        <div v-if="!editingUser">
          <input v-model="form.password" type="password" placeholder="Password" class="input">
        </div>

        <div class="modal-actions">
          <button @click="saveUser" class="btn-primary">Simpan</button>
          <button @click="showCreateModal = false" class="btn-secondary">Batal</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { superAdminService } from '../services/superAdminService'

const users = ref([])
const loading = ref(false)
const error = ref(null)
const showCreateModal = ref(false)
const editingUser = ref(null)
const filterRole = ref('')
const filterStatus = ref('')
const perPage = ref(25)

const pagination = ref({
  total: 0,
  perPage: 25,
  currentPage: 1,
  lastPage: 1,
  from: 0,
  to: 0,
})

const form = ref({
  name: '',
  email: '',
  role: '',
  password: ''
})

// Computed stats — total dari server
const totalUsers = computed(() => pagination.value.total)
const activeUsers = computed(() => users.value.filter(u => u.status === 'active').length)
const inactiveUsers = computed(() => users.value.filter(u => u.status === 'inactive').length)

// Halaman yang tampil di kontrol pagination (max 7 tombol)
const visiblePages = computed(() => {
  const total = pagination.value.lastPage
  const current = pagination.value.currentPage
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)

  const pages = []
  pages.push(1)
  if (current > 3) pages.push('...')
  for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) {
    pages.push(i)
  }
  if (current < total - 2) pages.push('...')
  pages.push(total)
  return pages
})

const formatRole = (role) => {
  const roleMap = {
    'super_admin': 'Super Admin',
    'admin': 'Admin Klinik',
    'dokter': 'Dokter',
    'terapis': 'Terapis',
    'pasien': 'Pasien'
  }
  return roleMap[role] || role
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  try {
    return new Date(dateStr).toLocaleString('id-ID', {
      day: '2-digit', month: 'short', year: 'numeric',
      hour: '2-digit', minute: '2-digit'
    })
  } catch {
    return dateStr
  }
}

onMounted(() => {
  fetchUsers()
})

const fetchUsers = async (page = pagination.value.currentPage) => {
  try {
    loading.value = true
    error.value = null
    const params = new URLSearchParams()
    if (filterRole.value) params.append('role', filterRole.value)
    if (filterStatus.value) params.append('status', filterStatus.value)
    params.append('page', page)
    params.append('per_page', perPage.value)

    const res = await superAdminService.getUsers(params.toString())
    const resData = res.data

    users.value = resData?.data ?? []
    pagination.value = {
      total:       resData?.total       ?? 0,
      perPage:     resData?.per_page    ?? perPage.value,
      currentPage: resData?.current_page ?? page,
      lastPage:    resData?.last_page   ?? 1,
      from:        resData?.from        ?? 0,
      to:          resData?.to          ?? 0,
    }
  } catch (err) {
    error.value = 'Gagal memuat users'
    console.error(err)
  } finally {
    loading.value = false
  }
}

const goToPage = (page) => {
  if (page < 1 || page > pagination.value.lastPage) return
  pagination.value.currentPage = page
  fetchUsers(page)
}

const onPerPageChange = () => {
  pagination.value.currentPage = 1
  fetchUsers(1)
}

const onFilterChange = () => {
  pagination.value.currentPage = 1
  fetchUsers(1)
}

const editUser = (user) => {
  editingUser.value = user
  form.value = { ...user }
  showCreateModal.value = true
}

const saveUser = async () => {
  try {
    if (editingUser.value) {
      await superAdminService.updateUser(editingUser.value.id, form.value)
    } else {
      await superAdminService.createUser(form.value)
    }
    showCreateModal.value = false
    fetchUsers()
  } catch (err) {
    error.value = 'Gagal menyimpan user: ' + (err.response?.data?.message || err.message)
  }
}

const resetPwd = async (user) => {
  const pwd = prompt('Password baru:')
  if (!pwd) return
  try {
    await superAdminService.resetPassword(user.id, pwd)
    alert('Password reset berhasil')
    fetchUsers()
  } catch {
    alert('Gagal reset password')
  }
}

const deleteUserConfirm = async (user) => {
  if (!confirm(`Hapus user ${user.name}?`)) return
  try {
    await superAdminService.deleteUser(user.id)
    fetchUsers()
  } catch {
    alert('Gagal hapus user')
  }
}
</script>

<style scoped>
.users-management {
  width: 100%;
  background: white;
  border-radius: 0.75rem;
  overflow: hidden;
}

.page-header { 
  display: flex; 
  justify-content: space-between; 
  align-items: center; 
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid #e2e8f0;
}

.page-header h1 { 
  font-size: 1.75rem; 
  font-weight: 700; 
  color: #1e293b; 
}

/* Filter Section */
.filter-section {
  display: flex;
  gap: 2rem;
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: #f8fafc;
  border-radius: 0.75rem;
  flex-wrap: wrap;
  align-items: center;
}

.filter-group {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.filter-group label {
  font-weight: 600;
  color: #475569;
  font-size: 0.95rem;
}

.filter-input {
  padding: 0.625rem 1rem;
  border: 1px solid #cbd5e1;
  border-radius: 0.5rem;
  background: white;
  cursor: pointer;
  font-size: 0.95rem;
  min-width: 150px;
}

.filter-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
}

.stats-group {
  display: flex;
  gap: 2rem;
  margin-left: auto;
  flex-wrap: wrap;
}

.stat {
  display: flex;
  gap: 0.75rem;
  align-items: center;
  padding: 0.75rem 1.5rem;
  background: white;
  border-radius: 0.5rem;
  border: 1px solid #e2e8f0;
}

.stat-label {
  font-weight: 600;
  color: #64748b;
  font-size: 0.9rem;
}

.stat-value {
  font-weight: 700;
  color: #1e293b;
  font-size: 1.25rem;
}

.stat-active {
  color: #059669;
}

.stat-inactive {
  color: #dc2626;
}

/* Loading & Error */
.loading { 
  text-align: center; 
  padding: 3rem; 
  color: #94a3b8;
  background: #f8fafc;
  border-radius: 0.75rem;
}

.error { 
  background: #fee2e2; 
  color: #dc2626; 
  padding: 1rem 1.5rem; 
  border-radius: 0.75rem; 
  margin-bottom: 2rem;
  border: 1px solid #fecaca;
}

/* Table */
.users-table { 
  width: 100%; 
  border-collapse: collapse; 
  background: white; 
}

.users-table th { 
  background: #f8fafc; 
  padding: 1.25rem; 
  text-align: left; 
  font-weight: 600; 
  color: #475569;
  font-size: 0.9rem;
  border-bottom: 2px solid #e2e8f0;
}

.users-table td { 
  padding: 1.25rem; 
  border-bottom: 1px solid #e2e8f0; 
  color: #1e293b;
  font-size: 0.95rem;
}

.users-table tbody tr:hover {
  background: #f8fafc;
}

.badge {
  display: inline-block;
  padding: 0.4rem 0.9rem;
  border-radius: 0.5rem;
  font-size: 0.85rem;
  font-weight: 600;
}

.badge-admin { background: #dbeafe; color: #1e40af; }
.badge-dokter { background: #dcfce7; color: #166534; }
.badge-terapis { background: #fce7f3; color: #831843; }
.badge-pasien { background: #fef3c7; color: #92400e; }
.badge-super_admin { background: #e9d5ff; color: #6b21a8; }

.status-active { 
  color: #059669;
  font-weight: 600;
}

.status-inactive { 
  color: #dc2626;
  font-weight: 600;
}

.actions { 
  display: flex; 
  gap: 0.75rem;
  flex-wrap: wrap;
}

.btn-small { 
  padding: 0.5rem 1rem; 
  font-size: 0.85rem; 
  border: 1px solid #cbd5e1; 
  border-radius: 0.5rem;
  background: white;
  cursor: pointer;
  transition: all 0.2s;
  font-weight: 500;
}

.btn-small:hover {
  border-color: #3b82f6;
  background: #eff6ff;
  color: #1e40af;
}

.btn-small.danger { 
  background: #fee2e2; 
  color: #dc2626;
  border-color: #fecaca;
}

.btn-small.danger:hover {
  background: #fecaca;
  color: #991b1b;
}

.text-muted {
  color: #94a3b8;
  font-size: 0.9rem;
}

/* Modal */
.modal-overlay { 
  position: fixed; 
  top: 0; 
  left: 0; 
  right: 0; 
  bottom: 0; 
  background: rgba(0,0,0,0.6); 
  display: flex; 
  align-items: center; 
  justify-content: center;
  z-index: 1000;
}

.modal { 
  background: white; 
  padding: 2.5rem; 
  border-radius: 0.75rem; 
  min-width: 420px;
  box-shadow: 0 25px 50px rgba(0,0,0,0.25);
  animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal h2 {
  margin-bottom: 1.5rem;
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
}

.input { 
  width: 100%; 
  padding: 0.875rem 1rem; 
  margin-bottom: 1rem; 
  border: 1px solid #cbd5e1; 
  border-radius: 0.5rem;
  font-size: 0.95rem;
  background: white;
}

.input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
}

.input:disabled {
  background: #f8fafc;
  color: #94a3b8;
  cursor: not-allowed;
}

.modal-actions { 
  display: flex; 
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 1.5rem;
}

.btn-primary { 
  background: #1e40af; 
  color: white; 
  padding: 0.875rem 1.75rem; 
  border: none; 
  border-radius: 0.5rem; 
  cursor: pointer;
  font-weight: 600;
  transition: background 0.2s;
}

.btn-primary:hover {
  background: #1e3a8a;
}

.btn-secondary { 
  background: #e2e8f0; 
  color: #1e293b;
  padding: 0.875rem 1.75rem; 
  border: none; 
  border-radius: 0.5rem; 
  cursor: pointer;
  font-weight: 600;
  transition: background 0.2s;
}

.btn-secondary:hover {
  background: #cbd5e1;
}

.empty { 
  text-align: center; 
  padding: 3rem; 
  color: #94a3b8;
  background: #f8fafc;
  border-radius: 0.75rem;
}

@media (max-width: 1024px) {
  .stats-group {
    margin-left: 0;
    width: 100%;
  }
  
  .filter-section {
    flex-direction: column;
    align-items: stretch;
  }
}

/* Table wrapper */
.table-wrapper {
  overflow-x: auto;
}

/* Pagination */
.pagination-wrapper {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1rem;
  border-top: 1px solid #e2e8f0;
  flex-wrap: wrap;
  gap: 1rem;
}

.pagination-info {
  color: #64748b;
  font-size: 0.9rem;
}

.pagination-controls {
  display: flex;
  gap: 0.375rem;
  align-items: center;
}

.page-btn {
  min-width: 2.25rem;
  height: 2.25rem;
  padding: 0 0.5rem;
  border: 1px solid #cbd5e1;
  border-radius: 0.5rem;
  background: white;
  color: #374151;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.page-btn:hover:not(:disabled):not(.ellipsis) {
  border-color: #3b82f6;
  background: #eff6ff;
  color: #1e40af;
}

.page-btn.active {
  background: #1e40af;
  border-color: #1e40af;
  color: white;
}

.page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.page-btn.ellipsis {
  border-color: transparent;
  background: transparent;
  cursor: default;
}

.pagination-perpage {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  color: #64748b;
}

@media (max-width: 768px) {
  .pagination-wrapper {
    flex-direction: column;
    align-items: stretch;
    text-align: center;
  }

  .pagination-controls {
    justify-content: center;
  }

  .pagination-perpage {
    justify-content: center;
  }
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }

  .filter-section {
    flex-direction: column;
  }

  .stats-group {
    flex-direction: column;
    width: 100%;
  }

  .users-table {
    font-size: 0.85rem;
  }

  .users-table th,
  .users-table td {
    padding: 0.75rem;
  }

  .actions {
    flex-direction: column;
  }

  .modal {
    min-width: auto;
    width: 90%;
  }
}
</style>
