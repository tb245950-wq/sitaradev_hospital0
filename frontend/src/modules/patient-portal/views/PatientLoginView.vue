<template>
  <div class="patient-login-page">
    <div class="circle circle-1"></div>
    <div class="circle circle-2"></div>

    <router-link to="/" class="back-btn">← Kembali ke Beranda</router-link>

    <div class="login-container">
      <div class="logo-section">
        <img src="@/assets/SITARA_RM_BG.png" alt="SITARA" class="logo" />
        <h1>Masuk Pasien</h1>
        <p>Akses layanan kesehatan Anda</p>
      </div>


      <div class="form-section">
        <h2>Masuk ke Akun Pasien</h2>
        <p class="subtitle">Akses jadwal, riwayat medis, dan booking antrian</p>

        <form @submit.prevent="handleLogin" class="login-form">
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
            <label for="password">Password</label>
            <input
              type="password"
              id="password"
              v-model="form.password"
              placeholder="••••••••"
              required
            />
          </div>

          <button type="submit" class="btn-login" :disabled="patientStore.loading">
            <span v-if="patientStore.loading">Memproses...</span>
            <span v-else>Masuk</span>
          </button>

          <div v-if="patientStore.error" class="error-message">
            {{ patientStore.error }}
          </div>

          <p class="register-link">
            Belum punya akun? 
            <router-link to="/pasien/register">Daftar di sini</router-link>
          </p>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { usePatientStore } from '../stores/patientStore'

const router = useRouter()
const patientStore = usePatientStore()

const form = ref({
  email: '',
  password: ''
})

const handleLogin = async () => {
  const result = await patientStore.login(form.value.email, form.value.password)
  if (result.success) {
    router.push('/pasien/dashboard')
  }
}
</script>

<style scoped>
.patient-login-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #ecfdf5 0%, #ffffff 100%);
  position: relative;
  overflow: hidden;
  padding: 2rem;
}

.circle {
  position: absolute;
  border-radius: 50%;
  background: rgba(16, 185, 129, 0.1);
  z-index: 0;
}

.circle-1 { width: 400px; height: 400px; top: -100px; left: -100px; }
.circle-2 { width: 300px; height: 300px; bottom: -50px; right: -50px; }

.back-btn {
  position: absolute;
  top: 2rem;
  left: 2rem;
  color: #059669;
  text-decoration: none;
  font-weight: 600;
  z-index: 10;
  transition: all 0.3s;
}

.back-btn:hover { color: #047857; transform: translateX(-4px); }

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
  background: linear-gradient(135deg, #059669 0%, #10b981 100%);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: white;
  text-align: center;
}

.logo { width: 120px; height: 120px; margin-bottom: 2rem; }
.logo-section h1 { font-size: 2.5rem; margin-bottom: 0.5rem; font-weight: 700; }
.logo-section p { font-size: 1rem; opacity: 0.9; }

.form-section {
  flex: 1;
  padding: 3rem;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.form-section h2 { font-size: 2rem; color: #1e293b; margin-bottom: 0.5rem; }
.subtitle { color: #64748b; margin-bottom: 2rem; }

.login-form { display: flex; flex-direction: column; gap: 1.25rem; }

.form-group { display: flex; flex-direction: column; gap: 0.4rem; }
.form-group label { font-weight: 600; color: #334155; font-size: 0.875rem; }
.form-group input {
  padding: 0.75rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 1rem;
  transition: all 0.3s;
}
.form-group input:focus {
  outline: none;
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.btn-login {
  margin-top: 0.5rem;
  padding: 1rem;
  background: #059669;
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-login:hover:not(:disabled) {
  background: #047857;
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(5, 150, 105, 0.3);
}

.btn-login:disabled { opacity: 0.6; cursor: not-allowed; }

.error-message {
  padding: 0.75rem;
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 0.5rem;
  color: #dc2626;
  text-align: center;
  font-size: 0.875rem;
}

.register-link { text-align: center; color: #64748b; font-size: 0.875rem; }
.register-link a { color: #10b981; text-decoration: none; font-weight: 600; }
.register-link a:hover { text-decoration: underline; }

@media (max-width: 768px) {
  .login-container { flex-direction: column; }
  .logo-section, .form-section { padding: 2rem; }
}
</style>
