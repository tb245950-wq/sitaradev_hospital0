<template>
  <div class="dashboard-layout">
    <Sidebar :is-open="isSidebarOpen" />
    
    <div class="main-content">
      <Navbar @toggle-sidebar="isSidebarOpen = !isSidebarOpen" />
      
      <main class="content-body">
        <div v-if="loading" class="p-4">Memverifikasi akses...</div>
        <template v-else>
          <DoctorDashboard v-if="authStore.user?.role === 'dokter'" />
          <AdminDashboard v-else-if="authStore.user?.role === 'admin'" />
          <div v-else class="p-4">Dashboard belum tersedia untuk role Anda.</div>
        </template>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../auth/stores/authStore'
import AdminDashboard from './AdminDashboard.vue'
import DoctorDashboard from './DoctorDashboard.vue'
import Sidebar from '../../../shared/components/layout/Sidebar.vue'
import Navbar from '../../../shared/components/layout/Navbar.vue'

const authStore = useAuthStore()
const router = useRouter()
const isSidebarOpen = ref(false)
const loading = ref(true)

onMounted(() => {
  // Security Redirect: If user is a patient, send them to patient portal
  if (authStore.user?.role === 'pasien') {
    router.push('/pasien/dashboard')
  }
  loading.value = false
})
</script>

<style scoped>
.dashboard-layout { display: flex; min-height: 100vh; background: #f8fafc; }
.main-content { flex: 1; margin-left: 260px; display: flex; flex-direction: column; }
.content-body { padding: 1.5rem; }
@media (max-width: 768px) { .main-content { margin-left: 0; } }
</style>
