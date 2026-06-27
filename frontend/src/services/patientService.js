import api from './api'

export const patientService = {
  async login(email, password) {
    console.log('🔐 Attempting patient login:', email)
    
    try {
      const response = await api.post('/pasien/login', {
        email,
        password
      })
      
      console.log('✅ Patient login response:', response.data)
      
      if (response.data.success) {
        localStorage.setItem('patient_token', response.data.data.token)
        localStorage.setItem('user', JSON.stringify(response.data.data.user))
        localStorage.setItem('patient', JSON.stringify(response.data.data.patient))
        
        return {
          success: true,
          data: response.data.data
        }
      } else {
        return {
          success: false,
          error: response.data.message || 'Login gagal'
        }
      }
    } catch (error) {
      console.error('❌ Patient login error:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Email atau password salah'
      }
    }
  },

  async register(patientData) {
    try {
      const response = await api.post('/pasien/register', patientData)
      
      if (response.data.success) {
        localStorage.setItem('patient_token', response.data.data.token)
        localStorage.setItem('user', JSON.stringify(response.data.data.user))
        
        return {
          success: true,
          data: response.data.data
        }
      } else {
        return {
          success: false,
          error: response.data.message
        }
      }
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Registrasi gagal'
      }
    }
  },

  async logout() {
    try {
      await api.post('/pasien/logout')
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      localStorage.removeItem('patient_token')
      localStorage.removeItem('user')
      localStorage.removeItem('patient')
    }
  },

  async getDashboard() {
    try {
      const response = await api.get('/pasien/dashboard')
      return {
        success: true,
        data: response.data.data
      }
    } catch (error) {
      return {
        success: false,
        error: error.message
      }
    }
  },

  isAuthenticated() {
    return !!localStorage.getItem('patient_token')
  },

  getStoredUser() {
    try {
      const user = localStorage.getItem('user')
      return user ? JSON.parse(user) : null
    } catch (error) {
      return null
    }
  }
}