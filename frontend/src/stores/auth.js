import { defineStore } from 'pinia'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('token') || null,
    loading: false,
    error: null
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    userRole: (state) => state.user?.role || null
  },

  actions: {
    async login(credentials) {
      this.loading = true
      this.error = null
      try {
        const response = await api.post('/login', credentials)
        if (response.data.success) {
          this.token = response.data.data.token
          this.user = response.data.data.user
          
          localStorage.setItem('token', this.token)
          localStorage.setItem('user', JSON.stringify(this.user))
          
          return true
        }
        return false
      } catch (error) {
        this.error = error.response?.data?.message || 'Login gagal. Silakan coba lagi.'
        throw error
      } finally {
        this.loading = false
      }
    },

    async register(userData) {
      this.loading = true
      this.error = null
      try {
        const response = await api.post('/register', userData)
        if (response.data.success) {
          return true
        }
        return false
      } catch (error) {
        this.error = error.response?.data?.message || 'Registrasi gagal.'
        throw error
      } finally {
        this.loading = false
      }
    },

    async logout() {
      try {
        await api.post('/logout')
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.user = null
        this.token = null
        localStorage.removeItem('token')
        localStorage.removeItem('user')
      }
    },

    async fetchUser() {
      try {
        const response = await api.get('/user')
        if (response.data.success) {
          this.user = response.data.data
          localStorage.setItem('user', JSON.stringify(this.user))
        }
      } catch (error) {
        console.error('Fetch user error:', error)
      }
    }
  }
})
