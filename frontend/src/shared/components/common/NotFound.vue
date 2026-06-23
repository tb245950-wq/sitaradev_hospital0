<template>
  <div class="error-page">
    <div class="error-content">
      <div class="error-icon">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
      </div>
      <h1>404 - Halaman Tidak Ditemukan</h1>
      <p>Maaf, halaman yang Anda cari tidak ada atau telah dipindahkan.</p>
      <button @click="goToDashboard" class="btn-back-large">
        <span class="arrow">←</span>
        <span>Kembali ke Dashboard</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { useNavigation } from '../../../shared/composables/useNavigation'

const { goToDashboard } = useNavigation()
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
  display: flex;
  justify-content: center;
  align-items: center;
  color: #64748b;
  margin-bottom: 1.5rem;
}

.error-icon svg {
  width: 64px;
  height: 64px;
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
  margin-bottom: 2.5rem;
}

.btn-back-large {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 2rem;
  background: #1e40af;
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  margin-top: 2rem;
}

.btn-back-large:hover {
  background: #1e3a8a;
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(30, 64, 175, 0.3);
}
</style>
