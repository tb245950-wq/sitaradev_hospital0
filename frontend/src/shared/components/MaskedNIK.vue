<template>
  <div class="masked-nik-container">
    <span class="nik-display">
      {{ isVisible ? fullNik : maskedNik }}
    </span>
    <button @click="toggleVisibility" class="btn-toggle" :title="isVisible ? 'Sembunyikan' : 'Tampilkan'">
      <svg v-if="isVisible" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon">
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
        <circle cx="12" cy="12" r="3"></circle>
      </svg>
      <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon">
        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
        <line x1="1" y1="1" x2="23" y2="23"></line>
      </svg>
    </button>
    <button @click="copyToClipboard" class="btn-copy" title="Salin NIK">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon">
        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
      </svg>
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  fullNik: { type: String, required: true },
  maskedNik: { type: String, required: true }
})

const isVisible = ref(false)

const toggleVisibility = () => {
  isVisible.value = !isVisible.value
}

const copyToClipboard = () => {
  navigator.clipboard.writeText(props.fullNik)
    .then(() => alert('NIK disalin ke clipboard'))
    .catch(err => console.error('Gagal menyalin NIK:', err))
}
</script>

<style scoped>
.masked-nik-container {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-family: monospace;
}

.nik-display {
  background: #f1f5f9;
  padding: 0.2rem 0.6rem;
  border-radius: 0.25rem;
  font-size: 0.9rem;
  font-weight: 500;
  color: #334155;
  border: 1px solid #e2e8f0;
}

.btn-toggle, .btn-copy {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 0.25rem;
  cursor: pointer;
  padding: 0.35rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  transition: all 0.2s;
}

.btn-toggle:hover, .btn-copy:hover {
  background: #f1f5f9;
  color: #1e40af;
  border-color: #cbd5e1;
}

.svg-icon {
  width: 14px;
  height: 14px;
}
</style>
