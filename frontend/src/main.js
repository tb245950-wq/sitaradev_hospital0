import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './core/router'

console.log('🚀 Starting SITARA App...')
console.log('📦 Vue version:', '3.x')
console.log('🛣️  Routes loaded:', router.getRoutes().length)

// Log semua routes
router.getRoutes().forEach(route => {
  console.log(`  → ${route.path} [${route.name}]`)
})

const app = createApp(App)

// Mount router DULU
app.use(router)
app.use(createPinia())

// Mount app
app.mount('#app')

console.log('✅ App mounted to #app')
console.log('🌐 Open: http://localhost:5173')