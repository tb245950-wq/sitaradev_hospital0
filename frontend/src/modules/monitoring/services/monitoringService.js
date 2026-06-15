import api from '../../../core/services/api'
export const monitoringService = {
  getMonitorings(params) { return api.get('/monitorings', { params }) },
  getMonitoring(id) { return api.get(`/monitorings/${id}`) },
  createMonitoring(data) { return api.post('/monitorings', data) },
  updateMonitoring(id, data) { return api.put(`/monitorings/${id}`, data) },
  deleteMonitoring(id) { return api.delete(`/monitorings/${id}`) },
  generateAssessment(id_terapi) { return api.post(`/monitorings/generate-assessment/${id_terapi}`) }
}
