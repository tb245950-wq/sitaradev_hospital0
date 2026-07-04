<template>
  <span class="masked-nik">{{ maskedNik || '-' }}</span>
</template>

<script setup>
defineProps({
  fullNik:   { type: String, default: '' },
  maskedNik: { type: String, default: '-' }
})
</script>

<style scoped>
.masked-nik {
  font-family: 'Courier New', Courier, monospace;
  font-size: 0.82rem;
  font-weight: 600;
  background: #f1f5f9;
  color: #475569;
  padding: 0.2rem 0.6rem;
  border-radius: 0.375rem;
  border: 1px solid #e2e8f0;
  letter-spacing: 0.04em;
  white-space: nowrap;
  user-select: none;
}
</style>
