<template>
  <div class="dashboard-layout">
    <Sidebar :is-open="isSidebarOpen" />
    
    <div class="main-content">
      <Navbar @toggle-sidebar="isSidebarOpen = !isSidebarOpen" />
      
      <main class="content-body">
        <div v-if="loading" class="p-4">Memverifikasi akses...</div>
        <template v-else>
          <AdminDashboard v-if="authStore.user?.role === 'admin'" />
          <DoctorDashboard v-else-if="authStore.user?.role === 'dokter'" />
          <TerapisDashboard v-else-if="authStore.user?.role === 'terapis'" />
          <div v-else class="p-4 text-gray-500">Dashboard belum tersedia untuk role Anda.</div>
        </template>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../auth/stores/authStore'
import { useAnalyticsStore } from '../../analytics/stores/analyticsStore'
import AdminDashboard from './AdminDashboard.vue'
import DoctorDashboard from './DoctorDashboard.vue'
import TerapisDashboard from './TerapisDashboard.vue'
import Sidebar from '../../../shared/components/layout/Sidebar.vue'
import Navbar from '../../../shared/components/layout/Navbar.vue'

const authStore = useAuthStore()
const analyticsStore = useAnalyticsStore()
const router = useRouter()
const isSidebarOpen = ref(false)
const loading = ref(true)

onMounted(() => {
  if (authStore.user?.role === 'pasien') {
    router.push('/pasien/dashboard')
    return
  }
  analyticsStore.fetchAnalytics()
  analyticsStore.startPolling()
  loading.value = false
})

onUnmounted(() => analyticsStore.stopPolling())
</script>

<style scoped>
.dashboard-layout { display: flex; min-height: 100vh; background: #f8fafc; }
.main-content { flex: 1; margin-left: 260px; display: flex; flex-direction: column; }
.content-body { padding: 1.5rem; }
@media (max-width: 768px) { .main-content { margin-left: 0; } }
</style>
