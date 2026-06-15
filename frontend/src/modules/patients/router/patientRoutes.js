export const patientRoutes = [
  {
    path: '/patients',
    name: 'patients',
    component: () => import('../views/PatientListView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/patients/create',
    name: 'patients.create',
    component: () => import('../views/PatientCreateView.vue'),
    meta: { requiresAuth: true, roles: ['admin', 'dokter'] }
  },
  {
    path: '/patients/:id',
    name: 'patients.detail',
    component: () => import('../views/PatientDetailView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/patients/:id/edit',
    name: 'patients.edit',
    component: () => import('../views/PatientEditView.vue'),
    meta: { requiresAuth: true, roles: ['admin', 'dokter'] }
  }
]
