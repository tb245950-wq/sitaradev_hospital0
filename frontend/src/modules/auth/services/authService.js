import api from '../../../core/services/api'

export const authService = {
  async login(email, password) {
    console.log('🔐 === AUTH SERVICE LOGIN ===')
    console.log('Email:', email)
    console.log('API BaseURL:', api.defaults.baseURL)
    
    try {
      const response = await api.post('/login', {
        email,
        password
      })
      
      console.log('📥 Response received:', response)
      console.log('Response data:', response.data)
      
      // Backend response structure: { success: true, message: "...", data: { user: {...}, token: "..." } }
      const backendResponse = response.data
      
      if (backendResponse.success) {
        console.log('✅ Login successful!')
        console.log('User data:', backendResponse.data.user)
        console.log('Token:', backendResponse.data.token)
        
        // Simpan ke localStorage
        localStorage.setItem('token', backendResponse.data.token)
        localStorage.setItem('user', JSON.stringify(backendResponse.data.user))
        
        console.log('✅ Data saved to localStorage')
        
        // Return structure yang konsisten
        return {
          success: true,
          data: backendResponse.data,
          message: backendResponse.message || 'Login berhasil'
        }
      } else {
        console.error('❌ Login failed:', backendResponse.message)
        return {
          success: false,
          error: backendResponse.message || 'Login gagal'
        }
      }
    } catch (error) {
      console.error('💥 LOGIN ERROR:')
      console.error('Error type:', error.constructor.name)
      console.error('Error message:', error.message)
      
      if (error.response) {
        console.error('Response status:', error.response.status)
        console.error('Response data:', error.response.data)
        
        // Handle error response dari backend
        return {
          success: false,
          error: error.response.data?.message || 'Email atau password salah'
        }
      } else if (error.request) {
        console.error('No response received')
        return {
          success: false,
          error: 'Tidak ada response dari server'
        }
      } else {
        console.error('Error setting up request:', error.message)
        return {
          success: false,
          error: error.message || 'Terjadi kesalahan'
        }
      }
    }
  },
  async register(userData) { /* ... */ },
  async logout() { /* ... */ },
  getStoredUser() {
  try {
    const user = localStorage.getItem('user')
    if (!user || user === 'undefined' || user === 'null') {
      return null
    }
    
    if (user.startsWith('<') || user.startsWith('{error')) {
      console.warn('Invalid user data, clearing...')
      localStorage.removeItem('user')
      return null
    }
    
    return JSON.parse(user)
  } catch (error) {
    console.error('Error parsing user:', error)
    localStorage.removeItem('user')
    return null
  }
},
  getToken() {
  try {
    const token = localStorage.getItem('token')
    if (!token || token === 'undefined' || token === 'null') {
      return null
    }
    
    if (token.startsWith('<') || token.startsWith('{')) {
      console.warn('Invalid token, clearing...')
      localStorage.removeItem('token')
      return null
    }
    
    return token
  } catch (error) {
    console.error('Error getting token:', error)
    localStorage.removeItem('token')
    return null
  }
}
}
