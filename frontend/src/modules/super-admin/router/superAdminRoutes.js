import SuperAdminLayout from '../views/SuperAdminLayout.vue'

export const superAdminRoutes = [
  {
    path: '/super-admin',
    component: SuperAdminLayout,
    meta: { requiresAuth: true, roles: ['super_admin'] },
    children: [
      {
        path: '',
        redirect: { name: 'super-admin.dashboard' }
      },
      {
        path: 'dashboard',
        name: 'super-admin.dashboard',
        component: () => import('../views/DashboardView.vue'),
        meta: { requiresAuth: true, roles: ['super_admin'] }
      },
      {
        path: 'users',
        name: 'super-admin.users',
        component: () => import('../views/UserManagementView.vue'),
        meta: { requiresAuth: true, roles: ['super_admin'] }
      },
      {
        path: 'polis',
        name: 'super-admin.polis',
        component: () => import('../views/PoliManagementView.vue'),
        meta: { requiresAuth: true, roles: ['super_admin'] }
      },
      {
        path: 'audit-logs',
        name: 'super-admin.audit-logs',
        component: () => import('../views/AuditLogsView.vue'),
        meta: { requiresAuth: true, roles: ['super_admin'] }
      },
      {
        path: 'backup',
        name: 'super-admin.backup',
        component: () => import('../views/BackupView.vue'),
        meta: { requiresAuth: true, roles: ['super_admin'] }
      },
      {
        path: 'settings',
        name: 'super-admin.settings',
        component: () => import('../views/SettingsView.vue'),
        meta: { requiresAuth: true, roles: ['super_admin'] }
      }
    ]
  }
]
