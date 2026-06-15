import api from '../../../core/services/api'

export const activityLogService = {
  async getRecentActivities(limit = 10) {
    const response = await api.get('/activity-logs', {
      params: { limit }
    })
    return response.data
  },
  
  async getAllActivities(page = 1, perPage = 20) {
    const response = await api.get('/activity-logs/all', {
      params: { page, per_page: perPage }
    })
    return response.data
  }
}
