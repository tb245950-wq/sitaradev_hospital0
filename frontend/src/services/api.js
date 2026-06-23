import axios from 'axios'

// Detect URL otomatis
const getBaseURL = () => {
  const hostname = window.location.hostname
  
  // Jika diakses via IP WiFi, gunakan IP tersebut untuk backend
  if (hostname === '172.16.20.218') {
    return 'http://172.16.20.218:8000/api'
  }
  
  // Default localhost
  return 'http://127.0.0.1:8000/api'
}

const api = axios.create({
  baseURL: getBaseURL(),
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  timeout: 30000
})

// Request interceptor
api.interceptors.request.use(
  config => {
    const token = localStorage.getItem('token') || localStorage.getItem('patient_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  error => Promise.reject(error)
)

// Response interceptor
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api