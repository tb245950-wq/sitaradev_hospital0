export const assessmentRoutes = [
  {
    path: '/assessments',
    name: 'assessments',
    component: () => import('../views/AssessmentListView.vue'),
    meta: { requiresAuth: true, roles: ['admin', 'dokter'] }
  },
  {
    path: '/assessments/create',
    name: 'assessments.create',
    component: () => import('../views/AssessmentCreateView.vue'),
    meta: { requiresAuth: true, roles: ['admin', 'dokter'] }
  },
  {
    path: '/assessments/:id',
    name: 'assessments.detail',
    component: () => import('../views/AssessmentView.vue'),
    meta: { requiresAuth: true, roles: ['admin', 'dokter'] }
  }
]
