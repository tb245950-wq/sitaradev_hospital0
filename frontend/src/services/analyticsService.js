import api from './api'

export const analyticsService = {
  async getDashboardAnalytics() {
    const response = await api.get('/analytics/dashboard')
    return response.data
  }
}
