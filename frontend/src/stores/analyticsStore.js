import { defineStore } from 'pinia'
import { ref } from 'vue'
import { analyticsService } from '../services/analyticsService'

export const useAnalyticsStore = defineStore('analytics', () => {
  const stats = ref({})
  const recentActivities = ref([])
  const loading = ref(false)
  const error = ref(null)
  
  async function fetchAnalytics() {
    loading.value = true
    error.value = null
    try {
      const response = await analyticsService.getDashboardAnalytics()
      if (response.success) {
        stats.value = response.data.summary
        recentActivities.value = response.data.recent_activities
      } else {
        error.value = response.message
      }
    } catch (err) {
      error.value = 'Gagal memuat data analytics'
    } finally {
      loading.value = false
    }
  }
  
  return { stats, recentActivities, loading, error, fetchAnalytics }
})
