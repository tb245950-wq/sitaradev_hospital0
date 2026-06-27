import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import './assets/main.css'

console.log('🚀 Starting SITARA App...')

try {
  const app = createApp(App)
  
  // Use plugins
  app.use(createPinia())
  app.use(router)
  
  // Mount app
  app.mount('#app')
  
  console.log('✅ App mounted successfully!')
} catch (error) {
  console.error('❌ Failed to mount app:', error)
}
