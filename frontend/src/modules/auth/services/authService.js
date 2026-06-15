import api from '../../../core/services/api'

export const authService = {
  async login(email, password) {
    const response = await api.post('/login', { 
      email, 
      password 
    })
    
    if (response.data.success) {
      localStorage.setItem('token', response.data.data.token)
      localStorage.setItem('user', JSON.stringify(response.data.data.user))
    }
    
    return response.data
  },

  async register(userData) {
    const response = await api.post('/register', userData)
    return response.data
  },

  async logout() {
    try {
      await api.post('/logout')
    } finally {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    }
  },

  async getCurrentUser() {
    const response = await api.get('/user')
    return response.data.data
  },

  getStoredUser() {
    const user = localStorage.getItem('user')
    return user ? JSON.parse(user) : null
  },

  getToken() {
    return localStorage.getItem('token')
  },

  isAuthenticated() {
    return !!this.getToken()
  }
}