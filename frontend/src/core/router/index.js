import { createRouter, createWebHistory } from 'vue-router'

// Import langsung semua component
import LandingPage from '../../modules/auth/views/LandingPage.vue'
import LoginPage from '../../modules/auth/views/LoginPage.vue'
import PatientLoginView from '../../modules/patient-portal/views/PatientLoginView.vue'
import PatientRegisterView from '../../modules/patient-portal/views/PatientRegisterView.vue'
import PatientDashboardView from '../../modules/patient-portal/views/PatientDashboardView.vue'
import DashboardView from '../../modules/dashboard/views/DashboardView.vue'

const routes = [
  {
    path: '/',
    name: 'Landing',
    component: LandingPage,
    meta: { requiresAuth: false, guest: true }
  },
  {
    path: '/login',
    name: 'Login',
    component: LoginPage,
    meta: { requiresAuth: false, guest: true }
  },
  {
    path: '/pasien/login',
    name: 'PatientLogin',
    component: PatientLoginView,
    meta: { requiresAuth: false, guest: true, portal: 'patient' }
  },
  {
    path: '/pasien/register',
    name: 'PatientRegister',
    component: PatientRegisterView,
    meta: { requiresAuth: false, guest: true, portal: 'patient' }
  },
  {
    path: '/pasien/dashboard',
    name: 'PatientDashboard',
    component: PatientDashboardView,
    meta: { requiresAuth: true, roles: ['pasien'], portal: 'patient' }
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: DashboardView,
    meta: { requiresAuth: true, roles: ['admin', 'dokter', 'terapis'], portal: 'staff' }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: { 
      template: `
        <div style="padding: 4rem; text-align: center;">
          <h1 style="color: #1e40af;">404 - Halaman Tidak Ditemukan</h1>
          <p style="margin: 1rem 0;">Halaman yang Anda cari tidak ada.</p>
          <a href="/" style="color: #3b82f6; text-decoration: none;">← Kembali ke Beranda</a>
        </div>
      `
    }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  console.log('🔍 Navigasi:', from.path, '→', to.path)
  
  let token = null
  let user = null
  
  try {
    token = localStorage.getItem('token') || localStorage.getItem('patient_token')
    const userStr = localStorage.getItem('user') || localStorage.getItem('patient_user')
    if (userStr && userStr !== 'undefined' && userStr !== 'null') {
      user = JSON.parse(userStr)
    }
  } catch (e) {
    console.warn('Error reading localStorage:', e)
    localStorage.clear()
  }
  
  if (to.meta.requiresAuth && !token) {
    return next(to.meta.portal === 'patient' ? '/pasien/login' : '/login')
  }
  
  if (to.meta.guest && token) {
    return next(user?.role === 'pasien' ? '/pasien/dashboard' : '/dashboard')
  }
  
  if (to.meta.roles && user && !to.meta.roles.includes(user.role)) {
    return next('/')
  }
  
  next()
})

export default router
