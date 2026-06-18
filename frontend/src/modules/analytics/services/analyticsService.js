import axios from 'axios'

const API_URL = import.meta.env.VITE_API_BASE_URL || '/api'

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  timeout: 15000 // 15 second timeout
})

// Add interceptor to include token
api.interceptors.request.use(config => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Add response interceptor for error handling
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      // Token expired or invalid — clear auth and redirect to login
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      if (window.location.pathname !== '/login') {
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

export const analyticsService = {
  // Get dashboard stats
  async getStats() {
    const response = await api.get('/analytics/stats')
    return response.data
  },
  
  // Get visit trends
  async getVisitTrends(period = 'month') {
    const response = await api.get('/analytics/visit-trends', {
      params: { period }
    })
    return response.data
  },
  
  // Get diagnosis distribution
  async getDiagnosisDistribution() {
    const response = await api.get('/analytics/diagnosis-distribution')
    return response.data
  },
  
  // Get recent activities
  async getRecentActivities(limit = 10) {
    const response = await api.get('/analytics/recent-activities', {
      params: { limit }
    })
    return response.data
  },
  
  // Get complete dashboard analytics
  async getDashboardAnalytics(period = 'month') {
    const response = await api.get('/analytics/dashboard', {
      params: { period }
    })
    return response.data
  }
}
