import axios from 'axios'

const API_URL = 'http://127.0.0.1:8000/api'

export const authService = {
  async login(email, password) {
    const response = await axios.post(`${API_URL}/login`, { 
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
    const response = await axios.post(`${API_URL}/register`, userData)
    return response.data
  },

  async logout() {
    const token = localStorage.getItem('token')
    await axios.post(`${API_URL}/logout`, {}, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    })
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  },

  async getCurrentUser() {
    const token = localStorage.getItem('token')
    const response = await axios.get(`${API_URL}/user`, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    })
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