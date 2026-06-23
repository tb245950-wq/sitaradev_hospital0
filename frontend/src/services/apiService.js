import api from './api'

export const patientService = {
  getAll: (params) => api.get('/patients', { params }),
  getById: (id) => api.get(`/patients/${id}`),
  create: (data) => api.post('/patients', data),
  update: (id, data) => api.put(`/patients/${id}`, data),
  delete: (id) => api.delete(`/patients/${id}`)
}

export const assessmentService = {
  getAll: () => api.get('/assessments'),
  create: (data) => api.post('/assessments', data),
  getById: (id) => api.get(`/assessments/${id}`)
}

export const queueService = {
  getAll: () => api.get('/queues'),
  callNext: () => api.post('/queues/call-next')
}
