export const settingsRoutes = [
  {
    path: '/settings',
    name: 'settings',
    component: () => import('../views/SettingsView.vue'),
    meta: { requiresAuth: true, roles: ['admin'] }
  },
  {
    path: '/settings/poli',
    name: 'poli-management',
    component: () => import('../views/PoliView.vue'),
    meta: { requiresAuth: true, roles: ['admin'] }
  }
]
