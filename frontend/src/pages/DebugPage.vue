<template>
  <div style="padding: 2rem; font-family: monospace; white-space: pre-wrap;">
    <h1>🔍 DEBUG: API Configuration</h1>
    
    <section style="margin: 2rem 0; padding: 1rem; background: #f0f0f0;">
      <h2>Environment Variables</h2>
      <p><strong>import.meta.env.VITE_API_BASE_URL:</strong></p>
      <p style="color: #0066cc; font-weight: bold;">{{ viteApiBaseUrl || '(empty/undefined)' }}</p>
      
      <p><strong>import.meta.env.MODE:</strong> {{ mode }}</p>
      <p><strong>window.location.hostname:</strong> {{ hostname }}</p>
    </section>

    <section style="margin: 2rem 0; padding: 1rem; background: #f0f0f0;">
      <h2>API Instance Configuration</h2>
      <p><strong>baseURL:</strong> {{ baseURL }}</p>
      <p><strong>timeout:</strong> {{ timeout }}ms</p>
    </section>

    <section style="margin: 2rem 0; padding: 1rem; background: #fff3cd;">
      <h2>Test Login Request</h2>
      <div>
        <label>Email: <input v-model="email" type="email" /></label>
        <label>Password: <input v-model="password" type="password" /></label>
        <button @click="testLogin" :disabled="loading">{{ loading ? 'Loading...' : 'Test Login' }}</button>
      </div>
    </section>

    <section v-if="response" style="margin: 2rem 0; padding: 1rem; background: #d4edda;">
      <h2>Response</h2>
      <pre>{{ JSON.stringify(response, null, 2) }}</pre>
    </section>

    <section v-if="error" style="margin: 2rem 0; padding: 1rem; background: #f8d7da;">
      <h2>Error</h2>
      <pre>{{ error }}</pre>
    </section>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '@/core/services/api'

const email = ref('admin@sitara.test')
const password = ref('admin123')
const loading = ref(false)
const response = ref(null)
const error = ref(null)

// Read env variables
const viteApiBaseUrl = import.meta.env.VITE_API_BASE_URL
const mode = import.meta.env.MODE
const hostname = window.location.hostname
const baseURL = api.defaults.baseURL
const timeout = api.defaults.timeout

const testLogin = async () => {
  loading.value = true
  response.value = null
  error.value = null
  
  try {
    const result = await api.post('/login', {
      email: email.value,
      password: password.value
    })
    response.value = result.data
  } catch (err) {
    error.value = `${err.message}\n\n${err.response?.data ? JSON.stringify(err.response.data, null, 2) : 'No response data'}`
  } finally {
    loading.value = false
  }
}
</script>
