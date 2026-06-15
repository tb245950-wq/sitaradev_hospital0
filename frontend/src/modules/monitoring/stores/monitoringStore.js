import { defineStore } from 'pinia'
import { ref } from 'vue'
import { monitoringService } from '../services/monitoringService'
export const useMonitoringStore = defineStore('monitoring', () => {
  const monitorings = ref([]); const loading = ref(false); const error = ref(null)
  async function fetchMonitorings(params) {
    loading.value = true; try { const res = await monitoringService.getMonitorings(params); monitorings.value = res.data.data }
    catch (err) { error.value = err.message } finally { loading.value = false }
  }
  return { monitorings, loading, error, fetchMonitorings }
})
