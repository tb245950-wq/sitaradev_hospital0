import { createRouter, createWebHistory } from 'vue-router'
import { patientPortalRoutes } from '../modules/patient-portal/router/patientPortalRoutes'
import { authRoutes } from '../modules/auth/router/authRoutes'
import { dashboardRoutes } from '../modules/dashboard/router/dashboardRoutes'
import { patientRoutes } from '../modules/patients/router/patientRoutes'
import { queueRoutes } from '../modules/queue/router/queueRoutes'
import { assessmentRoutes } from '../modules/assessment/router/assessmentRoutes'
import { therapyRoutes } from '../modules/therapy/router/therapyRoutes'
import { monitoringRoutes } from '../modules/monitoring/router/monitoringRoutes'
import { reportsRoutes } from '../modules/reports/router/reportsRoutes'
import { usersRoutes } from '../modules/users/router/usersRoutes'
import { settingsRoutes } from '../modules/settings/router/settingsRoutes'
import { analyticsRoutes } from '../modules/analytics/router/analyticsRoutes'

// Gabungkan semua route
const routes = [
  // Landing page (root)
  {
    path: '/',
    name: 'landing',
    component: () => import('../modules/auth/views/LandingPage.vue'),
    meta: { requiresAuth: false }
  },

  // Portal Pasien (prefix /pasien)
  ...patientPortalRoutes,

  // Auth staff (login/register)
  ...authRoutes,

  // Staff: Dashboard
  ...dashboardRoutes,

  // Staff: Data Pasien
  ...patientRoutes,

  // Staff: Antrian
  ...queueRoutes,

  // Staff: Assessment
  ...assessmentRoutes,

  // Staff: Terapi
  ...therapyRoutes,

  // Staff: Monitoring
  ...monitoringRoutes,

  // Staff: Laporan Medis
  ...reportsRoutes,

  // Staff: Manajemen User
  ...usersRoutes,

  // Staff: Pengaturan
  ...settingsRoutes,

  // Staff: Analitik
  ...analyticsRoutes,

  // 404 fallback
  {
    path: '/:pathMatch(.*)*',
    redirect: '/'
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

// ─── Navigation Guard ────────────────────────────────────────────────────────
router.beforeEach((to, from, next) => {
  const isPatientRoute = to.path.startsWith('/pasien')

  // ── Portal Pasien ──
  if (isPatientRoute) {
    const patientToken = localStorage.getItem('patient_token')

    if (to.meta.requiresAuth && !patientToken) {
      return next('/pasien/login')
    }

    if ((to.path === '/pasien/login' || to.path === '/pasien/register') && patientToken) {
      return next('/pasien/dashboard')
    }

    return next()
  }

  // ── Portal Staff ──
  const staffToken = localStorage.getItem('token')

  // Halaman publik (landing, login, register) — tidak butuh auth
  if (!to.meta.requiresAuth) {
    // Jika sudah login staff dan coba akses login/register, arahkan ke dashboard
    if ((to.path === '/login' || to.path === '/register') && staffToken) {
      return next('/dashboard')
    }
    return next()
  }

  // Halaman butuh auth staff, tapi belum login → ke /login
  if (!staffToken) {
    return next('/login')
  }

  // Cek role-based access jika route punya meta.roles
  if (to.meta.roles && to.meta.roles.length > 0) {
    try {
      const userRaw = localStorage.getItem('user')
      const user = userRaw ? JSON.parse(userRaw) : null
      const userRole = user?.role || null

      if (!userRole || !to.meta.roles.includes(userRole)) {
        // Role tidak punya akses → balik ke dashboard
        console.warn(`[Router] Role '${userRole}' tidak punya akses ke '${to.path}'`)
        return next('/dashboard')
      }
    } catch (e) {
      console.error('[Router] Gagal parse user dari localStorage:', e)
      return next('/login')
    }
  }

  return next()
})

export default router