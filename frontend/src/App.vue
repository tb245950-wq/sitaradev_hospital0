<template>
  <AlertNotification />
  <router-view v-slot="{ Component }">
    <transition name="fade" mode="out-in">
      <component :is="Component" />
    </transition>
  </router-view>
</template>

<script setup>
import { onMounted, onErrorCaptured } from 'vue'
import AlertNotification from './shared/components/AlertNotification.vue'

console.log('✅ App.vue: Setup')

onMounted(() => {
  console.log('📱 App.vue: Mounted')
})

onErrorCaptured((error) => {
  console.error('❌ Vue error:', error.message)
  return false
})
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background-color: #f8fafc;
  color: #1e293b;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
