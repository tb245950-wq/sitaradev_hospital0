// services/patientService.js
import apiClient from '../../../core/services/api'

export const patientService = {
  // ── Auth ──────────────────────────────────────────────────────────────

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

  async register(patientData) {
    try {
      const response = await apiClient.post('/pasien/register', patientData)
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
      const responseData = error.response?.data
      let errorMsg = responseData?.message || 'Registrasi gagal'
      if (responseData?.errors) {
        const fieldErrors = Object.values(responseData.errors).flat()
        errorMsg = fieldErrors.join(', ')
      }
      return { success: false, error: errorMsg }
    }
  },

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

  // ── Profile ───────────────────────────────────────────────────────────

  /**
   * Ambil data profil lengkap dari server
   */
  async getProfile() {
    try {
      const response = await apiClient.get('/pasien/profile')
      return { success: true, data: response.data?.data || response.data }
    } catch (error) {
      return { success: false, error: error.response?.data?.message || 'Gagal mengambil profil' }
    }
  },

  /**
   * Cek apakah profil sudah lengkap (NIK, wali, dll)
   * Return: { is_complete, missing: [], message }
   */
  async getProfileStatus() {
    try {
      const response = await apiClient.get('/pasien/profile-status')
      return { success: true, data: response.data }
    } catch (error) {
      return { success: false, error: error.response?.data?.message || 'Gagal mengecek profil' }
    }
  },

  /**
   * Update profil pasien
   * Mendukung: name, nik, date_of_birth, gender, address,
   *            parent_name, parent_phone, parent_relation
   */
  async updateProfile(profileData) {
    try {
      const response = await apiClient.put('/pasien/profile', profileData)
      const updated = response.data?.data || response.data
      if (updated?.user) {
        localStorage.setItem('patient_user', JSON.stringify(updated.user))
      }
      return { success: true, data: updated }
    } catch (error) {
      const responseData = error.response?.data
      let errorMsg = responseData?.message || 'Gagal memperbarui profil'
      if (responseData?.errors) {
        const fieldErrors = Object.values(responseData.errors).flat()
        errorMsg = fieldErrors.join(', ')
      }
      return { success: false, error: errorMsg }
    }
  },

  // ── Dashboard ─────────────────────────────────────────────────────────

  async getDashboardStats() {
    try {
      const response = await apiClient.get('/pasien/dashboard')
      return { success: true, data: response.data?.data || response.data }
    } catch (error) {
      return { success: false, error: error.response?.data?.message || 'Gagal mengambil data dashboard' }
    }
  },

  async getDashboard() {
    return this.getDashboardStats()
  },

  // ── Antrian & Booking ─────────────────────────────────────────────────

  async getMyQueue() {
    try {
      const response = await apiClient.get('/pasien/antrian-saya')
      return { success: true, data: response.data?.data || response.data }
    } catch (error) {
      return { success: false, error: error.response?.data?.message || 'Gagal mengambil antrian' }
    }
  },

  async bookQueue(data) {
    try {
      const response = await apiClient.post('/pasien/booking', data)
      return response.data
    } catch (error) {
      // Lempar error supaya BookingView bisa tangkap detail (termasuk missing fields)
      throw error
    }
  },

  // ── Jadwal & Riwayat ──────────────────────────────────────────────────

  async getTherapySchedule() {
    try {
      const response = await apiClient.get('/pasien/jadwal-terapi')
      return { success: true, data: response.data?.data || response.data }
    } catch (error) {
      return { success: false, error: error.response?.data?.message || 'Gagal mengambil jadwal' }
    }
  },

  async getMedicalHistory() {
    try {
      const response = await apiClient.get('/pasien/riwayat-medis')
      return { success: true, data: response.data?.data || response.data }
    } catch (error) {
      return { success: false, error: error.response?.data?.message || 'Gagal mengambil riwayat' }
    }
  },

  // ── Referensi ─────────────────────────────────────────────────────────

  async getDoctors() {
    try {
      const response = await apiClient.get('/pasien/doctors')
      return response.data
    } catch (error) {
      return { success: false, data: [] }
    }
  },

  async getPolis() {
    try {
      const response = await apiClient.get('/pasien/polis')
      return response.data
    } catch (error) {
      return { success: false, data: [] }
    }
  },

  // ── Upload ────────────────────────────────────────────────────────────

  async uploadKtp(file) {
    try {
      const form = new FormData()
      form.append('ktp_photo', file)
      const response = await apiClient.post('/pasien/upload/ktp', form, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      return { success: true, data: response.data }
    } catch (error) {
      const msg = error.response?.data?.message
        || Object.values(error.response?.data?.errors || {}).flat().join(', ')
        || 'Gagal upload foto KTP'
      return { success: false, error: msg }
    }
  },

  async uploadAvatar(file) {
    try {
      const form = new FormData()
      form.append('avatar', file)
      const response = await apiClient.post('/pasien/upload/avatar', form, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      return { success: true, data: response.data }
    } catch (error) {
      const msg = error.response?.data?.message
        || Object.values(error.response?.data?.errors || {}).flat().join(', ')
        || 'Gagal upload foto profil'
      return { success: false, error: msg }
    }
  },

  async getKtpStatus() {
    try {
      const response = await apiClient.get('/pasien/ktp-status')
      return { success: true, data: response.data?.data || response.data }
    } catch (error) {
      return { success: false, error: 'Gagal mengecek status KTP' }
    }
  },

  // ── Storage helpers ───────────────────────────────────────────────────

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
}
