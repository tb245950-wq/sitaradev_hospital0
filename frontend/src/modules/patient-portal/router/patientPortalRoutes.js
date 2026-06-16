export const patientPortalRoutes = [
  {
    path: '/pasien/login',
    name: 'PatientLogin',
    component: () => import('../views/PatientLoginView.vue'),
    meta: { requiresAuth: false, guest: true, portal: 'patient' }
  },
  {
    path: '/pasien/register',
    name: 'PatientRegister',
    component: () => import('../views/PatientRegisterView.vue'),
    meta: { requiresAuth: false, guest: true, portal: 'patient' }
  },
  {
    path: '/pasien/dashboard',
    name: 'PatientDashboard',
    component: () => import('../views/PatientDashboardView.vue'),
    meta: { requiresAuth: true, roles: ['pasien'], portal: 'patient' }
  },
  {
    path: '/pasien/booking',
    name: 'PatientBooking',
    component: () => import('../views/PatientBookingView.vue'),
    meta: { requiresAuth: true, roles: ['pasien'], portal: 'patient' }
  },
  {
    path: '/pasien/antrian-saya',
    name: 'PatientQueue',
    component: () => import('../views/PatientQueueView.vue'),
    meta: { requiresAuth: true, roles: ['pasien'], portal: 'patient' }
  },
  {
    path: '/pasien/jadwal',
    name: 'PatientSchedule',
    component: () => import('../views/PatientScheduleView.vue'),
    meta: { requiresAuth: true, roles: ['pasien'], portal: 'patient' }
  },
  {
    path: '/pasien/riwayat',
    name: 'PatientHistory',
    component: () => import('../views/PatientHistoryView.vue'),
    meta: { requiresAuth: true, roles: ['pasien'], portal: 'patient' }
  },
  {
    path: '/pasien/profil',
    name: 'PatientProfile',
    component: () => import('../views/PatientProfileView.vue'),
    meta: { requiresAuth: true, roles: ['pasien'], portal: 'patient' }
  }
]
