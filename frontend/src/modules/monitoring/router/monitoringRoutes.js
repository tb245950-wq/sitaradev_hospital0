export const monitoringRoutes = [
  {
    path: '/monitoring',
    name: 'monitoring',
    component: () => import('../views/MonitoringView.vue'),
    meta: { requiresAuth: true, roles: ['admin', 'dokter', 'terapis'] }
  }
]
