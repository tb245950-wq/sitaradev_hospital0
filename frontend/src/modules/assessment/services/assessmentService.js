import api from '../../../core/services/api'

export const assessmentService = {
  async getAssessments(params = {}) {
    const response = await api.get('/assessments', { params })
    return response.data
  },
  
  async createAssessment(data) {
    const response = await api.post('/assessments', data)
    return response.data
  },
  
  async getAssessmentById(id) {
    const response = await api.get(`/assessments/${id}`)
    return response.data
  },
  
  async updateAssessment(id, data) {
    const response = await api.put(`/assessments/${id}`, data)
    return response.data
  },
  
  async deleteAssessment(id) {
    const response = await api.delete(`/assessments/${id}`)
    return response.data
  },
  
  async submitAssessment(id) {
    const response = await api.post(`/assessments/${id}/submit`)
    return response.data
  },
  
  async approveAssessment(id) {
    const response = await api.post(`/assessments/${id}/approve`)
    return response.data
  }
}
