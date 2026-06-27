import api from '../../../core/services/api'

export const analyticsService = {
  async getDashboardAnalytics(period = 'week') {
    const response = await api.get('/analytics/dashboard', { params: { period } })
    return response.data
  }
}
