import axios from 'axios'

// ============================================
// DETECT BASE URL OTOMATIS
// ============================================
const getBaseURL = () => {
  const hostname = window.location.hostname
  const port = window.location.port
  
  console.log('🔍 Detecting base URL...')
  console.log('   Hostname:', hostname)
  console.log('   Port:', port)
  
  // Jika diakses via IP WiFi (172.16.20.218)
  if (hostname === '172.16.20.218') {
    const url = 'http://172.16.20.218:8000/api'
    console.log('   Using WiFi IP:', url)
    return url
  }
  
  // Default localhost untuk development
  const url = 'http://127.0.0.1:8000/api'
  console.log('   Using localhost:', url)
  return url
}

// ============================================
// CREATE AXIOS INSTANCE
// ============================================
const api = axios.create({
  baseURL: getBaseURL(),
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  timeout: 30000, // 30 detik
  withCredentials: false
})

console.log('✅ API instance created with baseURL:', api.defaults.baseURL)

// ============================================
// REQUEST INTERCEPTOR (SATU SAJA!)
// ============================================
api.interceptors.request.use(
  config => {
    // Prioritas: patient_token untuk portal pasien, token untuk staff
    const isPatientRequest = config.url?.includes('/pasien/')
    let token
    
    if (isPatientRequest) {
      // Untuk request pasien, prioritaskan patient_token
      token = localStorage.getItem('patient_token') || localStorage.getItem('token')
    } else {
      // Untuk request staff, prioritaskan token biasa
      token = localStorage.getItem('token') || localStorage.getItem('patient_token')
    }
    
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
      console.log(`📤 Request: ${config.method?.toUpperCase()} ${config.url} [Auth: ${isPatientRequest ? 'Patient' : 'Staff'}]`)
    } else {
      console.log(`📤 Request: ${config.method?.toUpperCase()} ${config.url} [No Auth]`)
    }
    
    return config
  },
  error => {
    console.error('❌ Request interceptor error:', error)
    return Promise.reject(error)
  }
)

// ============================================
// RESPONSE INTERCEPTOR (UNTUK HANDLE ERROR)
// ============================================
api.interceptors.response.use(
  response => {
    console.log(`📥 Response: ${response.status} ${response.config.url}`, {
      success: response.data?.success,
      message: response.data?.message
    })
    return response
  },
  error => {
    // Log error detail
    console.error('❌ Response error:', {
      url: error.config?.url,
      method: error.config?.method,
      status: error.response?.status,
      message: error.response?.data?.message || error.message,
      data: error.response?.data
    })
    
    // Handle specific HTTP errors
    if (error.response) {
      switch (error.response.status) {
        case 401: // Unauthorized
          console.warn('⚠️ Unauthorized! Clearing tokens...')
          const currentPath = window.location.pathname
          
          // Jangan redirect jika sudah di halaman login
          if (currentPath !== '/login' && currentPath !== '/pasien/login') {
            // Cek jenis token yang ada
            if (localStorage.getItem('patient_token')) {
              localStorage.removeItem('patient_token')
              localStorage.removeItem('user')
              localStorage.removeItem('patient')
              window.location.href = '/pasien/login'
            } else if (localStorage.getItem('token')) {
              localStorage.removeItem('token')
              localStorage.removeItem('user')
              window.location.href = '/login'
            }
          }
          break
          
        case 403: // Forbidden
          console.error('🚫 Access forbidden:', error.response.data?.message)
          break
          
        case 404: // Not Found
          console.error('🔍 Resource not found:', error.config?.url)
          break
          
        case 422: // Validation Error
          console.error('❌ Validation error:', error.response.data?.errors)
          break
          
        case 500: // Server Error
          console.error('💥 Server error:', error.response.data?.message)
          break
      }
    } else if (error.request) {
      // Request terkirim tapi tidak ada response
      console.error('🌐 Network error - no response from server')
      console.error('   Cek apakah backend Laravel running di port 8000')
    } else {
      // Error saat setup request
      console.error('⚙️ Request setup error:', error.message)
    }
    
    return Promise.reject(error)
  }
)

// ============================================
// EXPORT
// ============================================
export default api