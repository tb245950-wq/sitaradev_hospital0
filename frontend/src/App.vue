<template>
  <div id="app">
    <router-view v-slot="{ Component, route }">
      <transition name="fade" mode="out-in">
        <component :is="Component" :key="route.path" />
      </transition>
    </router-view>
  </div>
</template>

<script setup>
import { onMounted, onErrorCaptured } from 'vue'

console.log('✅ App.vue: Script setup execution')

onMounted(() => {
  console.log('📱 App.vue: Component mounted')
})

onErrorCaptured((error, instance, info) => {
  console.error('❌ App.vue: Vue error captured:', {
    message: error.message,
    stack: error.stack,
    info
  })
  return true
})
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

#app {
  min-height: 100vh;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  color: #1e293b;
  background: #f8fafc;
}

/* Page transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
