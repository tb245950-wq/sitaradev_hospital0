import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api'

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Add interceptor to include token
api.interceptors.request.use(config => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

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
