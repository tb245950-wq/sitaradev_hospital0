export const reportsRoutes = [
  {
    path: '/reports',
    name: 'reports',
    component: () => import('../views/ReportsView.vue'),
    meta: { requiresAuth: true, roles: ['admin', 'dokter'] }
  }
]
