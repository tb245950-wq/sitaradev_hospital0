import { defineStore } from 'pinia'
import { ref } from 'vue'
import { queueService } from '../services/queueService'
export const useQueueStore = defineStore('queue', () => {
  const queues = ref([]); const loading = ref(false); const error = ref(null)
  async function fetchQueues(params) {
    loading.value = true; try { const res = await queueService.getQueues(params); queues.value = res.data.data }
    catch (err) { error.value = err.message } finally { loading.value = false }
  }
  return { queues, loading, error, fetchQueues }
})
