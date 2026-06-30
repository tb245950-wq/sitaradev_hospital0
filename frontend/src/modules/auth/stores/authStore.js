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
  const isSuperAdmin = computed(() => userRole.value === 'super_admin')
  const isActive = computed(() => userStatus.value === 'active')

  // Menu items berdasarkan role
  const menuItems = computed(() => {
    // Super admin punya menu sendiri, tidak pakai menuItems ini
    if (userRole.value === 'super_admin') {
      return [
        { name: 'Dashboard', path: '/super-admin/dashboard', icon: '📊' },
        { name: 'Manajemen User', path: '/super-admin/users', icon: '👤' },
        { name: 'Manajemen Poli', path: '/super-admin/polis', icon: '🏥' },
        { name: 'Log Aktivitas', path: '/super-admin/audit-logs', icon: '📋' },
        { name: 'Backup', path: '/super-admin/backup', icon: '💾' },
        { name: 'Pengaturan', path: '/super-admin/settings', icon: '⚙️' },
      ]
    }

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
      // PANGGIL SERVICE
      const result = await authService.login(email, password)
      
      
      if (result.success) {
        // UPDATE STATE
        user.value = result.data.user
        token.value = result.data.token
        
        
        return {
          success: true,
          data: result.data
        }
      } else {
        console.error('📦 Store received failure from service:', result.error)
        error.value = result.error || 'Login gagal'
        return {
          success: false,
          error: error.value
        }
      }
    } catch (err) {
      console.error('📦 Store catch block error:', err)
      error.value = err.message || 'Terjadi kesalahan sistem'
      return {
        success: false,
        error: error.value
      }
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
      await authService.logout();
    } catch (err) {
      console.error('AuthStore: Service logout failed', err);
    } finally {
      // Clear local state
      user.value = null;
      token.value = null;
      
      // Clear storage just in case
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      
      
      // Optional: Force reload to clear all in-memory state
      // window.location.href = '/login'; 
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
    isSuperAdmin,
    isActive,
    menuItems,
    login,
    register,
    logout,
    canAccess
  }
})