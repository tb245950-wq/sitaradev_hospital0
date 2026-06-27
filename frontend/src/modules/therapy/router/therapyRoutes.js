export const therapyRoutes = [
  {
    path: '/therapies',
    name: 'therapies',
    component: () => import('../views/TherapyListView.vue'),
    meta: { requiresAuth: true, roles: ['admin', 'dokter', 'terapis'] }
  },
  {
    path: '/therapies/create',
    name: 'therapies.create',
    component: () => import('../views/TherapyCreateView.vue'),
    meta: { requiresAuth: true, roles: ['admin', 'dokter', 'terapis'] }
  },
  {
    path: '/therapies/:id',
    name: 'therapies.detail',
    component: () => import('../views/TherapyView.vue'),
    meta: { requiresAuth: true, roles: ['admin', 'dokter', 'terapis'] }
  }
]
