import { defineStore } from 'pinia'
import { ref } from 'vue'
import { therapyService } from '../services/therapyService'
export const useTherapyStore = defineStore('therapy', () => {
  const therapies = ref([]); const loading = ref(false); const error = ref(null)
  async function fetchTherapies(params) {
    loading.value = true; try { const res = await therapyService.getTherapies(params); therapies.value = res.data.data }
    catch (err) { error.value = err.message } finally { loading.value = false }
  }
  return { therapies, loading, error, fetchTherapies }
})
