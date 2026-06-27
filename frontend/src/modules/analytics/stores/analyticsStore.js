import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { analyticsService } from '../services/analyticsService'
import { useAuthStore } from '../../auth/stores/authStore'

export const useAnalyticsStore = defineStore('analytics', () => {
  const authStore = useAuthStore()

  const stats = ref({
    // Admin
    total_patients: 0, patients_today: 0,
    total_queues_today: 0, waiting_queues: 0, calling_queues: 0, completed_queues: 0,
    total_assessments: 0, assessments_today: 0, active_therapies: 0, attendance_rate: 0,
    // Dokter
    my_patients: 0, assessments_today_me: 0, waiting_queues_me: 0, completed_queues_me: 0,
    // Terapis
    my_sessions: 0, sessions_today: 0, active_therapies_me: 0,
  })

  const visitTrends          = ref([])  // [{date, label, patients}]
  const diagnosisDistribution = ref([]) // [{label, value}]
  const recentActivities     = ref([])
  const todayFormatted       = ref('')
  const loading              = ref(false)
  const error                = ref(null)
  const period               = ref('week') // week | month | year

  const role = computed(() => authStore.userRole)

  async function fetchAnalytics() {
    if (!role.value) return

    loading.value = true
    error.value   = null

    try {
      const response = await analyticsService.getDashboardAnalytics(period.value)

      if (!response.success) {
        error.value = response.message || 'Gagal memuat data'
        return
      }

      const d = response.data
      todayFormatted.value = response.today_formatted || ''

      if (role.value === 'admin') {
        stats.value = {
          ...stats.value,
          total_patients:     d.total_patients     ?? 0,
          patients_today:     d.patients_today     ?? 0,
          total_queues_today: d.total_queues_today ?? 0,
          waiting_queues:     d.waiting_queues     ?? 0,
          calling_queues:     d.calling_queues     ?? 0,
          completed_queues:   d.completed_queues   ?? 0,
          total_assessments:  d.total_assessments  ?? 0,
          assessments_today:  d.assessments_today  ?? 0,
          active_therapies:   d.active_therapies   ?? 0,
          attendance_rate:    d.attendance_rate    ?? 0,
        }
        visitTrends.value = (d.visit_trend || []).map(item => ({
          date:  item.date,
          label: item.label,
          count: item.patients ?? item.count ?? 0,
        }))
        recentActivities.value = d.recent_activities || []
        // diagnosis_dist dari backend: { "Skizofrenia": 12, "Depresi": 8 }
        const colors = ['#6366f1','#f59e0b','#10b981','#ef4444','#3b82f6']
        diagnosisDistribution.value = Object.entries(d.diagnosis_dist || {})
          .map(([category, count], i) => ({ category, count, color: colors[i % colors.length] }))

      } else if (role.value === 'dokter') {
        const s = d.summary || {}
        stats.value = {
          ...stats.value,
          my_patients:          s.total_patients    ?? 0,
          assessments_today_me: s.assessments_today ?? 0,
          waiting_queues_me:    s.waiting_queues    ?? 0,
          completed_queues_me:  s.completed_queues  ?? 0,
          attendance_rate:      s.attendance_rate   ?? 0,
        }
        visitTrends.value = (d.visit_trend || []).map(item => ({
          date:  item.date,
          label: item.label,
          count: item.patients ?? item.count ?? 0,
        }))
        recentActivities.value = d.recent_activities || []

      } else if (role.value === 'terapis') {
        const s = d.summary || {}
        stats.value = {
          ...stats.value,
          my_sessions:         s.my_sessions      ?? 0,
          sessions_today:      s.sessions_today   ?? 0,
          active_therapies_me: s.active_therapies ?? 0,
          attendance_rate:     s.attendance_rate  ?? 0,
        }
        recentActivities.value = d.recent_activities || []
      }

    } catch (err) {
      const status = err.response?.status
      if (status === 401)      error.value = 'Sesi berakhir. Silakan login kembali.'
      else if (status === 403) error.value = 'Akses ditolak.'
      else if (!err.response)  error.value = 'Tidak dapat terhubung ke server.'
      else                     error.value = err.response?.data?.message || 'Gagal memuat data.'
    } finally {
      loading.value = false
    }
  }

  // Polling
  let _timer = null
  function startPolling(ms = 30000) {
    stopPolling()
    _timer = setInterval(fetchAnalytics, ms)
  }
  function stopPolling() {
    if (_timer) { clearInterval(_timer); _timer = null }
  }

  function setPeriod(p) {
    period.value = p
    fetchAnalytics()
  }

  function $reset() {
    stats.value = {
      total_patients: 0, patients_today: 0,
      total_queues_today: 0, waiting_queues: 0, calling_queues: 0, completed_queues: 0,
      total_assessments: 0, assessments_today: 0, active_therapies: 0, attendance_rate: 0,
      my_patients: 0, assessments_today_me: 0, waiting_queues_me: 0, completed_queues_me: 0,
      my_sessions: 0, sessions_today: 0, active_therapies_me: 0,
    }
    visitTrends.value = []
    diagnosisDistribution.value = []
    recentActivities.value = []
    loading.value = false
    error.value = null
    todayFormatted.value = ''
  }

  return {
    stats, visitTrends, diagnosisDistribution, recentActivities, todayFormatted,
    loading, error, role, period,
    fetchAnalytics, setPeriod, startPolling, stopPolling, $reset
  }
})
