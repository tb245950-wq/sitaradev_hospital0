export const queueRoutes = [
  {
    path: '/queues',
    name: 'queues',
    component: () => import('../views/QueueView.vue'),
    meta: { requiresAuth: true, roles: ['admin', 'dokter'] }
  },
  {
    path: '/queues/create',
    name: 'queues.create',
    component: () => import('../views/QueueCreateView.vue'),
    meta: { requiresAuth: true, roles: ['admin', 'dokter'] }
  }
]
