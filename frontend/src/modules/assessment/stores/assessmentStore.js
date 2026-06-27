import { defineStore } from 'pinia'
import { ref } from 'vue'
import { assessmentService } from '../services/assessmentService'
import { useAnalyticsStore } from '../../analytics/stores/analyticsStore'

export const useAssessmentStore = defineStore('assessment', () => {
  const assessments = ref([])
  const currentAssessment = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0
  })
  
  async function fetchAssessments(params = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await assessmentService.getAssessments(params)
      // Laravel Resource Collection structure: { success: true, data: [...], meta: { current_page, ... } }
      // Or if additional(['success' => true]) is used on collection: { data: [...], success: true, links: ..., meta: ... }
      assessments.value = response.data
      
      if (response.meta) {
        pagination.value = {
          current_page: response.meta.current_page,
          last_page: response.meta.last_page,
          per_page: response.meta.per_page,
          total: response.meta.total
        }
      } else if (response.current_page) {
        pagination.value = {
          current_page: response.current_page,
          last_page: response.last_page,
          per_page: response.per_page,
          total: response.total
        }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal mengambil data assessment'
    } finally {
      loading.value = false
    }
  }
  
  async function createAssessment(assessmentData) {
    loading.value = true
    error.value = null
    try {
      const response = await assessmentService.createAssessment(assessmentData)
      await fetchAssessments()
      useAnalyticsStore().fetchAnalytics()
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal membuat assessment'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  async function updateAssessment(id, assessmentData) {
    loading.value = true
    error.value = null
    try {
      const response = await assessmentService.updateAssessment(id, assessmentData)
      await fetchAssessments()
      useAnalyticsStore().fetchAnalytics()
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal update assessment'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  async function deleteAssessment(id) {
    loading.value = true
    error.value = null
    try {
      await assessmentService.deleteAssessment(id)
      await fetchAssessments()
      useAnalyticsStore().fetchAnalytics()
      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal hapus assessment'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  async function submitAssessment(id) {
    try {
      const response = await assessmentService.submitAssessment(id)
      await fetchAssessments()
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal submit assessment'
      return { success: false, error: error.value }
    }
  }
  
  async function getAssessmentById(id) {
    loading.value = true
    error.value = null
    try {
      const response = await assessmentService.getAssessmentById(id)
      currentAssessment.value = response.data
      return { success: true, data: response.data }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal mengambil detail assessment'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }
  
  return {
    assessments,
    currentAssessment,
    loading,
    error,
    pagination,
    fetchAssessments,
    createAssessment,
    updateAssessment,
    deleteAssessment,
    submitAssessment,
    getAssessmentById
  }
})
