import api from './api'

export const authService = {
  async login(email, password) {
    const response = await api.post('/login', { email, password })
    if (response.data.success) {
      localStorage.setItem('token', response.data.data.token)
      localStorage.setItem('user', JSON.stringify(response.data.data.user))
    }
    return response.data
  },
  
  async logout() {
    await api.post('/logout')
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  },
  
  isAuthenticated() {
    return !!localStorage.getItem('token')
  },
  
  getStoredUser() {
    return JSON.parse(localStorage.getItem('user'))
  }
}
