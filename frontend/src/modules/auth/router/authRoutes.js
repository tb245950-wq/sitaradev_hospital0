export const authRoutes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/LoginPage.vue'),
    meta: { requiresAuth: false, guest: true }
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('../views/RegisterPage.vue'),
    meta: { requiresAuth: false, guest: true }
  }
]
