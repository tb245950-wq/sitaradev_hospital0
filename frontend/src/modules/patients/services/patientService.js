import api from '../../../core/services/api'

export const patientService = {
  // Get all patients
  getPatients(params) {
    return api.get('/patients', { params })
  },

  // Get single patient
  getPatient(id) {
    return api.get(`/patients/${id}`)
  },

  // Create new patient
  createPatient(data) {
    return api.post('/patients', data)
  },

  // Update patient
  updatePatient(id, data) {
    return api.put(`/patients/${id}`, data)
  },

  // Delete patient
  deletePatient(id) {
    return api.delete(`/patients/${id}`)
  }
}
