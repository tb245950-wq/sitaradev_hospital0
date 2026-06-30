import { createRouter, createWebHistory } from 'vue-router'
import { authService } from '@/services/authService'

// Import views secara eksplisit
import LandingPage from '@/modules/auth/views/LandingPage.vue'
import LoginPage from '@/modules/auth/views/LoginPage.vue'
import DashboardView from '@/modules/dashboard/views/DashboardView.vue'
import PatientListView from '@/modules/patients/views/PatientListView.vue'
import QueueView from '@/modules/queue/views/QueueView.vue'
import AssessmentListView from '@/modules/assessment/views/AssessmentListView.vue'
import TherapyListView from '@/modules/therapy/views/TherapyListView.vue'
import MonitoringView from '@/modules/monitoring/views/MonitoringView.vue'
import ReportsView from '@/modules/reports/views/ReportsView.vue'
import UserManagementView from '@/modules/users/views/UserManagementView.vue'
import SettingsView from '@/modules/settings/views/SettingsView.vue'
import SuperAdminLayout from '@/modules/super-admin/views/SuperAdminLayout.vue'
import SuperAdminUserManagementView from '@/modules/super-admin/views/UserManagementView.vue'
import SuperAdminPoliManagementView from '@/modules/super-admin/views/PoliManagementView.vue'
import SuperAdminAuditLogsView from '@/modules/super-admin/views/AuditLogsView.vue'
import SuperAdminBackupView from '@/modules/super-admin/views/BackupView.vue'
import SuperAdminSettingsView from '@/modules/super-admin/views/SettingsView.vue'
import DEBUG_SuperAdminTest from '@/modules/super-admin/views/DEBUG_SuperAdminTest.vue'
import TestSimple from '@/modules/super-admin/views/TestSimple.vue'
import PatientLoginView from '@/modules/patient-portal/views/PatientLoginView.vue'
import PatientRegisterView from '@/modules/patient-portal/views/PatientRegisterView.vue'
import PatientDashboardView from '@/modules/patient-portal/views/PatientDashboardView.vue'

const routes = [
  { path: '/', name: 'Landing', component: LandingPage, meta: { guest: true } },
  { path: '/login', name: 'Login', component: LoginPage, meta: { guest: true } },

  // ── PATIENT PORTAL ──
  { path: '/pasien/login',    name: 'PatientLogin',    component: PatientLoginView,    meta: { guest: true, portal: 'patient' } },
  { path: '/pasien/register', name: 'PatientRegister', component: PatientRegisterView, meta: { guest: true, portal: 'patient' } },
  { path: '/pasien/dashboard', name: 'PatientDashboard', component: PatientDashboardView, meta: { requiresAuth: true, portal: 'patient' } },
  { path: '/pasien/antrian',  name: 'PatientAntrian',  component: () => import('@/modules/patient-portal/views/PatientBookingView.vue'),  meta: { requiresAuth: true, portal: 'patient' } },
  { path: '/pasien/jadwal',   name: 'PatientJadwal',   component: () => import('@/modules/patient-portal/views/PatientScheduleView.vue'), meta: { requiresAuth: true, portal: 'patient' } },
  { path: '/pasien/riwayat',  name: 'PatientRiwayat',  component: () => import('@/modules/patient-portal/views/PatientHistoryView.vue'),  meta: { requiresAuth: true, portal: 'patient' } },
  { path: '/pasien/profil',   name: 'PatientProfil',   component: () => import('@/modules/patient-portal/views/PatientProfileView.vue'),  meta: { requiresAuth: true, portal: 'patient' } },

  // ── STAFF PORTAL ──
  { path: '/dashboard',    name: 'Dashboard',      component: DashboardView,      meta: { requiresAuth: true, portal: 'staff' } },
  { path: '/patients',     name: 'Patients',       component: PatientListView,    meta: { requiresAuth: true, portal: 'staff' } },
  { path: '/waiting-list', name: 'WaitingList',    component: QueueView,          meta: { requiresAuth: true, portal: 'staff' } },
  { path: '/queues',       name: 'Queues',         component: QueueView,          meta: { requiresAuth: true, portal: 'staff' } },
  { path: '/assessments',  name: 'Assessments',    component: AssessmentListView, meta: { requiresAuth: true, portal: 'staff' } },
  { path: '/therapies',    name: 'Therapies',      component: TherapyListView,    meta: { requiresAuth: true, portal: 'staff' } },
  { path: '/monitoring',   name: 'Monitoring',     component: MonitoringView,     meta: { requiresAuth: true, portal: 'staff' } },
  { path: '/reports',      name: 'Reports',        component: ReportsView,        meta: { requiresAuth: true, portal: 'staff' } },
  { path: '/users',        name: 'UserManagement', component: UserManagementView, meta: { requiresAuth: true, portal: 'staff', roles: ['admin'] } },
  { path: '/settings',     name: 'Settings',       component: SettingsView,       meta: { requiresAuth: true, portal: 'staff', roles: ['admin'] } },

  // ── SUPER ADMIN ROUTES ──
  { path: '/super-admin/test-simple', name: 'TestSimple', component: TestSimple }, // NO AUTH - TEST ONLY
  { path: '/super-admin/debug', name: 'SuperAdminDebug', component: DEBUG_SuperAdminTest, meta: { requiresAuth: true, portal: 'staff' } },
  { path: '/super-admin/users', name: 'SuperAdminUsers', component: SuperAdminUserManagementView, meta: { requiresAuth: true, portal: 'staff', roles: ['super_admin'] } },
  { path: '/super-admin/polis', name: 'SuperAdminPolis', component: SuperAdminPoliManagementView, meta: { requiresAuth: true, portal: 'staff', roles: ['super_admin'] } },
  { path: '/super-admin/audit-logs', name: 'SuperAdminAuditLogs', component: SuperAdminAuditLogsView, meta: { requiresAuth: true, portal: 'staff', roles: ['super_admin'] } },
  { path: '/super-admin/backup', name: 'SuperAdminBackup', component: SuperAdminBackupView, meta: { requiresAuth: true, portal: 'staff', roles: ['super_admin'] } },
  { path: '/super-admin/settings', name: 'SuperAdminSettings', component: SuperAdminSettingsView, meta: { requiresAuth: true, portal: 'staff', roles: ['super_admin'] } },

  // Catch-all temporarily disabled for debugging
  // { path: '/:pathMatch(.*)*', redirect: '/' }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const staffToken = localStorage.getItem('token')
  const patientToken = localStorage.getItem('patient_token')
  
  console.log('🔍 Router Guard:', {
    to: to.path,
    from: from.path,
    hasToken: !!staffToken
  })

  // === PREVENT INFINITE LOOPS ===
  // If redirecting to same path, stop
  if (to.path === from.path) {
    console.log('⚠️ Redirect loop detected, blocking')
    return next(false)
  }

  // Track redirect chain to prevent loops
  if (!router._redirectCount) router._redirectCount = 0
  if (from.path !== '/' && to.path !== from.path) {
    router._redirectCount++
    if (router._redirectCount > 3) {
      console.error('❌ Too many redirects, blocking')
      router._redirectCount = 0
      return next(false)
    }
  } else {
    router._redirectCount = 0
  }

  // === AUTHENTICATION CHECK ===
  if (to.meta.requiresAuth) {
    // Patient portal
    if (to.meta.portal === 'patient') {
      if (!patientToken) {
        console.log('❌ No patient token → /pasien/login')
        return next('/pasien/login')
      }
    } 
    // Staff portal
    else if (to.meta.portal === 'staff') {
      if (!staffToken) {
        console.log('❌ No staff token → /login')
        return next('/login')
      }
      
      // === ROLE-BASED ACCESS CONTROL ===
      if (to.meta.roles && to.meta.roles.length > 0) {
        try {
          const userStr = localStorage.getItem('user')
          const user = JSON.parse(userStr || '{}')
          const userRole = user.role
          
          console.log('👤 User role:', userRole, '| Required:', to.meta.roles)
          
          // Check if user role is in allowed roles
          if (!to.meta.roles.includes(userRole)) {
            console.log('❌ Access denied - role not allowed')
            
            // SAFE REDIRECT: Based on user's actual role
            // Prevent redirect loop by checking we're not already on dashboard
            if (to.path !== '/dashboard') {
              // Super admin accessing non-super-admin route → dashboard
              if (userRole === 'super_admin') {
                console.log('   → Super admin redirected to /dashboard')
                return next('/dashboard')
              }
              // Regular staff accessing super-admin route → dashboard  
              else if (to.path.startsWith('/super-admin')) {
                console.log('   → Non-super-admin blocked from super-admin area → /dashboard')
                return next('/dashboard')
              }
              // Other role mismatches → dashboard
              else {
                console.log('   → Role mismatch → /dashboard')
                return next('/dashboard')
              }
            } else {
              // Already on dashboard but role doesn't match? Allow anyway (dashboard handles display)
              console.log('   → Already on dashboard, allowing')
              return next()
            }
          }
          
          console.log('✅ Role check passed')
        } catch (err) {
          console.error('❌ Error parsing user:', err)
          // Don't redirect on error, block navigation
          return next(false)
        }
      }
    }
  }

  // === GUEST PAGES - Redirect logged-in users ===
  if (to.meta.guest) {
    if (patientToken && to.meta.portal === 'patient') {
      console.log('🔄 Patient already logged in → /pasien/dashboard')
      return next('/pasien/dashboard')
    }
    if (staffToken && to.meta.portal !== 'patient') {
      console.log('🔄 Staff already logged in → /dashboard')
      return next('/dashboard')
    }
  }

  console.log('✅ Navigation allowed')
  next()
})

export default router
