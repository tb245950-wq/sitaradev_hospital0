import { defineStore } from 'pinia'
import { ref } from 'vue'
import { reportService } from '../services/reportService'
export const useReportStore = defineStore('reports', () => {
  const reports = ref([]); const loading = ref(false); const error = ref(null)
  async function fetchReports(params) {
    loading.value = true; try { const res = await reportService.getReports(params); reports.value = res.data.data }
    catch (err) { error.value = err.message } finally { loading.value = false }
  }
  return { reports, loading, error, fetchReports }
})
