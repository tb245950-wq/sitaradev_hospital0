import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { patientService } from '../services/patientService'

export const usePatientStore = defineStore('patient', () => {
  // ============================================
  // STATE
  // ============================================
  const user = ref(null)
  const patient = ref(null)
  const token = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const dashboardData = ref(null)

  // ============================================
  // COMPUTED
  // ============================================
  const isAuthenticated = computed(() => {
    return !!token.value || !!localStorage.getItem('patient_token')
  })

  const userName = computed(() => user.value?.name || '')
  const patientData = computed(() => patient.value)

  // ============================================
  // ACTIONS
  // ============================================

  /**
   * Login pasien
   */
  async function login(email, password) {
    console.log('🔐 Patient store: Attempting login...')
    console.log('   Email:', email)
    
    loading.value = true
    error.value = null
    
    try {
      // Panggil service untuk API call
      const result = await patientService.login(email, password)
      
      console.log('📥 Patient store: Login result:', result)
      
      if (result.success) {
        // Update state
        user.value = result.data.user
        patient.value = result.data.patient
        token.value = result.data.token
        
        console.log('✅ Patient store: Login successful!')
        console.log('   User:', user.value)
        console.log('   Token saved:', !!token.value)
        
        return {
          success: true,
          data: result.data
        }
      } else {
        console.error('❌ Patient store: Login failed:', result.error)
        error.value = result.error || 'Login gagal'
        
        return {
          success: false,
          error: result.error
        }
      }
    } catch (err) {
      console.error('💥 Patient store: Unexpected error:', err)
      error.value = 'Terjadi kesalahan sistem'
      
      return {
        success: false,
        error: err.message || 'Terjadi kesalahan'
      }
    } finally {
      loading.value = false
    }
  }

  /**
   * Register pasien baru
   */
  async function register(patientData) {
    console.log('📝 Patient store: Registering...')
    
    loading.value = true
    error.value = null
    
    try {
      const result = await patientService.register(patientData)
      
      if (result.success) {
        user.value = result.data.user
        patient.value = result.data.patient
        token.value = result.data.token
        
        return {
          success: true,
          data: result.data
        }
      } else {
        error.value = result.error || 'Registrasi gagal'
        return {
          success: false,
          error: result.error
        }
      }
    } catch (err) {
      error.value = 'Terjadi kesalahan saat registrasi'
      return {
        success: false,
        error: err.message
      }
    } finally {
      loading.value = false
    }
  }

  /**
   * Logout pasien
   */
  async function logout() {
    console.log('🚪 Patient store: Logging out...')
    
    try {
      await patientService.logout()
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      // Clear state
      user.value = null
      patient.value = null
      token.value = null
      dashboardData.value = null
      error.value = null
      
      // Clear localStorage
      localStorage.removeItem('patient_token')
      localStorage.removeItem('user')
      localStorage.removeItem('patient')
      
      console.log('✅ Patient store: Logged out')
    }
  }

  /**
   * Get dashboard data
   */
  async function fetchDashboard() {
    console.log('📊 Patient store: Fetching dashboard...')
    
    loading.value = true
    error.value = null
    
    try {
      const result = await patientService.getDashboard()
      
      if (result.success) {
        dashboardData.value = result.data
        return {
          success: true,
          data: result.data
        }
      } else {
        error.value = result.error
        return {
          success: false,
          error: result.error
        }
      }
    } catch (err) {
      error.value = 'Gagal memuat dashboard'
      return {
        success: false,
        error: err.message
      }
    } finally {
      loading.value = false
    }
  }

  /**
   * Load user from localStorage (saat app refresh)
   */
  function loadFromStorage() {
    try {
      const storedToken = localStorage.getItem('patient_token')
      const storedUser = localStorage.getItem('user')
      const storedPatient = localStorage.getItem('patient')
      
      if (storedToken) {
        token.value = storedToken
        user.value = storedUser ? JSON.parse(storedUser) : null
        patient.value = storedPatient ? JSON.parse(storedPatient) : null
        console.log('✅ Patient store: Loaded from localStorage')
      }
    } catch (err) {
      console.error('Error loading from storage:', err)
    }
  }

  /**
   * Clear error
   */
  function clearError() {
    error.value = null
  }

  // ============================================
  // INITIALIZE
  // ============================================
  loadFromStorage()

  // ============================================
  // RETURN
  // ============================================
  return {
    // State
    user,
    patient,
    token,
    loading,
    error,
    dashboardData,
    
    // Computed
    isAuthenticated,
    userName,
    patientData,
    
    // Actions
    login,
    register,
    logout,
    fetchDashboard,
    loadFromStorage,
    clearError
  }
})