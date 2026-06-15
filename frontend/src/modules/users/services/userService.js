import api from '../../../core/services/api'

export const userService = {
  // Get all users with filters and pagination
  getUsers(params) {
    return api.get('/users', { params })
  },

  // Get single user
  getUser(id) {
    return api.get(`/users/${id}`)
  },

  // Create new user
  createUser(data) {
    return api.post('/users', data)
  },

  // Update user
  updateUser(id, data) {
    return api.put(`/users/${id}`, data)
  },

  // Delete user
  deleteUser(id) {
    return api.delete(`/users/${id}`)
  },

  // Update user status (active/inactive/suspended)
  updateUserStatus(id, status) {
    return api.patch(`/users/${id}/status`, { status })
  },

  // Reset user password
  resetPassword(id, data) {
    return api.post(`/users/${id}/reset-password`, data)
  }
}
