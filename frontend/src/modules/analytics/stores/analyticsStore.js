import { defineStore } from 'pinia'
import { ref } from 'vue'
import { analyticsService } from '../services/analyticsService'

export const useAnalyticsStore = defineStore('analytics', () => {
  const stats = ref({
    total_patients: 0,
    patients_today: 0,
    patients_this_month: 0,
    total_queues_today: 0,
    waiting_queues: 0,
    calling_queues: 0,
    completed_queues: 0,
    total_assessments: 0,
    assessments_today: 0,
    active_therapies: 0,
    attendance_rate: 0
  })
  
  const visitTrends = ref([])
  const diagnosisDistribution = ref([])
  const recentActivities = ref([])
  
  const loading = ref(false)
  const error = ref(null)
  const selectedPeriod = ref('month')
  const todayFormatted = ref('')
  
  // Fetch all analytics data
  async function fetchAnalytics() {
    loading.value = true
    error.value = null
    
    try {
      const response = await analyticsService.getDashboardAnalytics(selectedPeriod.value)
      
      if (response.success && response.data) {
        const data = response.data
        
        // Map summary to stats
        if (data.summary) {
          stats.value = {
            total_patients: data.summary.total_patients || 0,
            patients_today: data.summary.patients_today || 0,
            patients_this_month: data.summary.patients_this_month || 0,
            total_queues_today: data.summary.total_queues_today || 0,
            waiting_queues: data.summary.waiting_queues || 0,
            calling_queues: data.summary.calling_queues || 0,
            completed_queues: data.summary.completed_queues || 0,
            total_assessments: data.summary.total_assessments || 0,
            assessments_today: data.summary.assessments_today || 0,
            active_therapies: data.summary.active_therapies || 0,
            attendance_rate: data.summary.attendance_rate || 0
          }
        }
        
        // Map visit_trend to include 'count' for Chart.js
        visitTrends.value = (data.visit_trend || []).map(item => ({
          ...item,
          count: item.patients || 0 // Chart.js expects 'count'
        }))
        
        diagnosisDistribution.value = data.diagnosis_distribution || []
        recentActivities.value = data.recent_activities || []
        todayFormatted.value = response.today_formatted || ''
      } else {
        error.value = response.message || 'Gagal memuat data'
      }
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
    todayFormatted,
    fetchAnalytics,
    updatePeriod
  }
})
