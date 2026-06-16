import axios from 'axios'

const API_URL = 'http://127.0.0.1:8000/api'

export const patientService = {
  // ===== AUTHENTICATION =====
  async login(email, password) {
    const response = await axios.post(`${API_URL}/pasien/login`, { email, password })
    if (response.data.success) {
      localStorage.setItem('patient_token', response.data.data.token)
      localStorage.setItem('patient_user', JSON.stringify(response.data.data.user))
    }
    return response.data
  },

  async register(patientData) {
    const response = await axios.post(`${API_URL}/pasien/register`, patientData)
    if (response.data.success) {
      localStorage.setItem('patient_token', response.data.data.token)
      localStorage.setItem('patient_user', JSON.stringify(response.data.data.user))
    }
    return response.data
  },

  async logout() {
    const token = localStorage.getItem('patient_token')
    try {
      await axios.post(`${API_URL}/pasien/logout`, {}, {
        headers: { 'Authorization': `Bearer ${token}` }
      })
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      localStorage.removeItem('patient_token')
      localStorage.removeItem('patient_user')
    }
  },

  // ===== PROFILE =====
  async getProfile() {
    const token = localStorage.getItem('patient_token')
    const response = await axios.get(`${API_URL}/pasien/profile`, {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    return response.data
  },

  async updateProfile(profileData) {
    const token = localStorage.getItem('patient_token')
    const response = await axios.put(`${API_URL}/pasien/profile`, profileData, {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    return response.data
  },

  // ===== DASHBOARD =====
  async getDashboardStats() {
    const token = localStorage.getItem('patient_token')
    const response = await axios.get(`${API_URL}/pasien/dashboard`, {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    return response.data
  },

  // ===== BOOKING ANTRIAN =====
  async bookQueue(bookingData) {
    const token = localStorage.getItem('patient_token')
    const response = await axios.post(`${API_URL}/pasien/booking`, bookingData, {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    return response.data
  },

  async getMyQueues() {
    const token = localStorage.getItem('patient_token')
    const response = await axios.get(`${API_URL}/pasien/antrian-saya`, {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    return response.data
  },

  async cancelQueue(queueId) {
    const token = localStorage.getItem('patient_token')
    const response = await axios.post(`${API_URL}/pasien/antrian/${queueId}/cancel`, {}, {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    return response.data
  },

  // ===== DOKTER =====
  async getDoctors() {
    const token = localStorage.getItem('patient_token')
    const response = await axios.get(`${API_URL}/pasien/doctors`, {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    return response.data
  },

  // ===== JADWAL TERAPI =====
  async getSchedule() {
    const token = localStorage.getItem('patient_token')
    const response = await axios.get(`${API_URL}/pasien/jadwal`, {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    return response.data
  },

  // ===== RIWAYAT MEDIS =====
  async getHistory() {
    const token = localStorage.getItem('patient_token')
    const response = await axios.get(`${API_URL}/pasien/riwayat`, {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    return response.data
  },

  // ===== HELPERS =====
  getStoredUser() {
    const user = localStorage.getItem('patient_user')
    return user ? JSON.parse(user) : null
  },

  getToken() {
    return localStorage.getItem('patient_token')
  },

  isAuthenticated() {
    return !!this.getToken()
  }
}
