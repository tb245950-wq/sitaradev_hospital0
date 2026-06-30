<template>
  <div style="padding: 20px; font-family: monospace;">
    <h1>🔍 Super Admin Debug Page</h1>
    
    <div style="background: #f4f4f4; padding: 15px; margin: 10px 0; border-radius: 5px;">
      <h3>1. Auth Store State</h3>
      <pre>{{ authStoreDebug }}</pre>
    </div>

    <div style="background: #f4f4f4; padding: 15px; margin: 10px 0; border-radius: 5px;">
      <h3>2. LocalStorage</h3>
      <pre>{{ localStorageDebug }}</pre>
    </div>

    <div style="background: #f4f4f4; padding: 15px; margin: 10px 0; border-radius: 5px;">
      <h3>3. Router Info</h3>
      <pre>Current Route: {{ $route.path }}
Route Name: {{ $route.name }}
Route Meta: {{ JSON.stringify($route.meta, null, 2) }}</pre>
    </div>

    <div style="background: #f4f4f4; padding: 15px; margin: 10px 0; border-radius: 5px;">
      <h3>4. Test Navigation</h3>
      <button @click="testNavigation('/super-admin/users')" style="padding: 10px; margin: 5px; background: #3b82f6; color: white; border: none; cursor: pointer;">
        Navigate to /super-admin/users
      </button>
      <button @click="testNavigation('/super-admin/polis')" style="padding: 10px; margin: 5px; background: #3b82f6; color: white; border: none; cursor: pointer;">
        Navigate to /super-admin/polis
      </button>
      <div v-if="navigationResult" style="margin-top: 10px; padding: 10px; background: #fef3c7;">
        {{ navigationResult }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../../auth/stores/authStore'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const navigationResult = ref(null)

const authStoreDebug = computed(() => {
  return {
    user: authStore.user,
    token: authStore.token ? `EXISTS (${authStore.token.length} chars)` : 'NULL',
    userRole: authStore.userRole,
    isAuthenticated: authStore.isAuthenticated,
    isAdmin: authStore.isAdmin,
    isDokter: authStore.isDokter,
    isTerapis: authStore.isTerapis
  }
})

const localStorageDebug = computed(() => {
  const token = localStorage.getItem('token')
  const userStr = localStorage.getItem('user')
  let user = null
  
  try {
    user = JSON.parse(userStr || '{}')
  } catch (e) {
    user = { error: e.message }
  }
  
  return {
    token: token ? `EXISTS (${token.length} chars)` : 'NULL',
    user: user,
    userRole: user?.role || 'NULL',
    match: user?.role === 'super_admin' ? '✅ YES' : '❌ NO'
  }
})

const testNavigation = async (path) => {
  console.log('🔍 Testing navigation to:', path)
  navigationResult.value = `Navigating to ${path}...`
  
  try {
    await router.push(path)
    navigationResult.value = `✅ Navigation to ${path} successful!`
    console.log('✅ Navigation successful')
  } catch (error) {
    navigationResult.value = `❌ Navigation failed: ${error.message}`
    console.error('❌ Navigation error:', error)
  }
}
</script>
