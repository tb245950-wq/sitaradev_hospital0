import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './core/router'
import * as dateUtils from './utils/dateFormatter'

console.log('🚀 Starting SITARA App...')

// Global error handler for uncaught promises
window.addEventListener('unhandledrejection', event => {
  console.error('❌ Unhandled promise rejection:', event.reason)
})

// Global error handler for generic errors
window.addEventListener('error', event => {
  console.error('❌ Global error:', event.error)
})

const app = createApp(App)

// Use Pinia
const pinia = createPinia()
app.use(pinia)

// Use Router
app.use(router)

// Global properties for date formatting
app.config.globalProperties.$formatDate = dateUtils.formatDate
app.config.globalProperties.$formatDateForInput = dateUtils.formatDateForInput
app.config.globalProperties.$getToday = dateUtils.getToday
app.config.globalProperties.$getCurrentMonth = dateUtils.getCurrentMonth
app.config.globalProperties.$getRelativeTime = dateUtils.getRelativeTime

// Mount app
try {
  app.mount('#app')
  console.log('✅ App successfully mounted to #app')
  console.log('🛣️  Available routes:', router.getRoutes().length)
} catch (error) {
  console.error('💥 Critical error during app mounting:', error)
}

export { app }
