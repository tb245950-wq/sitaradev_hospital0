<template>
  <div class="masked-nik-container">
    <span class="nik-display">
      {{ isVisible ? fullNik : maskedNik }}
    </span>
    <button @click="toggleVisibility" class="btn-toggle" :title="isVisible ? 'Sembunyikan' : 'Tampilkan'">
      {{ isVisible ? '👁️' : '🙈' }}
    </button>
    <button @click="copyToClipboard" class="btn-copy" title="Salin">
      📋
    </button>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

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
    .catch(err => console.error('Gagal menyalin:', err))
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
  padding: 0.2rem 0.4rem;
  border-radius: 0.25rem;
  font-size: 0.9rem;
}

.btn-toggle, .btn-copy {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 0.8rem;
  padding: 0.2rem;
}
</style>
