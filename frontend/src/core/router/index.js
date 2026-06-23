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
import PatientLoginView from '@/modules/patient-portal/views/PatientLoginView.vue'
import PatientRegisterView from '@/modules/patient-portal/views/PatientRegisterView.vue'
import PatientDashboardView from '@/modules/patient-portal/views/PatientDashboardView.vue'

const routes = [
  { path: '/', name: 'Landing', component: LandingPage, meta: { guest: true } },
  { path: '/login', name: 'Login', component: LoginPage, meta: { guest: true } },
  { path: '/pasien/login', name: 'PatientLogin', component: PatientLoginView, meta: { guest: true, portal: 'patient' } },
  { path: '/pasien/register', name: 'PatientRegister', component: PatientRegisterView, meta: { guest: true, portal: 'patient' } },
  
  { path: '/dashboard', name: 'Dashboard', component: DashboardView, meta: { requiresAuth: true, portal: 'staff' } },
  { path: '/patients', name: 'Patients', component: PatientListView, meta: { requiresAuth: true, portal: 'staff' } },
  { path: '/queues', name: 'Queues', component: QueueView, meta: { requiresAuth: true, portal: 'staff' } },
  { path: '/assessments', name: 'Assessments', component: AssessmentListView, meta: { requiresAuth: true, portal: 'staff' } },
  { path: '/therapies', name: 'Therapies', component: TherapyListView, meta: { requiresAuth: true, portal: 'staff' } },
  { path: '/monitoring', name: 'Monitoring', component: MonitoringView, meta: { requiresAuth: true, portal: 'staff' } },
  { path: '/reports', name: 'Reports', component: ReportsView, meta: { requiresAuth: true, portal: 'staff' } },
  { path: '/users', name: 'UserManagement', component: UserManagementView, meta: { requiresAuth: true, portal: 'staff' } },
  { path: '/settings', name: 'Settings', component: SettingsView, meta: { requiresAuth: true, portal: 'staff' } },

  { path: '/pasien/dashboard', name: 'PatientDashboard', component: PatientDashboardView, meta: { requiresAuth: true, portal: 'patient' } },
  
  { path: '/:pathMatch(.*)*', redirect: '/' }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const staffToken = localStorage.getItem('token')
  const patientToken = localStorage.getItem('patient_token')
  const user = authService.getStoredUser()
  
  if (to.meta.requiresAuth) {
    if (to.meta.portal === 'patient' && !patientToken) return next('/pasien/login')
    if (to.meta.portal === 'staff' && !staffToken) return next('/login')
  }
  
  if (to.meta.guest && (staffToken || patientToken)) {
    if (user?.role === 'pasien') return next('/pasien/dashboard')
    return next('/dashboard')
  }
  
  next()
})

export default router
