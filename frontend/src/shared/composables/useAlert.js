import { useNotificationStore } from '../stores/notificationStore'

/**
 * Composable untuk menampilkan alerts dengan cara yang lebih mudah
 * Contoh penggunaan:
 * const { alert, success, error, warning, info } = useAlert()
 * 
 * success('Data berhasil disimpan')
 * error('Terjadi kesalahan', 'Error', { label: 'Retry', callback: () => {...} })
 */
export function useAlert() {
  const store = useNotificationStore()

  const alert = (config) => store.addAlert(config)
  
  const success = (message, title = null, action = null) => {
    return store.success(message, title, action)
  }
  
  const error = (message, title = null, action = null) => {
    return store.error(message, title, action)
  }
  
  const warning = (message, title = null, action = null) => {
    return store.warning(message, title, action)
  }
  
  const info = (message, title = null, action = null) => {
    return store.info(message, title, action)
  }

  const clearAll = () => store.clearAll()

  // Helper untuk API responses
  const showApiResponse = (response, defaultMessage = 'Operasi berhasil') => {
    const message = response?.message || response?.msg || defaultMessage
    const type = response?.success ? 'success' : 'error'
    return alert({ message, type })
  }

  // Helper untuk error dengan opsi retry
  const showError = (message, onRetry = null, title = 'Terjadi Kesalahan') => {
    return store.error(message, title, onRetry ? {
      label: 'Coba Lagi',
      callback: onRetry
    } : null)
  }

  return {
    alert,
    success,
    error,
    warning,
    info,
    clearAll,
    showApiResponse,
    showError
  }
}
