import { createRouter, createWebHistory } from 'vue-router'
import { authService } from '../../modules/auth/services/authService'
import { patientService } from '../../modules/patient-portal/services/patientService'

// Import routes dari setiap modul
import { authRoutes } from '../../modules/auth/router/authRoutes'
import { dashboardRoutes } from '../../modules/dashboard/router/dashboardRoutes'
import { patientRoutes } from '../../modules/patients/router/patientRoutes'
import { patientPortalRoutes } from '../../modules/patient-portal/router/patientPortalRoutes'

// Import views untuk modul baru
import AssessmentListView from '../../modules/assessment/views/AssessmentListView.vue'
import QueueView from '../../modules/queue/views/QueueView.vue'
import TherapyListView from '../../modules/therapy/views/TherapyListView.vue'

const routes = [
  ...authRoutes,
  ...dashboardRoutes,
  ...patientRoutes,
  ...patientPortalRoutes,
  
  // User Management Route (Admin Only)
  {
    path: '/users',
    name: 'UserManagement',
    component: () => import('../../modules/users/views/UserManagementView.vue'),
    meta: { 
      requiresAuth: true, 
      roles: ['admin'] 
    }
  },
  
  // Settings Route (Admin Only)
  {
    path: '/settings',
    name: 'Settings',
    component: () => import('../../modules/settings/views/SettingsView.vue'),
    meta: { 
      requiresAuth: true, 
      roles: ['admin'] 
    }
  },
  
  // Queue Routes
  {
    path: '/queues',
    name: 'Queues',
    component: () => import('../../modules/queue/views/QueueView.vue'),
    meta: { requiresAuth: true, roles: ['admin', 'dokter'] }
  },
  {
    path: '/queues/create',
    name: 'QueueCreate',
    component: () => import('../../modules/queue/views/QueueCreateView.vue'),
    meta: { requiresAuth: true, roles: ['admin', 'dokter'] }
  },
  {
    path: '/queue',
    name: 'Queue',
    redirect: '/queues'
  },
  
  // Assessment Routes
  {
    path: '/assessments',
    name: 'Assessments',
    component: AssessmentListView,
    meta: { requiresAuth: true, roles: ['admin', 'dokter'] }
  },
  {
    path: '/assessments/create',
    name: 'AssessmentCreate',
    component: () => import('../../modules/assessment/views/AssessmentCreateView.vue'),
    meta: { requiresAuth: true, roles: ['admin', 'dokter'] }
  },
  {
    path: '/assessment',
    name: 'Assessment',
    redirect: '/assessments'
  },
  
  // Therapy Routes
  {
    path: '/therapies',
    name: 'Therapies',
    component: TherapyListView,
    meta: { requiresAuth: true, roles: ['admin', 'dokter', 'terapis'] }
  },
  {
    path: '/therapies/create',
    name: 'TherapyCreate',
    component: () => import('../../modules/therapy/views/TherapyCreateView.vue'),
    meta: { requiresAuth: true, roles: ['admin', 'dokter'] }
  },
  {
    path: '/therapy',
    name: 'Therapy',
    redirect: '/therapies'
  },
  
  // Monitoring Route (Semua Role Medis)
  {
    path: '/monitoring',
    name: 'Monitoring',
    component: () => import('../../modules/monitoring/views/MonitoringView.vue'),
    meta: { 
      requiresAuth: true, 
      roles: ['admin', 'dokter', 'terapis'] 
    }
  },
  
  // Reports Route (Admin & Dokter)
  {
    path: '/reports',
    name: 'Reports',
    component: () => import('../../modules/reports/views/ReportsView.vue'),
    meta: { 
      requiresAuth: true, 
      roles: ['admin', 'dokter'] 
    }
  },
  {
    path: '/activities',
    name: 'ActivityLog',
    component: () => import('../../modules/dashboard/views/ActivityLogView.vue'),
    meta: { requiresAuth: true }
  },
  
  // Unauthorized Page
  {
    path: '/unauthorized',
    name: 'unauthorized',
    component: () => import('../../shared/components/common/Unauthorized.vue')
  },
  
  // 404 Not Found - HARUS PALING BAWAH
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('../../shared/components/common/NotFound.vue')
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  }
})

// Navigation guard dengan RBAC
router.beforeEach((to, from, next) => {
  const staffAuth = authService.isAuthenticated()
  const patientAuth = patientService.isAuthenticated()
  const isAuthenticated = staffAuth || patientAuth

  const staffUser = authService.getStoredUser()
  const patientUser = patientService.getStoredUser()
  const user = staffUser || patientUser
  const userRole = user?.role

  // Jika route butuh auth tapi belum login
  if (to.meta.requiresAuth && !isAuthenticated) {
    if (to.path.startsWith('/pasien/')) {
      next('/pasien/login')
    } else {
      next('/login')
    }
    return
  }

  // Jika guest page tapi sudah login
  if (to.meta.guest && isAuthenticated) {
    if (userRole === 'pasien') {
      next('/pasien/dashboard')
    } else {
      next('/dashboard')
    }
    return
  }

  // Check role access
  if (to.meta.roles && user) {
    if (!to.meta.roles.includes(userRole)) {
      if (userRole === 'pasien') {
        if (!to.path.startsWith('/pasien/')) {
          next('/pasien/dashboard')
          return
        }
      } else {
        if (to.path.startsWith('/pasien/')) {
          next('/unauthorized')
          return
        }
      }
      next('/unauthorized')
      return
    }
  }

  // Prevent staff from accessing patient portal and vice versa
  if (to.meta.portal === 'patient' && staffAuth && !patientAuth) {
    next('/dashboard')
    return
  }

  if (to.meta.portal === 'staff' && patientAuth && !staffAuth) {
    next('/pasien/dashboard')
    return
  }

  next()
})

export default router
