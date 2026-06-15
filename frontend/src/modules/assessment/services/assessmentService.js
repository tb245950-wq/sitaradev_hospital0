import api from '../../../core/services/api'

export const assessmentService = {
  /**
   * Get all assessments with pagination and filters
   * @param {Object} params { page, search, status }
   */
  async getAssessments(params = {}) {
    const response = await api.get('/assessments', { params })
    return response.data
  },

  /**
   * Get assessment by ID
   */
  async getAssessmentById(id) {
    const response = await api.get(`/assessments/${id}`)
    return response.data
  },

  /**
   * Create new assessment
   */
  async createAssessment(data) {
    const response = await api.post('/assessments', data)
    return response.data
  },

  /**
   * Update existing assessment
   */
  async updateAssessment(id, data) {
    const response = await api.put(`/assessments/${id}`, data)
    return response.data
  },

  /**
   * Delete assessment
   */
  async deleteAssessment(id) {
    const response = await api.delete(`/assessments/${id}`)
    return response.data
  }
}
