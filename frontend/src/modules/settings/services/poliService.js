import api from '../../../core/services/api'

export const poliService = {
  getAll: ()             => api.get('/polis').then(r => r.data),
  create: (data)         => api.post('/polis', data).then(r => r.data),
  update: (id, data)     => api.put(`/polis/${id}`, data).then(r => r.data),
  remove: (id)           => api.delete(`/polis/${id}`).then(r => r.data),

  // Untuk pasien — endpoint berbeda
  getAktif: ()           => api.get('/pasien/polis').then(r => r.data),
}
