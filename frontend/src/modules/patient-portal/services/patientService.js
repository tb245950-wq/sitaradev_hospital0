// services/patientService.js
// PERBAIKAN: Hapus prefix /api/ karena baseURL axios sudah mengandung /api
import apiClient from '../../../core/services/api'

export const patientService = {
  // Login
  async login(email, password) {
    try {
      const response = await apiClient.post('/pasien/login', { email, password })
      const data = response.data
      if (data.data?.token) {
        localStorage.setItem('patient_token', data.data.token)
        localStorage.setItem('patient_user', JSON.stringify(data.data.user))
        if (data.data.patient) {
          localStorage.setItem('patient', JSON.stringify(data.data.patient))
        }
      }
      return { success: true, data: data.data || data }
    } catch (error) {
      return { success: false, error: error.response?.data?.message || 'Login gagal' }
    }
  },

  // Register
  async register(patientData) {
    try {
      const response = await apiClient.post('/pasien/register', patientData)
      return { success: true, data: response.data }
    } catch (error) {
      return { success: false, error: error.response?.data?.message || 'Registrasi gagal' }
    }
  },

  // Logout
  async logout() {
    try {
      await apiClient.post('/pasien/logout')
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      localStorage.removeItem('patient_token')
      localStorage.removeItem('patient_user')
      localStorage.removeItem('patient')
    }
  },

  // Dashboard Stats
  async getDashboardStats() {
    try {
      const response = await apiClient.get('/pasien/dashboard/stats')
      return { success: true, data: response.data }
    } catch (error) {
      return { success: false, error: error.response?.data?.message || 'Gagal mengambil data dashboard' }
    }
  },

  // Antrian Saya
  async getMyQueue() {
    try {
      const response = await apiClient.get('/pasien/antrian-saya')
      return { success: true, data: response.data?.data || response.data }
    } catch (error) {
      return { success: false, error: error.response?.data?.message || 'Gagal mengambil antrian' }
    }
  },

  // Jadwal Terapi
  async getTherapySchedule() {
    try {
      const response = await apiClient.get('/pasien/jadwal-terapi')
      return { success: true, data: response.data?.data || response.data }
    } catch (error) {
      return { success: false, error: error.response?.data?.message || 'Gagal mengambil jadwal' }
    }
  },

  // Riwayat Medis
  async getMedicalHistory() {
    try {
      const response = await apiClient.get('/pasien/riwayat-medis')
      return { success: true, data: response.data?.data || response.data }
    } catch (error) {
      return { success: false, error: error.response?.data?.message || 'Gagal mengambil riwayat' }
    }
  },

  // Update Profil
  async updateProfile(profileData) {
    try {
      const response = await apiClient.put('/pasien/profile', profileData)
      const updated = response.data?.data || response.data
      if (updated?.user) {
        localStorage.setItem('patient_user', JSON.stringify(updated.user))
      }
      return { success: true, data: updated }
    } catch (error) {
      return { success: false, error: error.response?.data?.message || 'Gagal memperbarui profil' }
    }
  },

  // Get Dashboard (legacy)
  async getDashboard() {
    return this.getDashboardStats()
  },

  getStoredUser() {
    try {
      const user = localStorage.getItem('patient_user')
      if (!user || user === 'undefined' || user === 'null') return null
      if (user.startsWith('<') || user.startsWith('{error')) {
        localStorage.removeItem('patient_user')
        return null
      }
      return JSON.parse(user)
    } catch (error) {
      console.error('Error parsing patient_user:', error)
      localStorage.removeItem('patient_user')
      return null
    }
  },

  getToken() {
    try {
      const token = localStorage.getItem('patient_token')
      if (!token || token === 'undefined' || token === 'null') return null
      if (token.startsWith('<') || token.startsWith('{')) {
        localStorage.removeItem('patient_token')
        return null
      }
      return token
    } catch (error) {
      console.error('Error getting token:', error)
      localStorage.removeItem('patient_token')
      return null
    }
  },

  // Daftar dokter & terapis aktif
  async getDoctors() {
    const response = await apiClient.get('/pasien/doctors')
    return response.data
  },

  // Daftar poli aktif
  async getPolis() {
    const response = await apiClient.get('/pasien/polis')
    return response.data
  },

  // Booking antrian
  async bookQueue(data) {
    const response = await apiClient.post('/pasien/booking', data)
    return response.data
  },
}
