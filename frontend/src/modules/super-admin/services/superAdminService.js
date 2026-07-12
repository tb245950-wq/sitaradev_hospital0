import api from '../../../core/services/api'

export const superAdminService = {
  // Dashboard
  getDashboard() {
    return api.get('/super-admin/dashboard')
  },

  // Audit Logs (SystemAuditLog - CRUD super admin)
  getAuditLogs(limit = 15, page = 1, filters = {}) {
    const params = new URLSearchParams({ limit, page })
    if (filters.module)      params.append('module', filters.module)
    if (filters.action)      params.append('action', filters.action)
    if (filters.status)      params.append('status', filters.status)
    if (filters.search)      params.append('search', filters.search)
    if (filters.anomaly_only) params.append('anomaly_only', '1')
    return api.get(`/super-admin/audit-logs?${params.toString()}`)
  },

  // Activity Logs (ActivityLog - semua aksi klinik)
  getActivityLogs(limit = 15, page = 1, filters = {}) {
    const params = new URLSearchParams({ limit, page })
    if (filters.search) params.append('search', filters.search)
    if (filters.status) params.append('status', filters.status)
    if (filters.type)   params.append('type', filters.type)
    if (filters.date)   params.append('date', filters.date)
    return api.get(`/super-admin/activity-logs?${params.toString()}`)
  },

  // Users
  getUsers(params = '') {
    let url = '/super-admin/users'
    if (params) url += '?' + params
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
  },

  // Backup
  getBackups() {
    return api.get('/super-admin/backups')
  },

  createBackup() {
    return api.post('/super-admin/backup')
  },

  // Settings
  getSettings() {
    return api.get('/super-admin/settings')
  },

  saveSettings(data) {
    return api.post('/super-admin/settings', data)
  }
}
