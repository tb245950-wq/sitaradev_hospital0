import { defineStore } from 'pinia'
import { ref } from 'vue'
import { assessmentService } from '../services/assessmentService'
export const useAssessmentStore = defineStore('assessment', () => {
  const assessments = ref([]); const loading = ref(false); const error = ref(null)
  async function fetchAssessments(params) {
    loading.value = true; try { const res = await assessmentService.getAssessments(params); assessments.value = res.data.data }
    catch (err) { error.value = err.message } finally { loading.value = false }
  }
  return { assessments, loading, error, fetchAssessments }
})
