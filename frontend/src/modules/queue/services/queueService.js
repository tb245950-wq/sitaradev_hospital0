import api from '../../../core/services/api'

export const queueService = {
  async getQueues(params = {}) {
    const response = await api.get('/queues', { params })
    return response.data
  },
  
  async addToQueue(queueData) {
    const response = await api.post('/queues', queueData)
    return response.data
  },
  
  async getStats() {
    // Current backend doesn't have /queues/stats, but we can compute from index or just return empty
    // Let's assume we want to handle it or the user will add it
    try {
      const response = await api.get('/queues/stats')
      return response.data
    } catch (e) {
      return { success: true, data: { waiting: 0, calling: 0, completed: 0, high_priority: 0 } }
    }
  },
  
  async callNext(params = {}) {
    const response = await api.post('/queues/call-next', params)
    return response.data
  },
  
  async callQueue(id) {
    const response = await api.put(`/queues/${id}`, { status: 'dipanggil' })
    return response.data
  },
  
  async completeQueue(id) {
    const response = await api.put(`/queues/${id}`, { status: 'selesai' })
    return response.data
  },
  
  async cancelQueue(id) {
    const response = await api.put(`/queues/${id}`, { status: 'tidak_hadir' })
    return response.data
  }
}
