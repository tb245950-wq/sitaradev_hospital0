<template>
  <span class="masked-nik">{{ displayNik }}</span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  fullNik:   { type: String, default: '' },
  maskedNik: { type: String, default: '-' }
})

// Tampilkan format: ****1234 (hanya 4 bintang + 4 angka terakhir)
const displayNik = computed(() => {
  const nik = props.maskedNik || props.fullNik
  if (!nik || nik === '-') return '-'
  // Ambil 4 digit terakhir dari string (abaikan bintang)
  const digits = nik.replace(/\*/g, '')
  const last4 = digits.slice(-4)
  if (!last4) return '****'
  return '****' + last4
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
