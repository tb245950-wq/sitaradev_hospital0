import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authService } from '../services/authService'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(authService.getStoredUser())
  const token = ref(authService.getToken())
  const loading = ref(false)
  const error = ref(null)

  const isAuthenticated = computed(() => !!token.value)
  const userRole = computed(() => user.value?.role || null)
  const userStatus = computed(() => user.value?.status || null)
  
  // Permission checks
  const isAdmin = computed(() => userRole.value === 'admin')
  const isDokter = computed(() => userRole.value === 'dokter')
  const isTerapis = computed(() => userRole.value === 'terapis')
  const isActive = computed(() => userStatus.value === 'active')

  // Menu items berdasarkan role
  const menuItems = computed(() => {
    const allMenus = [
      { 
        name: 'Dashboard', 
        path: '/dashboard', 
        icon: '📊', 
        roles: ['admin', 'dokter', 'terapis'] 
      },
      { 
        name: 'Data Pasien', 
        path: '/patients', 
        icon: '👥', 
        roles: ['admin', 'dokter', 'terapis'] 
      },
      { 
        name: 'Antrian', 
        path: '/queue', 
        icon: '🎫', 
        roles: ['admin', 'dokter'] 
      },
      { 
        name: 'Assessment', 
        path: '/assessment', 
        icon: '📋', 
        roles: ['admin', 'dokter'] 
      },
      { 
        name: 'Terapi', 
        path: '/therapy', 
        icon: '🧠', 
        roles: ['admin', 'dokter', 'terapis'] 
      },
      { 
        name: 'Monitoring', 
        path: '/monitoring', 
        icon: '📈', 
        roles: ['admin', 'dokter', 'terapis'] 
      },
      { 
        name: 'Laporan Medis', 
        path: '/reports', 
        icon: '', 
        roles: ['admin', 'dokter'] 
      },
      { 
        name: 'Manajemen User', 
        path: '/users', 
        icon: '👤', 
        roles: ['admin'] 
      },
      { 
        name: 'Pengaturan', 
        path: '/settings', 
        icon: '⚙️', 
        roles: ['admin'] 
      }
    ]

    return allMenus.filter(menu => menu.roles.includes(userRole.value))
  })

  async function login(email, password) {
    loading.value = true
    error.value = null
    try {
      const data = await authService.login(email, password)
      user.value = data.data.user
      token.value = data.data.token
      
      // Simpan ke localStorage
      localStorage.setItem('token', data.data.token)
      localStorage.setItem('user', JSON.stringify(data.data.user))
      
      return { success: true, role: data.data.user.role }
    } catch (err) {
      error.value = err.response?.data?.message || 'Login gagal'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function register(userData) {
    loading.value = true
    error.value = null
    try {
      const data = await authService.register(userData)
      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.message || 'Register gagal'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await authService.logout()
    } finally {
      user.value = null
      token.value = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    }
  }

  // Check permission untuk akses fitur
  function canAccess(feature) {
    const permissions = {
      'patients': ['admin', 'dokter', 'terapis'],
      'queue': ['admin', 'dokter'],
      'assessment': ['admin', 'dokter'],
      'therapy': ['admin', 'dokter', 'terapis'],
      'monitoring': ['admin', 'dokter', 'terapis'],
      'reports': ['admin', 'dokter'],
      'users': ['admin'],
      'settings': ['admin']
    }

    return permissions[feature]?.includes(userRole.value) || false
  }

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    userRole,
    userStatus,
    isAdmin,
    isDokter,
    isTerapis,
    isActive,
    menuItems,
    login,
    register,
    logout,
    canAccess
  }
})