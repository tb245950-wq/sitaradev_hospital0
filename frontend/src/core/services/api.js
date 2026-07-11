import axios from 'axios'

// ✅ Gunakan env var VITE_API_BASE_URL yang di-set di dashboard Vercel/Railway
// Fallback ke localhost untuk development lokal
// v2 - force rebuild 2026-07-12
const getBaseURL = () => {
  // Production: pakai env var yang di-inject Vite saat build
  if (import.meta.env.VITE_API_BASE_URL) {
    return import.meta.env.VITE_API_BASE_URL
  }
  const hostname = window.location.hostname
  if (hostname === '172.16.20.218') return 'http://172.16.20.218:8000/api'
  return 'http://127.0.0.1:8000/api'
}

const api = axios.create({
  baseURL: getBaseURL(),
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  timeout: 10000
})


api.interceptors.request.use(
  config => {
    // Gunakan token yang sesuai dengan portal berdasarkan URL
    const isPatientRoute = config.url?.includes('/pasien/')
    const token = isPatientRoute
      ? localStorage.getItem('patient_token')
      : localStorage.getItem('token') || localStorage.getItem('patient_token')

    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }

    // Cache busting
    if (config.method === 'get') {
      config.params = { ...config.params, _t: Date.now() }
    }

    return config
  },
  error => Promise.reject(error)
)

// Response interceptor — handle 401 per portal
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      const isPatientRoute = error.config?.url?.includes('/pasien/')
      if (isPatientRoute) {
        localStorage.removeItem('patient_token')
        localStorage.removeItem('patient_user')
        localStorage.removeItem('patient')
        window.location.href = '/pasien/login'
      } else {
        localStorage.removeItem('token')
        localStorage.removeItem('user')
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

export default api
