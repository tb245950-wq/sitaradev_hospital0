import api from '../../../core/services/api'

export const therapyService = {
  async getTherapies(params = {}) {
    const response = await api.get('/therapies', { params })
    return response.data
  },
  
  async getTodaySessions() {
    // We can filter by status 'aktif' and date
    const response = await api.get('/therapies', { params: { status: 'aktif' } })
    return response.data
  },
  
  async createTherapy(therapyData) {
    const response = await api.post('/therapies', therapyData)
    return response.data
  },
  
  async getTherapyById(id) {
    const response = await api.get(`/therapies/${id}`)
    return response.data
  },
  
  async updateTherapy(id, therapyData) {
    const response = await api.put(`/therapies/${id}`, therapyData)
    return response.data
  },
  
  async deleteTherapy(id) {
    const response = await api.delete(`/therapies/${id}`)
    return response.data
  }
}
