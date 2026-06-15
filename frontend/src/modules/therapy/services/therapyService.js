import api from '../../../core/services/api'
export const therapyService = {
  getTherapies(params) { return api.get('/therapies', { params }) },
  getTherapy(id) { return api.get(`/therapies/${id}`) },
  createTherapy(data) { return api.post('/therapies', data) },
  updateTherapy(id, data) { return api.put(`/therapies/${id}`, data) },
  deleteTherapy(id) { return api.delete(`/therapies/${id}`) }
}
