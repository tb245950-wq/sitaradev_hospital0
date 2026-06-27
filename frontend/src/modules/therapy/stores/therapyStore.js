import { defineStore } from 'pinia'
import { ref } from 'vue'
import { therapyService } from '../services/therapyService'
import { useAnalyticsStore } from '../../analytics/stores/analyticsStore'

export const useTherapyStore = defineStore('therapy', () => {
  const therapies = ref([])
  const currentTherapy = ref(null)
  const todaySessions = ref([])
  const loading = ref(false)
  const error = ref(null)
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0
  })
  
  async function fetchTherapies(params = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await therapyService.getTherapies(params)
      therapies.value = response.data
      
      if (response.meta) {
        pagination.value = {
          current_page: response.meta.current_page,
          last_page: response.meta.last_page,
          per_page: response.meta.per_page,
          total: response.meta.total
        }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal mengambil data terapi'
    } finally {
      loading.value = false
    }
  }
  
  async function fetchTodaySessions() {
    loading.value = true
    error.value = null
    try {
      const response = await therapyService.getTodaySessions()
      todaySessions.value = response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal mengambil sesi hari ini'
    } finally {
      loading.value = false
    }
  }
  
  async function createTherapy(therapyData) {
    loading.value = true
    error.value = null
    try {
      const response = await therapyService.createTherapy(therapyData)
      await fetchTherapies()
      useAnalyticsStore().fetchAnalytics()
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal membuat program terapi'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  async function updateTherapy(id, therapyData) {
    loading.value = true
    error.value = null
    try {
      const response = await therapyService.updateTherapy(id, therapyData)
      await fetchTherapies()
      useAnalyticsStore().fetchAnalytics()
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal update terapi'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  async function deleteTherapy(id) {
    loading.value = true
    error.value = null
    try {
      await therapyService.deleteTherapy(id)
      await fetchTherapies()
      useAnalyticsStore().fetchAnalytics()
      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal hapus terapi'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  async function getTherapyById(id) {
    loading.value = true
    error.value = null
    try {
      const response = await therapyService.getTherapyById(id)
      currentTherapy.value = response.data
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal mengambil detail terapi'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  return {
    therapies,
    currentTherapy,
    todaySessions,
    loading,
    error,
    pagination,
    fetchTherapies,
    fetchTodaySessions,
    createTherapy,
    updateTherapy,
    deleteTherapy,
    getTherapyById
  }
})
