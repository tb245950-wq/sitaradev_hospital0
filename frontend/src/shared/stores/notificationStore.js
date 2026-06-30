import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useNotificationStore = defineStore('notification', () => {
  const alerts = ref([])
  let alertIdCounter = 0

  // Durasi default untuk setiap tipe alert
  const defaultDurations = {
    success: 3000,
    error: 5000,
    warning: 4000,
    info: 3000
  }

  /**
   * Tambahkan alert dengan opsi advanced
   * @param {Object} config - { message, type, title, duration, action }
   */
  function addAlert(config) {
    const {
      message,
      type = 'info',
      title = null,
      duration = defaultDurations[type] || 3000,
      action = null
    } = config

    const id = ++alertIdCounter
    alerts.value.push({
      id,
      message,
      type,
      title,
      duration,
      action
    })

    if (duration > 0) {
      setTimeout(() => {
        removeAlert(id)
      }, duration)
    }

    return id
  }

  function removeAlert(id) {
    alerts.value = alerts.value.filter(a => a.id !== id)
  }

  function clearAll() {
    alerts.value = []
  }

  // Shortcut methods
  function success(message, title = null, action = null) {
    return addAlert({ message, type: 'success', title, action })
  }

  function error(message, title = null, action = null) {
    return addAlert({ message, type: 'error', title, action, duration: 5000 })
  }

  function warning(message, title = null, action = null) {
    return addAlert({ message, type: 'warning', title, action, duration: 4000 })
  }

  function info(message, title = null, action = null) {
    return addAlert({ message, type: 'info', title, action })
  }

  // Legacy support
  const notifications = alerts
  function notify(message, type = 'info') {
    return addAlert({ message, type })
  }

  function removeNotification(id) {
    removeAlert(id)
  }

  return {
    alerts,
    notifications,
    addAlert,
    removeAlert,
    clearAll,
    success,
    error,
    warning,
    info,
    notify,
    removeNotification
  }
})