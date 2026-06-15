import api from '../../../core/services/api'
export const monitoringService = {
  getMonitorings(params) { return api.get('/monitoring', { params }) },
  getMonitoring(id) { return api.get(`/monitoring/${id}`) },
  createMonitoring(data) { return api.post('/monitoring', data) },
  updateMonitoring(id, data) { return api.put(`/monitoring/${id}`, data) },
  deleteMonitoring(id) { return api.delete(`/monitoring/${id}`) }
}
