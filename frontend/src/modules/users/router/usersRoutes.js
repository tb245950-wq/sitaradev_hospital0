export const usersRoutes = [
  {
    path: '/users',
    name: 'users',
    component: () => import('../views/UserManagementView.vue'),
    meta: { requiresAuth: true, roles: ['admin'] }
  }
]
