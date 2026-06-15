import api from '../../../core/services/api'
export const reportService = {
  getReports(params) { return api.get('/reports', { params }) },
  getReport(id) { return api.get(`/reports/${id}`) },
  generateReport(data) { return api.post('/reports/generate', data) },
  downloadReport(id) { return api.get(`/reports/${id}/download`, { responseType: 'blob' }) }
}
