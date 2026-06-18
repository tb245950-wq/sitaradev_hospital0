/**
 * Format tanggal ke standar Indonesia: DD-MM-YYYY
 * @param {Date|string} date - Date object atau string
 * @param {string} format - Format output (default: 'DD-MM-YYYY')
 * @returns {string} Formatted date
 */
export function formatDate(date, format = 'DD-MM-YYYY') {
  if (!date) return '-'
  
  const d = new Date(date)
  
  if (isNaN(d.getTime())) return '-'
  
  const day = String(d.getDate()).padStart(2, '0')
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const year = d.getFullYear()
  const hours = String(d.getHours()).padStart(2, '0')
  const minutes = String(d.getMinutes()).padStart(2, '0')
  
  switch (format) {
    case 'DD-MM-YYYY':
      return `${day}-${month}-${year}`
    case 'DD/MM/YYYY':
      return `${day}/${month}/${year}`
    case 'YYYY-MM-DD':
      return `${year}-${month}-${day}`
    case 'DD-MM-YYYY HH:mm':
      return `${day}-${month}-${year} ${hours}:${minutes}`
    case 'relative':
      return getRelativeTime(d)
    default:
      return `${day}-${month}-${year}`
  }
}

/**
 * Get relative time (e.g., "2 jam yang lalu")
 */
export function getRelativeTime(date) {
  const now = new Date()
  const diff = now - date
  const seconds = Math.floor(diff / 1000)
  const minutes = Math.floor(seconds / 60)
  const hours = Math.floor(minutes / 60)
  const days = Math.floor(hours / 24)
  
  if (days > 0) return `${days} hari yang lalu`
  if (hours > 0) return `${hours} jam yang lalu`
  if (minutes > 0) return `${minutes} menit yang lalu`
  return 'Baru saja'
}

/**
 * Format untuk input date (YYYY-MM-DD)
 */
export function formatDateForInput(date) {
  if (!date) return ''
  const d = new Date(date)
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

/**
 * Get today's date in YYYY-MM-DD format
 */
export function getToday() {
  const now = new Date()
  const year = now.getFullYear()
  const month = String(now.getMonth() + 1).padStart(2, '0')
  const day = String(now.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

/**
 * Get current month in YYYY-MM format
 */
export function getCurrentMonth() {
  const now = new Date()
  const year = now.getFullYear()
  const month = String(now.getMonth() + 1).padStart(2, '0')
  return `${year}-${month}`
}
