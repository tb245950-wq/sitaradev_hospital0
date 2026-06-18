import { createRouter, createWebHistory } from 'vue-router'

// ===== VIEWS IMPORTS =====

// Auth & Landing
import LandingPage from '../../modules/auth/views/LandingPage.vue'
import LoginPage from '../../modules/auth/views/LoginPage.vue'

// Dashboard
import DashboardView from '../../modules/dashboard/views/DashboardView.vue'

// Patients
import PatientListView from '../../modules/patients/views/PatientListView.vue'
import PatientCreateView from '../../modules/patients/views/PatientCreateView.vue'
import PatientDetailView from '../../modules/patients/views/PatientDetailView.vue'
import PatientEditView from '../../modules/patients/views/PatientEditView.vue'

// Queue
import QueueView from '../../modules/queue/views/QueueView.vue'

// Assessment
import AssessmentListView from '../../modules/assessment/views/AssessmentListView.vue'
import AssessmentCreateView from '../../modules/assessment/views/AssessmentCreateView.vue'

// Therapy & Monitoring
import TherapyListView from '../../modules/therapy/views/TherapyListView.vue'
import MonitoringView from '../../modules/monitoring/views/MonitoringView.vue'

// Reports
import ReportsView from '../../modules/reports/views/ReportsView.vue'

// Admin
import UserManagementView from '../../modules/users/views/UserManagementView.vue'
import SettingsView from '../../modules/settings/views/SettingsView.vue'

// Patient Portal
import PatientLoginView from '../../modules/patient-portal/views/PatientLoginView.vue'
import PatientRegisterView from '../../modules/patient-portal/views/PatientRegisterView.vue'
import PatientDashboardView from '../../modules/patient-portal/views/PatientDashboardView.vue'

// ===== ROUTES DEFINITION =====

const routes = [
  // Public
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

  // Staff Dashboard
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: DashboardView,
    meta: { requiresAuth: true, roles: ['admin', 'dokter', 'terapis'], portal: 'staff' }
  },

  // Patients Management
  {
    path: '/patients',
    name: 'Patients',
    component: PatientListView,
    meta: { requiresAuth: true, roles: ['admin', 'dokter', 'terapis'], portal: 'staff' }
  },
  {
    path: '/patients/create',
    name: 'PatientCreate',
    component: PatientCreateView,
    meta: { requiresAuth: true, roles: ['admin', 'dokter'], portal: 'staff' }
  },
  {
    path: '/patients/:id',
    name: 'PatientDetail',
    component: PatientDetailView,
    meta: { requiresAuth: true, roles: ['admin', 'dokter', 'terapis'], portal: 'staff' }
  },
  {
    path: '/patients/:id/edit',
    name: 'PatientEdit',
    component: PatientEditView,
    meta: { requiresAuth: true, roles: ['admin', 'dokter'], portal: 'staff' }
  },

  // Queue Management
  {
    path: '/queues',
    name: 'Queues',
    component: QueueView,
    meta: { requiresAuth: true, roles: ['admin', 'dokter'], portal: 'staff' }
  },

  // Medical Assessment
  {
    path: '/assessments',
    name: 'Assessments',
    component: AssessmentListView,
    meta: { requiresAuth: true, roles: ['admin', 'dokter'], portal: 'staff' }
  },
  {
    path: '/assessments/create',
    name: 'AssessmentCreate',
    component: AssessmentCreateView,
    meta: { requiresAuth: true, roles: ['admin', 'dokter'], portal: 'staff' }
  },

  // Therapy Program
  {
    path: '/therapies',
    name: 'Therapies',
    component: TherapyListView,
    meta: { requiresAuth: true, roles: ['admin', 'dokter', 'terapis'], portal: 'staff' }
  },

  // Monitoring Progress
  {
    path: '/monitoring',
    name: 'Monitoring',
    component: MonitoringView,
    meta: { requiresAuth: true, roles: ['admin', 'dokter', 'terapis'], portal: 'staff' }
  },

  // Reports
  {
    path: '/reports',
    name: 'Reports',
    component: ReportsView,
    meta: { requiresAuth: true, roles: ['admin', 'dokter'], portal: 'staff' }
  },

  // Administration
  {
    path: '/users',
    name: 'UserManagement',
    component: UserManagementView,
    meta: { requiresAuth: true, roles: ['admin'], portal: 'staff' }
  },
  {
    path: '/settings',
    name: 'Settings',
    component: SettingsView,
    meta: { requiresAuth: true, roles: ['admin'], portal: 'staff' }
  },

  // Patient Portal
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

  // Unauthorized
  {
    path: '/unauthorized',
    name: 'Unauthorized',
    component: { 
      template: `
        <div style="padding: 4rem; text-align: center;">
          <h1 style="color: #ef4444;">🚫 Akses Ditolak</h1>
          <p style="margin: 1rem 0;">Anda tidak memiliki izin untuk mengakses halaman ini.</p>
          <a href="/dashboard" style="color: #3b82f6; text-decoration: none;">← Kembali ke Dashboard</a>
        </div>
      `
    }
  },

  // 404 Not Found
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: { 
      template: `
        <div style="padding: 4rem; text-align: center;">
          <h1 style="color: #1e40af;">404 - Halaman Tidak Ditemukan</h1>
          <p style="margin: 1rem 0;">Halaman yang Anda cari tidak tersedia.</p>
          <a href="/dashboard" style="color: #3b82f6; text-decoration: none;">← Kembali ke Dashboard</a>
        </div>
      `
    }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) return savedPosition
    return { top: 0 }
  }
})

// ===== NAVIGATION GUARD =====

router.beforeEach((to, from, next) => {
  console.log('🔍 Navigation:', from.path, '→', to.path)
  
  let token = null
  let user = null
  
  try {
    // Check both staff and patient storage
    token = localStorage.getItem('token') || localStorage.getItem('patient_token')
    const userStr = localStorage.getItem('user') || localStorage.getItem('patient_user')
    
    if (userStr && userStr !== 'undefined' && userStr !== 'null') {
      user = JSON.parse(userStr)
    }
  } catch (e) {
    console.error('❌ Router: Error reading localStorage:', e)
  }
  
  // 1. Check Authentication
  if (to.meta.requiresAuth && !token) {
    console.warn('⚠️ Router: Requires Auth but no token, redirecting to login')
    return next(to.meta.portal === 'patient' ? '/pasien/login' : '/login')
  }
  
  // 2. Check Guest Access (Already Logged In)
  if (to.meta.guest && token) {
    console.log('ℹ️ Router: Already logged in, redirecting to dashboard')
    return next(user?.role === 'pasien' ? '/pasien/dashboard' : '/dashboard')
  }
  
  // 3. Check Role-Based Access Control
  if (to.meta.roles && user) {
    if (!to.meta.roles.includes(user.role)) {
      console.error('🚫 Router: Access denied for role:', user.role)
      return next('/unauthorized')
    }
  }
  
  // 4. Final Fallback
  console.log('✅ Router: Navigation allowed')
  next()
})

// Attach router to window for diagnostic access
if (typeof window !== 'undefined') {
  window.$router = router
}

export default router
