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
  }
]
