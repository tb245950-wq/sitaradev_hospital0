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
  const errorCode = ref(null)
  const selectedPeriod = ref('month')
  const todayFormatted = ref('')
  
  /**
   * Fetch all analytics data with robust error handling
   */
  async function fetchAnalytics() {
    loading.value = true
    error.value = null
    errorCode.value = null
    
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
            attendance_rate: data.summary.attendance_rate || 0,
            // Support doctor/therapist-specific keys
            my_assessments: data.summary.my_assessments || 0,
            my_patients: data.summary.my_patients || 0,
            my_sessions: data.summary.my_sessions || 0,
            sessions_today: data.summary.sessions_today || 0
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
        error.value = response.message || 'Gagal memuat data analytics'
      }
    } catch (err) {
      const status = err.response?.status
      errorCode.value = status || null

      // Handle specific HTTP error codes with user-friendly messages
      switch (status) {
        case 401:
          error.value = 'Sesi Anda telah berakhir. Silakan login kembali.'
          break
        case 403:
          error.value = 'Anda tidak memiliki izin untuk mengakses data analytics.'
          break
        case 404:
          error.value = 'Endpoint analytics tidak ditemukan. Hubungi administrator.'
          break
        case 422:
          error.value = err.response?.data?.message || 'Data yang dikirim tidak valid.'
          break
        case 500:
          error.value = 'Terjadi kesalahan pada server. Silakan coba lagi nanti.'
          break
        case 503:
          error.value = 'Server sedang dalam maintenance. Silakan coba beberapa saat lagi.'
          break
        default:
          if (err.code === 'ECONNABORTED' || err.message?.includes('timeout')) {
            error.value = 'Koneksi timeout. Periksa koneksi internet Anda.'
          } else if (!err.response) {
            error.value = 'Tidak dapat terhubung ke server. Periksa koneksi Anda.'
          } else {
            error.value = err.response?.data?.message || 'Gagal mengambil data analytics'
          }
      }

      console.error('Analytics Error:', {
        status,
        message: err.message,
        data: err.response?.data
      })
    } finally {
      loading.value = false
    }
  }
  
  // Update period and refetch
  async function updatePeriod(period) {
    selectedPeriod.value = period
    await fetchAnalytics()
  }

  // Reset store state
  function $reset() {
    stats.value = {
      total_patients: 0, patients_today: 0, patients_this_month: 0,
      total_queues_today: 0, waiting_queues: 0, calling_queues: 0, completed_queues: 0,
      total_assessments: 0, assessments_today: 0, active_therapies: 0, attendance_rate: 0,
      my_assessments: 0, my_patients: 0, my_sessions: 0, sessions_today: 0
    }
    visitTrends.value = []
    diagnosisDistribution.value = []
    recentActivities.value = []
    loading.value = false
    error.value = null
    errorCode.value = null
    todayFormatted.value = ''
  }
  
  return {
    stats,
    visitTrends,
    diagnosisDistribution,
    recentActivities,
    loading,
    error,
    errorCode,
    selectedPeriod,
    todayFormatted,
    fetchAnalytics,
    updatePeriod,
    $reset
  }
})
