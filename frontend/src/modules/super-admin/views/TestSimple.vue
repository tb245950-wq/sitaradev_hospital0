<template>
  <div style="padding: 40px; font-family: sans-serif; background: #f0f9ff;">
    <h1 style="color: #0ea5e9;">✅ ROUTING WORKS!</h1>
    <p style="font-size: 18px;">You successfully navigated to <strong>/super-admin/test-simple</strong></p>
    
    <div style="background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
      <h3>Current Route Info:</h3>
      <pre>{{ $route }}</pre>
    </div>

    <div style="margin: 20px 0;">
      <button @click="goToUsers" style="padding: 12px 24px; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer; margin-right: 10px;">
        Go to User Management
      </button>
      <button @click="goToDashboard" style="padding: 12px 24px; background: #64748b; color: white; border: none; border-radius: 6px; cursor: pointer;">
        Back to Dashboard
      </button>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

const goToUsers = () => {
  console.log('Navigating to /super-admin/users...')
  router.push('/super-admin/users')
}

const goToDashboard = () => {
  console.log('Navigating to /dashboard...')
  router.push('/dashboard')
}
</script>
