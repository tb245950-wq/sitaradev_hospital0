import api from '../../../core/services/api'
export const queueService = {
  getQueues(params) { return api.get('/queues', { params }) },
  getQueue(id) { return api.get(`/queues/${id}`) },
  createQueue(data) { return api.post('/queues', data) },
  updateQueue(id, data) { return api.put(`/queues/${id}`, data) },
  deleteQueue(id) { return api.delete(`/queues/${id}`) },
  updateStatus(id, status) { return api.patch(`/queues/${id}/status`, { status }) }
}
