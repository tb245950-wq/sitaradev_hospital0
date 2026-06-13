import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import LandingPage from '../views/auth/LandingPage.vue'
import LoginPage from '../views/auth/LoginPage.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'landing',
      component: LandingPage
    },
    {
      path: '/login',
      name: 'login',
      component: LoginPage,
      meta: { guest: true }
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/auth/RegisterPage.vue'),
      meta: { guest: true }
    },
    {
      path: '/patients',
      name: 'patients',
      component: () => import('../views/dashboard/Dashboard.vue'), // Placeholder
      meta: { requiresAuth: true }
    },
    {
      path: '/assessments',
      name: 'assessments',
      component: () => import('../views/dashboard/Dashboard.vue'), // Placeholder
      meta: { requiresAuth: true, roles: ['admin', 'dokter'] }
    },
    {
      path: '/therapies',
      name: 'therapies',
      component: () => import('../views/dashboard/Dashboard.vue'), // Placeholder
      meta: { requiresAuth: true, roles: ['admin', 'dokter', 'terapis'] }
    },
    {
      path: '/reports',
      name: 'reports',
      component: () => import('../views/dashboard/Dashboard.vue'), // Placeholder
      meta: { requiresAuth: true, roles: ['admin', 'dokter'] }
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('../views/dashboard/Dashboard.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/unauthorized',
      name: 'unauthorized',
      component: () => import('../views/Unauthorized.vue')
    }
  ]
})

router.beforeEach((to, from, next) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    next('/login')
  } else if (to.meta.guest && auth.isAuthenticated) {
    next('/dashboard')
  } else if (to.meta.roles && !to.meta.roles.includes(auth.userRole)) {
    next('/unauthorized')
  } else {
    next()
  }
})

export default router