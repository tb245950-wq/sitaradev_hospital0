import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useNotificationStore = defineStore('notification', () => {
  const notifications = ref([])

  function notify(message, type = 'info') {
    const id = Date.now()
    notifications.value.push({ id, message, type })
    setTimeout(() => {
      removeNotification(id)
    }, 3000)
  }

  function removeNotification(id) {
    notifications.value = notifications.value.filter(n => n.id !== id)
  }

  return { notifications, notify, removeNotification }
})
