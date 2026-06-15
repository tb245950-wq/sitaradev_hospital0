import { defineStore } from 'pinia'
import { ref } from 'vue'
import { queueService } from '../services/queueService'

export const useQueueStore = defineStore('queue', () => {
  const queues = ref([])
  const stats = ref({
    waiting: 0,
    calling: 0,
    completed: 0,
    high_priority: 0
  })
  const loading = ref(false)
  const error = ref(null)
  
  async function fetchQueues(params = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await queueService.getQueues(params)
      queues.value = response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal mengambil data antrian'
    } finally {
      loading.value = false
    }
  }
  
  async function getStats() {
    try {
      const response = await queueService.getStats()
      if (response.success) {
        stats.value = response.data
      }
      return response
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal mengambil statistik'
      return { success: false }
    }
  }
  
  async callNext(params = {}) {
    try {
      const response = await queueService.callNext(params)
      await fetchQueues()
      await getStats()
      return response
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal memanggil pasien'
      return { success: false }
    }
  }

  async function callQueue(id) {
    try {
      const response = await queueService.callQueue(id)
      await fetchQueues()
      await getStats()
      return response
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal memanggil pasien'
      return { success: false }
    }
  }

  async function completeQueue(id) {
    try {
      const response = await queueService.completeQueue(id)
      await fetchQueues()
      await getStats()
      return response
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal menyelesaikan antrian'
      return { success: false }
    }
  }
  
  async function cancelQueue(id) {
    try {
      const response = await queueService.cancelQueue(id)
      await fetchQueues()
      await getStats()
      return response
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal membatalkan antrian'
      return { success: false }
    }
  }
  
  return {
    queues,
    stats,
    loading,
    error,
    fetchQueues,
    getStats,
    callNext,
    completeQueue,
    cancelQueue
  }
})
