import { defineStore } from 'pinia'
import { ref } from 'vue'
import { userService } from '../services/userService'

export const useUserStore = defineStore('users', () => {
  const users = ref([])
  const currentUser = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0
  })

  // Filters
  const filters = ref({
    search: '',
    role: '',
    status: ''
  })

  // Fetch users with filters
  async function fetchUsers(page = 1) {
    loading.value = true
    error.value = null

    try {
      const params = {
        ...filters.value,
        page
      }

      const response = await userService.getUsers(params)
      users.value = response.data.data
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        per_page: response.data.per_page,
        total: response.data.total
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal mengambil data user'
    } finally {
      loading.value = false
    }
  }

  // Create new user
  async function createUser(userData) {
    loading.value = true
    error.value = null

    try {
      const response = await userService.createUser(userData)
      await fetchUsers(pagination.value.current_page)
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal menambah user'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  // Update user
  async function updateUser(id, userData) {
    loading.value = true
    error.value = null

    try {
      const response = await userService.updateUser(id, userData)
      await fetchUsers(pagination.value.current_page)
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal update user'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  // Update user status
  async function updateUserStatus(id, status) {
    loading.value = true
    error.value = null

    try {
      const response = await userService.updateUserStatus(id, status)
      await fetchUsers(pagination.value.current_page)
      return { success: true, message: response.message }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal update status'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  // Reset user password
  async function resetPassword(id, passwordData) {
    loading.value = true
    error.value = null

    try {
      const response = await userService.resetPassword(id, passwordData)
      return { success: true, message: response.message }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal reset password'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  // Delete user
  async function deleteUser(id) {
    loading.value = true
    error.value = null

    try {
      await userService.deleteUser(id)
      await fetchUsers(pagination.value.current_page)
      return { success: true, message: 'User berhasil dihapus' }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal hapus user'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  // Set filters
  function setFilters(newFilters) {
    filters.value = { ...filters.value, ...newFilters }
  }

  // Reset filters
  function resetFilters() {
    filters.value = {
      search: '',
      role: '',
      status: ''
    }
  }

  return {
    users,
    currentUser,
    loading,
    error,
    pagination,
    filters,
    fetchUsers,
    createUser,
    updateUser,
    updateUserStatus,
    resetPassword,
    deleteUser,
    setFilters,
    resetFilters
  }
})