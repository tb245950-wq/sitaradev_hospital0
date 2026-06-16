import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { patientService } from '../services/patientService'

export const usePatientStore = defineStore('patient', () => {
  const user = ref(patientService.getStoredUser())
  const token = ref(patientService.getToken())
  const loading = ref(false)
  const error = ref(null)

  const isAuthenticated = computed(() => !!token.value)

  async function login(email, password) {
    loading.value = true
    error.value = null
    try {
      const data = await patientService.login(email, password)
      user.value = data.data.user
      token.value = data.data.token
      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.message || 'Login gagal'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function register(patientData) {
    loading.value = true
    error.value = null
    try {
      const data = await patientService.register(patientData)
      user.value = data.data.user
      token.value = data.data.token
      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.message || 'Registrasi gagal'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await patientService.logout()
    } finally {
      user.value = null
      token.value = null
    }
  }

  async function fetchProfile() {
    try {
      const response = await patientService.getProfile()
      if (response.success) {
        user.value = response.data
        localStorage.setItem('patient_user', JSON.stringify(response.data))
      }
    } catch (error) {
      console.error('Error fetching profile:', error)
    }
  }

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    login,
    register,
    logout,
    fetchProfile
  }
})
