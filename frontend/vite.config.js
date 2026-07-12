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

  // Konfigurasi development server (TIDAK berpengaruh di production)
  server: {
    port: 5173,
    host: '0.0.0.0',
    strictPort: false,
    cors: true,
    // Izinkan semua host (termasuk SSH tunnel, localhost.run, dll)
    allowedHosts: true,
    // Proxy hanya aktif saat `npm run dev` — tidak ada di production build
    proxy: {
      '/api': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
      }
    }
  },

  build: {
    // Sourcemap dimatikan di production agar kode sumber tidak terekspos
    sourcemap: false,
    // Target browser modern
    target: 'es2015',
    rollupOptions: {
      output: {
        // Pisahkan vendor chunk agar browser bisa cache lebih efisien
        manualChunks(id) {
          if (id.includes('node_modules')) {
            return 'vendor'
          }
        }
      }
    }
  }
})
