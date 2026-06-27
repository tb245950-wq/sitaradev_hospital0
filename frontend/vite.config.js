import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  server: {
    port: 5173,
    host: '0.0.0.0',
    strictPort: false,
    cors: true,
    // ⚡ Menambahkan baris ini agar terowongan SSH (localhost.run) tidak diblokir oleh Vite
    allowedHosts: true, 
    Allow: ['http://localhost:5173', 'http://127.0.0.1:5173', 'http://172.18.0.1:5173', 'http://172.16.20.218:5173', 'http://172.16.20.218:8000'],
    proxy: {
      '/api': {
        target: 'http://127.0.0.1:8000', // Ganti dengan URL backend Anda
        changeOrigin: true, 
      }
    }
  },
  build: {
    sourcemap: true,
    rollupOptions: {
      output: {
        manualChunks(id) {
          if (id.includes('node_modules')) {
            return 'vendor';
          }
        }
      }
    }
  }
})
