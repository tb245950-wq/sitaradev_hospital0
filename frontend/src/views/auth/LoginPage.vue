<template>
  <div class="login-page">
    <!-- Decorative Circles -->
    <div class="circle circle-1"></div>
    <div class="circle circle-2"></div>

    <!-- Back Button -->
    <router-link to="/" class="back-btn">
      ← Kembali
    </router-link>

    <!-- Login Form Container -->
    <div class="login-container">
      <div class="logo-section">
        <img src="/logo-sitara.png" alt="SITARA" class="logo" />
        <h1>SITARA</h1>
        <p>Sistem Informasi Terpadu</p>
      </div>

      <div class="form-section">
        <h2>Masuk ke Akun</h2>
        <p class="subtitle">Silakan masukkan email dan password Anda</p>

        <form @submit.prevent="handleLogin" class="login-form">
          <!-- Email Input -->
          <div class="form-group">
            <label for="email">Email</label>
            <input
              type="email"
              id="email"
              v-model="form.email"
              placeholder="nama@email.com"
              required
              :class="{ error: errors.email }"
            />
            <span v-if="errors.email" class="error-text">{{ errors.email }}</span>
          </div>

          <!-- Password Input -->
          <div class="form-group">
            <label for="password">Password</label>
            <input
              type="password"
              id="password"
              v-model="form.password"
              placeholder="••••••••"
              required
              :class="{ error: errors.password }"
            />
            <span v-if="errors.password" class="error-text">{{ errors.password }}</span>
          </div>

          <!-- Remember Me & Forgot Password -->
          <div class="form-options">
            <label class="checkbox-label">
              <input type="checkbox" v-model="form.remember" />
              <span>Ingat saya</span>
            </label>
            <a href="#" class="forgot-link">Lupa password?</a>
          </div>

          <!-- Submit Button -->
          <button type="submit" class="btn-login" :disabled="loading">
            <span v-if="loading">Loading...</span>
            <span v-else>Masuk</span>
          </button>

          <!-- Error Message -->
          <div v-if="errorMessage" class="error-message">
            {{ errorMessage }}
          </div>

          <!-- Register Link -->
          <p class="register-link">
            Belum punya akun? 
            <router-link to="/register">Daftar sekarang</router-link>
          </p>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const form = ref({
  email: '',
  password: '',
  remember: false
})

const errors = ref({
  email: '',
  password: ''
})

const errorMessage = ref('')
const loading = ref(false)

const handleLogin = async () => {
  // Reset errors
  errors.value = { email: '', password: '' }
  errorMessage.value = ''

  // Validation
  if (!form.value.email) {
    errors.value.email = 'Email harus diisi'
    return
  }

  if (!form.value.password) {
    errors.value.password = 'Password harus diisi'
    return
  }

  loading.value = true

  try {
    const response = await axios.post('http://127.0.0.1:8000/api/login', {
      email: form.value.email,
      password: form.value.password
    })

    if (response.data.success) {
      // Simpan token
      localStorage.setItem('token', response.data.data.token)
      localStorage.setItem('user', JSON.stringify(response.data.data.user))

      // Redirect ke dashboard
      router.push('/dashboard')
    }
  } catch (error) {
    if (error.response) {
      errorMessage.value = error.response.data.message || 'Login gagal. Periksa email dan password Anda.'
    } else {
      errorMessage.value = 'Tidak dapat terhubung ke server. Pastikan backend Laravel berjalan.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-page {
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

.login-container {
  display: flex;
  max-width: 1000px;
  margin: 0 auto;
  background: white;
  border-radius: 1rem;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  position: relative;
  z-index: 1;
  min-height: 600px;
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

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: 600;
  color: #334155;
  font-size: 0.875rem;
}

.form-group input {
  padding: 0.875rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 1rem;
  transition: all 0.3s;
}

.form-group input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group input.error {
  border-color: #ef4444;
}

.error-text {
  color: #ef4444;
  font-size: 0.875rem;
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  color: #64748b;
  font-size: 0.875rem;
}

.forgot-link {
  color: #3b82f6;
  text-decoration: none;
  font-size: 0.875rem;
  font-weight: 500;
}

.forgot-link:hover {
  text-decoration: underline;
}

.btn-login {
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

.btn-login:hover:not(:disabled) {
  background: #1e3a8a;
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(30, 64, 175, 0.3);
}

.btn-login:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error-message {
  padding: 1rem;
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 0.5rem;
  color: #dc2626;
  text-align: center;
  font-size: 0.875rem;
}

.register-link {
  text-align: center;
  color: #64748b;
  font-size: 0.875rem;
}

.register-link a {
  color: #3b82f6;
  text-decoration: none;
  font-weight: 600;
}

.register-link a:hover {
  text-decoration: underline;
}

@media (max-width: 768px) {
  .login-container {
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