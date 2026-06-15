import { defineStore } from 'pinia'
import { ref } from 'vue'
import { assessmentService } from '../services/assessmentService'

export const useAssessmentStore = defineStore('assessment', () => {
  const assessments = ref([])
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 15
  })
  const loading = ref(false)
  const error = ref(null)

  /**
   * Fetch all assessments
   */
  async function fetchAssessments(params = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await assessmentService.getAssessments(params)
      if (response.success) {
        assessments.value = response.data.data
        pagination.value = {
          current_page: response.data.current_page,
          last_page: response.data.last_page,
          total: response.data.total,
          per_page: response.data.per_page
        }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal memuat data assessment'
      console.error('Fetch Assessments Error:', err)
    } finally {
      loading.value = false
    }
  }

  /**
   * Create assessment
   */
  async function createAssessment(data) {
    loading.value = true
    error.value = null
    try {
      const response = await assessmentService.createAssessment(data)
      if (response.success) {
        await fetchAssessments() // Refresh list
        return { success: true, message: response.message }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal membuat assessment'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  /**
   * Update assessment
   */
  async function updateAssessment(id, data) {
    loading.value = true
    error.value = null
    try {
      const response = await assessmentService.updateAssessment(id, data)
      if (response.success) {
        await fetchAssessments() // Refresh list
        return { success: true, message: response.message }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal memperbarui assessment'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  /**
   * Delete assessment
   */
  async function deleteAssessment(id) {
    loading.value = true
    error.value = null
    try {
      const response = await assessmentService.deleteAssessment(id)
      if (response.success) {
        await fetchAssessments() // Refresh list
        return { success: true, message: response.message }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal menghapus assessment'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  return {
    assessments,
    pagination,
    loading,
    error,
    fetchAssessments,
    createAssessment,
    updateAssessment,
    deleteAssessment
  }
})
