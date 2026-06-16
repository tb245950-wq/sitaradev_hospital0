<template>
  <div id="app">
    <router-view />
  </div>
</template>

<script setup>
import { onMounted } from 'vue'

onMounted(() => {
  console.log('✅ App mounted')
})
</script>

<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
#app { min-height: 100vh; }
</style>
