import { defineStore } from 'pinia'
import { ref } from 'vue'
import { queueService } from '../services/queueService'
import { useAnalyticsStore } from '../../analytics/stores/analyticsStore'

export const useQueueStore = defineStore('queue', () => {
  const queues = ref([])
  const stats = ref({
    waiting: [],
    calling: [],
    completed: [],
    high_priority: [],
    waiting_count: 0,
    calling_count: 0,
    completed_count: 0,
    high_priority_count: 0
  })
  const loading = ref(false)
  const error = ref(null)
  
  async function fetchQueues(params = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await queueService.getQueues(params)
      // Handle paginated response - data is nested in response.data
      queues.value = response.data || []
      if (queues.value.length > 0) {
      }
    } catch (err) {
      console.error('[QueueStore] Fetch error:', err)
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

  function _refreshAnalytics() {
    useAnalyticsStore().fetchAnalytics()
  }
  
  async function addToQueue(data) {
    try {
      const response = await queueService.addToQueue(data)
      if (response.success) {
        await fetchQueues({ status: 'menunggu,dipanggil' })
        await getStats()
        _refreshAnalytics()
      }
      return response
    } catch (err) {
      const message = err.response?.data?.message || 'Gagal menambah antrian'
      error.value = message
      return { success: false, message }
    }
  }

  async function callNext(params = {}) {
    try {
      const response = await queueService.callNext(params)
      await fetchQueues()
      await getStats()
      _refreshAnalytics()
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
      _refreshAnalytics()
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
      _refreshAnalytics()
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
      _refreshAnalytics()
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
    addToQueue,
    callNext,
    callQueue,
    completeQueue,
    cancelQueue
  }
})
