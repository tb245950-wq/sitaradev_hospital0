import { defineStore } from 'pinia'
import { ref } from 'vue'
import { monitoringService } from '../services/monitoringService'
import { useAnalyticsStore } from '../../analytics/stores/analyticsStore'

export const useMonitoringStore = defineStore('monitoring', () => {
  const monitorings = ref([])
  const loading = ref(false)
  const error = ref(null)
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0
  })

  async function fetchMonitorings(params = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await monitoringService.getMonitorings(params)
      monitorings.value = response.data.data
      
      const meta = response.data.meta || response.data
      pagination.value = {
        current_page: meta.current_page,
        last_page: meta.last_page,
        per_page: meta.per_page,
        total: meta.total
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal memuat data monitoring'
    } finally {
      loading.value = false
    }
  }

  async function generateAssessment(id_terapi) {
    loading.value = true
    error.value = null
    try {
      const response = await monitoringService.generateAssessment(id_terapi)
      useAnalyticsStore().fetchAnalytics()
      return { success: true, data: response.data.data }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal generate assessment'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  return { monitorings, loading, error, pagination, fetchMonitorings, generateAssessment }
})
