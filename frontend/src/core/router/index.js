import { createRouter, createWebHistory } from 'vue-router'
import { authService } from '../../modules/auth/services/authService'

// Import routes dari setiap modul
import { authRoutes } from '../../modules/auth/router/authRoutes'
import { dashboardRoutes } from '../../modules/dashboard/router/dashboardRoutes'
import { patientRoutes } from '../../modules/patients/router/patientRoutes'

// Import User Management View
import UserManagementView from '../../modules/users/views/UserManagementView.vue'

const routes = [
  ...authRoutes,
  ...dashboardRoutes,
  ...patientRoutes,
  
  // User Management Route (Admin Only)
  {
    path: '/users',
    name: 'UserManagement',
    component: UserManagementView,
    meta: { 
      requiresAuth: true, 
      roles: ['admin'] 
    }
  },
  
  {
    path: '/unauthorized',
    name: 'unauthorized',
    component: () => import('../../shared/components/common/Unauthorized.vue')
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const isAuthenticated = authService.isAuthenticated()
  const requiresAuth = to.meta.requiresAuth !== false
  const user = authService.getStoredUser()

  if (requiresAuth && !isAuthenticated) {
    next('/login')
  } else if (to.meta.guest && isAuthenticated) {
    next('/dashboard')
  } else if (to.meta.roles && !to.meta.roles.includes(user?.role)) {
    next('/unauthorized')
  } else {
    next()
  }
})

export default router