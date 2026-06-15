<template>
  <div class="error-page">
    <div class="error-content">
      <div class="error-icon">🔒</div>
      <h1>403 - Akses Ditolak</h1>
      <p>Anda tidak memiliki izin untuk mengakses halaman ini.</p>
      <p v-if="allowedRoles" class="error-detail">
        Halaman ini hanya dapat diakses oleh <strong>{{ allowedRoles }}</strong>.
      </p>
      <router-link to="/dashboard" class="btn-back">
        ← Kembali ke Dashboard
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  allowedRoles: {
    type: String,
    default: ''
  }
})
</script>

<style scoped>
.error-page {
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8fafc;
  padding: 1.5rem;
}

.error-content {
  background: white;
  padding: 3rem;
  border-radius: 1rem;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
  max-width: 500px;
  width: 100%;
  text-align: center;
}

.error-icon {
  font-size: 4rem;
  margin-bottom: 1.5rem;
}

h1 {
  font-size: 2.25rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 1rem;
}

p {
  color: #64748b;
  font-size: 1.125rem;
  margin-bottom: 0.5rem;
}

.error-detail {
  font-size: 0.875rem;
  color: #94a3b8;
  margin-bottom: 2.5rem;
}

.btn-back {
  display: inline-block;
  padding: 0.75rem 1.5rem;
  background: #3b82f6;
  color: white;
  text-decoration: none;
  border-radius: 0.5rem;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-back:hover {
  background: #2563eb;
  transform: translateY(-2px);
}
</style>
