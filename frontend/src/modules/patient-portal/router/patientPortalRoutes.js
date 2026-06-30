// src/modules/patient-portal/router/patientPortalRoutes.js
// CATATAN: Route '/' (LandingPage) didefinisikan di router/index.js, TIDAK di sini.

export const patientPortalRoutes = [
  {
    path: '/pasien/login',
    name: 'PatientLogin',
    component: () => import('../views/PatientLoginView.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/pasien/register',
    name: 'PatientRegister',
    component: () => import('../views/PatientRegisterView.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/pasien/dashboard',
    name: 'PatientDashboard',
    component: () => import('../views/PatientDashboardView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/pasien/antrian',
    name: 'PatientAntrian',
    component: () => import('../views/PatientBookingView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/pasien/jadwal',
    name: 'PatientSchedule',
    component: () => import('../views/PatientScheduleView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/pasien/riwayat',
    name: 'PatientHistory',
    component: () => import('../views/PatientHistoryView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/pasien/profil',
    name: 'PatientProfile',
    component: () => import('../views/PatientProfileView.vue'),
    meta: { requiresAuth: true }
  }
]
