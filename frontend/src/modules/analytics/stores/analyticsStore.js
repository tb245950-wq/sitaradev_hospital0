import { defineStore } from 'pinia'
import { ref } from 'vue'
import { analyticsService } from '../services/analyticsService'

export const useAnalyticsStore = defineStore('analytics', () => {
  const stats = ref({
    total_patients: { value: 0, trend: 0, trend_label: '' },
    today_sessions: { value: 0, completed: 0, remaining: 0 },
    waiting_list: { value: 0, high_priority: 0 },
    attendance_rate: { value: 0, period: '' }
  })
  
  const visitTrends = ref([])
  const diagnosisDistribution = ref([])
  const recentActivities = ref([])
  
  const loading = ref(false)
  const error = ref(null)
  const selectedPeriod = ref('month')
  
  // Fetch all analytics data
  async function fetchAnalytics() {
    loading.value = true
    error.value = null
    
    try {
      const response = await analyticsService.getDashboardAnalytics(selectedPeriod.value)
      
      const data = response.data
      stats.value = data.stats
      visitTrends.value = data.visit_trends
      diagnosisDistribution.value = data.diagnosis_distribution
      recentActivities.value = data.recent_activities
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal mengambil data analytics'
      console.error('Analytics Error:', err)
    } finally {
      loading.value = false
    }
  }
  
  // Update period and refetch
  async function updatePeriod(period) {
    selectedPeriod.value = period
    await fetchAnalytics()
  }
  
  return {
    stats,
    visitTrends,
    diagnosisDistribution,
    recentActivities,
    loading,
    error,
    selectedPeriod,
    fetchAnalytics,
    updatePeriod
  }
})
