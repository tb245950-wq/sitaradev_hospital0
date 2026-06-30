<template>
  <div class="dashboard-layout">
    <Sidebar :is-open="isSidebarOpen" />
    
    <div class="main-content">
      <Navbar @toggle-sidebar="isSidebarOpen = !isSidebarOpen" />
      
      <main class="content-body">
        <div class="users-management">
          <div class="page-header">
            <h1>Manajemen User</h1>
            <button @click="showCreateModal = true" class="btn-primary">+ Tambah User</button>
          </div>

          <div v-if="loading" class="loading">Memuat...</div>
          <div v-if="error" class="error">{{ error }}</div>

          <table v-if="!loading && users.length" class="users-table">
      <thead>
        <tr>
          <th>Nama</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users" :key="user.id">
          <td>{{ user.name }}</td>
          <td>{{ user.email }}</td>
          <td><span class="badge">{{ user.role }}</span></td>
          <td><span :class="`status-${user.status}`">{{ user.status }}</span></td>
          <td class="actions">
            <button @click="editUser(user)" class="btn-small">Edit</button>
            <button @click="resetPwd(user)" class="btn-small">Reset PWD</button>
            <button @click="deleteUserConfirm(user)" class="btn-small danger">Hapus</button>
          </td>
        </tr>
      </tbody>
    </table>

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
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { superAdminService } from '../services/superAdminService'
import Sidebar from '../../../shared/components/layout/Sidebar.vue'
import Navbar from '../../../shared/components/layout/Navbar.vue'

const isSidebarOpen = ref(false)

const users = ref([])
const loading = ref(false)
const error = ref(null)
const showCreateModal = ref(false)
const editingUser = ref(null)
const form = ref({
  name: '',
  email: '',
  role: '',
  password: ''
})

onMounted(() => {
  fetchUsers()
})

const fetchUsers = async () => {
  try {
    loading.value = true
    const res = await superAdminService.getUsers()
    users.value = res.data || []
  } catch (err) {
    error.value = 'Gagal memuat users'
  } finally {
    loading.value = false
  }
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
    error.value = 'Gagal menyimpan user'
  }
}

const resetPwd = async (user) => {
  const pwd = prompt('Password baru:')
  if (!pwd) return
  try {
    await superAdminService.resetPassword(user.id, pwd)
    alert('Password reset berhasil')
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
.dashboard-layout { display: flex; min-height: 100vh; background: #f8fafc; }
.main-content { flex: 1; margin-left: 260px; display: flex; flex-direction: column; }
.content-body { padding: 1.5rem; }
@media (max-width: 768px) { .main-content { margin-left: 0; } }

.users-management { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.loading { text-align: center; padding: 2rem; }
.error { background: #fee2e2; color: #dc2626; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; }
.users-table { width: 100%; border-collapse: collapse; background: white; border-radius: 0.5rem; overflow: hidden; }
.users-table th { background: #f8fafc; padding: 1rem; text-align: left; font-weight: 600; }
.users-table td { padding: 1rem; border-bottom: 1px solid #e2e8f0; }
.badge { background: #e0e7ff; color: #3730a3; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.85rem; }
.status-active { color: #059669; }
.status-inactive { color: #dc2626; }
.actions { display: flex; gap: 0.5rem; }
.btn-small { padding: 0.5rem 1rem; font-size: 0.85rem; border: 1px solid #e2e8f0; border-radius: 0.25rem; }
.btn-small.danger { background: #fee2e2; color: #dc2626; }
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; }
.modal { background: white; padding: 2rem; border-radius: 0.5rem; min-width: 400px; }
.input { width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 1px solid #e2e8f0; border-radius: 0.25rem; }
.modal-actions { display: flex; gap: 0.5rem; }
.btn-primary { background: #1e40af; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 0.25rem; cursor: pointer; }
.btn-secondary { background: #e2e8f0; padding: 0.75rem 1.5rem; border: none; border-radius: 0.25rem; cursor: pointer; }
.empty { text-align: center; padding: 2rem; color: #94a3b8; }
</style>
