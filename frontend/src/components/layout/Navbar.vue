<template>
  <nav class="navbar">
    <div class="navbar-left">
      <button @click="$emit('toggle-sidebar')" class="toggle-btn">
        <span class="menu-icon">☰</span>
      </button>
      <h2 class="page-title">{{ pageTitle }}</h2>
    </div>

    <div class="navbar-right">
      <div class="user-profile">
        <div class="user-info">
          <span class="user-name">{{ authStore.user?.name }}</span>
          <span class="user-role">{{ authStore.user?.role }}</span>
        </div>
        <div class="user-avatar">
          {{ authStore.user?.name?.charAt(0) }}
        </div>
      </div>
      <button @click="handleLogout" class="logout-btn" title="Keluar">
        Logout
      </button>
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

defineEmits(['toggle-sidebar'])

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const pageTitle = computed(() => {
  switch (route.name) {
    case 'dashboard': return 'Dashboard'
    case 'patients': return 'Data Pasien'
    case 'queues': return 'Antrian'
    default: return 'SITARA'
  }
})

const handleLogout = async () => {
  if (confirm('Apakah Anda yakin ingin keluar?')) {
    await authStore.logout()
    router.push('/login')
  }
}
</script>

<style scoped>
.navbar {
  height: 64px;
  background: white;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  position: sticky;
  top: 0;
  z-index: 100;
}

.navbar-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.toggle-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #64748b;
  display: none; /* Hidden on desktop */
}

@media (max-width: 768px) {
  .toggle-btn {
    display: block;
  }
}

.page-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
}

.navbar-right {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-info {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.user-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1e293b;
}

.user-role {
  font-size: 0.75rem;
  color: #64748b;
  text-transform: capitalize;
}

.user-avatar {
  width: 36px;
  height: 36px;
  background: #3b82f6;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
}

.logout-btn {
  padding: 0.5rem 1rem;
  background: #f1f5f9;
  color: #ef4444;
  border: none;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.logout-btn:hover {
  background: #fee2e2;
}
</style>
