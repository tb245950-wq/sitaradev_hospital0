import api from '../../../core/services/api'
export const assessmentService = {
  getAssessments(params) { return api.get('/assessments', { params }) },
  getAssessment(id) { return api.get(`/assessments/${id}`) },
  createAssessment(data) { return api.post('/assessments', data) },
  updateAssessment(id, data) { return api.put(`/assessments/${id}`, data) },
  deleteAssessment(id) { return api.delete(`/assessments/${id}`) }
}
