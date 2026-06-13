<template>
  <div class="register-page">
    <div class="circle circle-1"></div>
    <div class="circle circle-2"></div>

    <router-link to="/" class="back-btn">
      ← Kembali
    </router-link>

    <div class="register-container">
      <div class="logo-section">
        <img src="@/assets/SITARA_RM_BG.png" alt="SITARA" class="logo" />
        <h1>SITARA</h1>
        <p>Sistem Informasi Terpadu</p>
      </div>

      <div class="form-section">
        <h2>Daftar Akun Baru</h2>
        <p class="subtitle">Bergabunglah dengan SITARA hari ini</p>

        <form @submit.prevent="handleRegister" class="register-form">
          <div class="form-group">
            <label for="name">Nama Lengkap</label>
            <input
              type="text"
              id="name"
              v-model="form.name"
              placeholder="Masukkan nama lengkap"
              required
            />
          </div>

          <div class="form-group">
            <label for="email">Email</label>
            <input
              type="email"
              id="email"
              v-model="form.email"
              placeholder="nama@email.com"
              required
            />
          </div>

          <div class="form-group">
            <label for="role">Role</label>
            <select id="role" v-model="form.role" required>
              <option value="" disabled>Pilih Role</option>
              <option value="admin">Admin</option>
              <option value="dokter">Dokter</option>
              <option value="terapis">Terapis</option>
            </select>
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <input
              type="password"
              id="password"
              v-model="form.password"
              placeholder="••••••••"
              required
            />
          </div>

          <div class="form-group">
            <label for="password_confirmation">Konfirmasi Password</label>
            <input
              type="password"
              id="password_confirmation"
              v-model="form.password_confirmation"
              placeholder="••••••••"
              required
            />
          </div>

          <button type="submit" class="btn-register" :disabled="authStore.loading">
            <span v-if="authStore.loading">Memproses...</span>
            <span v-else>Daftar</span>
          </button>

          <div v-if="authStore.error" class="error-message">
            {{ authStore.error }}
          </div>

          <p class="login-link">
            Sudah punya akun? 
            <router-link to="/login">Masuk di sini</router-link>
          </p>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  name: '',
  email: '',
  role: '',
  password: '',
  password_confirmation: ''
})

const handleRegister = async () => {
  try {
    const success = await authStore.register(form.value)
    if (success) {
      alert('Registrasi berhasil! Silakan login.')
      router.push('/login')
    }
  } catch (error) {
    console.error('Registration failed:', error)
  }
}
</script>

<style scoped>
.register-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #ffffff 100%);
  position: relative;
  overflow: hidden;
  padding: 2rem;
}

.circle {
  position: absolute;
  border-radius: 50%;
  background: rgba(59, 130, 246, 0.1);
  z-index: 0;
}

.circle-1 {
  width: 400px;
  height: 400px;
  top: -100px;
  left: -100px;
}

.circle-2 {
  width: 300px;
  height: 300px;
  bottom: -50px;
  right: -50px;
}

.back-btn {
  position: absolute;
  top: 2rem;
  left: 2rem;
  color: #1e40af;
  text-decoration: none;
  font-weight: 600;
  z-index: 10;
  transition: all 0.3s;
}

.back-btn:hover {
  color: #1e3a8a;
  transform: translateX(-4px);
}

.register-container {
  display: flex;
  max-width: 1000px;
  margin: 0 auto;
  background: white;
  border-radius: 1rem;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  position: relative;
  z-index: 1;
  min-height: 700px;
}

.logo-section {
  flex: 1;
  background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: white;
  text-align: center;
}

.logo {
  width: 120px;
  height: 120px;
  margin-bottom: 2rem;
}

.logo-section h1 {
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
  font-weight: 700;
}

.logo-section p {
  font-size: 1rem;
  opacity: 0.9;
}

.form-section {
  flex: 1;
  padding: 3rem;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.form-section h2 {
  font-size: 2rem;
  color: #1e293b;
  margin-bottom: 0.5rem;
}

.subtitle {
  color: #64748b;
  margin-bottom: 2rem;
}

.register-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.form-group label {
  font-weight: 600;
  color: #334155;
  font-size: 0.875rem;
}

.form-group input, .form-group select {
  padding: 0.75rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 1rem;
  transition: all 0.3s;
}

.form-group input:focus, .form-group select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.btn-register {
  margin-top: 1rem;
  padding: 1rem;
  background: #1e40af;
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-register:hover:not(:disabled) {
  background: #1e3a8a;
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(30, 64, 175, 0.3);
}

.btn-register:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error-message {
  padding: 0.75rem;
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 0.5rem;
  color: #dc2626;
  text-align: center;
  font-size: 0.875rem;
}

.login-link {
  text-align: center;
  color: #64748b;
  font-size: 0.875rem;
}

.login-link a {
  color: #3b82f6;
  text-decoration: none;
  font-weight: 600;
}

.login-link a:hover {
  text-decoration: underline;
}

@media (max-width: 768px) {
  .register-container {
    flex-direction: column;
  }

  .logo-section {
    padding: 2rem;
  }

  .form-section {
    padding: 2rem;
  }
}
</style>
