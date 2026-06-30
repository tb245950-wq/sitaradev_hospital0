import api from '../../../core/services/api'

export const superAdminService = {
  // Dashboard
  getDashboard() {
    return api.get('/super-admin/dashboard')
  },

  // Audit Logs
  getAuditLogs(limit = 10, page = 1) {
    return api.get(`/super-admin/audit-logs?limit=${limit}&page=${page}`)
  },

  // Users
  getUsers(role = null, status = null) {
    let url = '/super-admin/users'
    const params = []
    if (role) params.push(`role=${role}`)
    if (status) params.push(`status=${status}`)
    if (params.length) url += '?' + params.join('&')
    return api.get(url)
  },

  createUser(data) {
    return api.post('/super-admin/users', data)
  },

  updateUser(userId, data) {
    return api.put(`/super-admin/users/${userId}`, data)
  },

  deleteUser(userId) {
    return api.delete(`/super-admin/users/${userId}`)
  },

  resetPassword(userId, password) {
    return api.post(`/super-admin/users/${userId}/reset-password`, { password })
  },

  // Polis
  getPolis() {
    return api.get('/super-admin/polis')
  },

  createPoli(data) {
    return api.post('/super-admin/polis', data)
  },

  updatePoli(poliId, data) {
    return api.put(`/super-admin/polis/${poliId}`, data)
  },

  deletePoli(poliId) {
    return api.delete(`/super-admin/polis/${poliId}`)
  },

  // Login History
  getLoginHistory(limit = 20, page = 1, success = null) {
    let url = `/super-admin/login-history?limit=${limit}&page=${page}`
    if (success !== null) url += `&success=${success}`
    return api.get(url)
  },

  // Failed Logins
  getFailedLogins(days = 7) {
    return api.get(`/super-admin/failed-logins?days=${days}`)
  }
}
