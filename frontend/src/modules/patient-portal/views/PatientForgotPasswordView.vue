<template>
  <div class="forgot-page">
    <div class="circle circle-1"></div>
    <div class="circle circle-2"></div>

    <router-link to="/pasien/login" class="back-btn">← Kembali ke Login</router-link>

    <div class="forgot-container">
      <!-- Logo Section -->
      <div class="logo-section">
        <img src="@/assets/SITARA_RM_BG.png" alt="SITARA" class="logo" />
        <h1>Portal Pasien</h1>
        <p>Akses layanan kesehatan Anda</p>
      </div>

      <!-- Form Section -->
      <div class="form-section">

        <!-- STATE: Form Input Email -->
        <template v-if="state === 'form'">
          <div class="header">
            <div class="icon-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
            </div>
            <h2>Lupa Password?</h2>
            <p class="subtitle">
              Masukkan alamat Gmail yang terdaftar di akun pasien Anda.
              Kami akan mengirimkan link reset password ke email tersebut.
            </p>
          </div>

          <form @submit.prevent="handleSubmit" class="forgot-form">
            <div class="form-group">
              <label for="email">Alamat Gmail</label>
              <div class="input-wrapper">
                <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                  fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                  <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
                <input
                  type="email"
                  id="email"
                  v-model="email"
                  placeholder="contoh@gmail.com"
                  :class="{ error: errorMsg }"
                  autocomplete="email"
                  required
                />
              </div>
              <span v-if="errorMsg" class="error-text">{{ errorMsg }}</span>
            </div>

            <button type="submit" class="btn-submit" :disabled="loading">
              <span v-if="loading" class="spinner"></span>
              <span>{{ loading ? 'Mengirim...' : 'Kirim Link Reset Password' }}</span>
            </button>

            <p class="back-login-link">
              Ingat password? <router-link to="/pasien/login">Masuk di sini</router-link>
            </p>
          </form>
        </template>

        <!-- STATE: Email Terkirim -->
        <template v-else-if="state === 'success'">
          <div class="success-state">
            <div class="success-icon">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
              </svg>
            </div>

            <h2>Email Terkirim!</h2>
            <p class="subtitle">
              Link reset password telah dikirim ke:
            </p>
            <div class="email-badge">{{ submittedEmail }}</div>

            <!-- Instruksi -->
            <div class="steps">
              <div class="step-item">
                <div class="step-number">1</div>
                <p>Buka aplikasi <strong>Gmail</strong> atau kunjungi <strong>gmail.com</strong>.</p>
              </div>
              <div class="step-item">
                <div class="step-number">2</div>
                <p>Cari email dari <strong>SITARA</strong> dengan subjek <em>"Reset Password Akun SITARA Anda"</em>.</p>
              </div>
              <div class="step-item">
                <div class="step-number">3</div>
                <p>Klik link yang ada di dalam email untuk mereset password Anda.</p>
              </div>
              <div class="step-item">
                <div class="step-number">4</div>
                <p>Link berlaku selama <strong>24 jam</strong>. Jika tidak ada email, cek folder <strong>Spam</strong>.</p>
              </div>
            </div>

            <!-- Info box -->
            <div class="info-box">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="info-icon">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
              </svg>
              <p>Email tidak masuk? Tunggu beberapa menit, atau
                <button class="resend-btn" @click="resendEmail" :disabled="resendCooldown > 0">
                  {{ resendCooldown > 0 ? `kirim ulang (${resendCooldown}s)` : 'kirim ulang' }}
                </button>.
              </p>
            </div>

            <router-link to="/pasien/login" class="btn-back-login">
              Kembali ke Halaman Login
            </router-link>
          </div>
        </template>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onUnmounted } from 'vue'
import api from '../../../core/services/api'

const state = ref('form')
const email = ref('')
const submittedEmail = ref('')
const errorMsg = ref('')
const loading = ref(false)
const resendCooldown = ref(0)

let cooldownTimer = null

const startCooldown = () => {
  resendCooldown.value = 60
  cooldownTimer = setInterval(() => {
    resendCooldown.value--
    if (resendCooldown.value <= 0) {
      clearInterval(cooldownTimer)
    }
  }, 1000)
}

const sendRequest = async (emailValue) => {
  const response = await api.post('/pasien/forgot-password', { email: emailValue })
  return response.data
}

const handleSubmit = async () => {
  errorMsg.value = ''
  if (!email.value) {
    errorMsg.value = 'Email harus diisi.'
    return
  }

  loading.value = true
  try {
    const result = await sendRequest(email.value)
    if (result?.success) {
      submittedEmail.value = email.value
      state.value = 'success'
      startCooldown()
    } else {
      errorMsg.value = result?.message || 'Terjadi kesalahan. Coba lagi.'
    }
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Tidak dapat terhubung ke server.'
  } finally {
    loading.value = false
  }
}

const resendEmail = async () => {
  if (resendCooldown.value > 0) return
  try {
    await sendRequest(submittedEmail.value)
    startCooldown()
  } catch {
    // silent fail pada resend
  }
}

onUnmounted(() => {
  if (cooldownTimer) clearInterval(cooldownTimer)
})
</script>

<style scoped>
.forgot-page {
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
  top: 2rem; left: 2rem;
  color: #059669;
  text-decoration: none;
  font-weight: 600;
  z-index: 10;
  transition: all 0.3s;
}
.back-btn:hover { color: #047857; transform: translateX(-4px); }

.forgot-container {
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

/* Logo */
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
.logo-section h1 { font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem; }
.logo-section p { font-size: 1rem; opacity: 0.9; }

/* Form Section */
.form-section {
  flex: 1.2;
  padding: 3rem;
  display: flex;
  flex-direction: column;
  justify-content: center;
  overflow-y: auto;
}

/* Header */
.header { margin-bottom: 1.75rem; }
.icon-wrapper {
  width: 56px; height: 56px;
  background: #ecfdf5;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 1rem;
  color: #059669;
}
.icon-wrapper svg { width: 28px; height: 28px; }
.header h2 { font-size: 1.75rem; color: #1e293b; margin-bottom: 0.5rem; }
.subtitle { color: #64748b; font-size: 0.95rem; line-height: 1.6; }

/* Form */
.forgot-form { display: flex; flex-direction: column; gap: 1.25rem; }
.form-group { display: flex; flex-direction: column; gap: 0.5rem; }
.form-group label { font-weight: 600; color: #334155; font-size: 0.875rem; }

.input-wrapper { position: relative; }
.input-icon {
  position: absolute;
  left: 0.875rem; top: 50%;
  transform: translateY(-50%);
  width: 18px; height: 18px;
  color: #94a3b8;
  pointer-events: none;
}
.form-group input {
  width: 100%;
  padding: 0.875rem 1rem 0.875rem 2.75rem;
  border: 2px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 1rem;
  transition: all 0.3s;
  box-sizing: border-box;
}
.form-group input:focus {
  outline: none;
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}
.form-group input.error { border-color: #ef4444; }
.error-text { color: #ef4444; font-size: 0.875rem; }

.btn-submit {
  display: flex; align-items: center; justify-content: center; gap: 0.5rem;
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
.btn-submit:hover:not(:disabled) {
  background: #047857;
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(5, 150, 105, 0.3);
}
.btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }

.spinner {
  width: 18px; height: 18px;
  border: 2px solid rgba(255,255,255,0.4);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.back-login-link {
  text-align: center;
  color: #64748b;
  font-size: 0.875rem;
}
.back-login-link a { color: #10b981; font-weight: 600; text-decoration: none; }
.back-login-link a:hover { text-decoration: underline; }

/* Success State */
.success-state { display: flex; flex-direction: column; gap: 1rem; }

.success-icon {
  width: 64px; height: 64px;
  background: #ecfdf5;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: #059669;
}
.success-icon svg { width: 32px; height: 32px; }
.success-state h2 { font-size: 1.75rem; color: #1e293b; margin: 0; }

.email-badge {
  display: inline-block;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  color: #15803d;
  padding: 0.5rem 1rem;
  border-radius: 2rem;
  font-weight: 600;
  font-size: 0.9rem;
  word-break: break-all;
}

/* Steps */
.steps { display: flex; flex-direction: column; gap: 0.625rem; }
.step-item {
  display: flex; align-items: flex-start; gap: 0.75rem;
  padding: 0.75rem;
  background: #f8fafc;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  color: #475569;
}
.step-number {
  min-width: 28px; height: 28px;
  background: #059669;
  color: white;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700;
  font-size: 0.8rem;
  flex-shrink: 0;
}
.step-item p { margin: 0; line-height: 1.5; }

/* Info Box */
.info-box {
  display: flex; align-items: flex-start; gap: 0.625rem;
  padding: 0.875rem 1rem;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 0.5rem;
  font-size: 0.85rem;
  color: #15803d;
}
.info-icon { width: 18px; height: 18px; flex-shrink: 0; margin-top: 2px; }
.info-box p { margin: 0; line-height: 1.5; }

.resend-btn {
  background: none;
  border: none;
  color: #059669;
  font-weight: 600;
  cursor: pointer;
  padding: 0;
  font-size: inherit;
  text-decoration: underline;
}
.resend-btn:disabled { color: #94a3b8; cursor: not-allowed; text-decoration: none; }

.btn-back-login {
  display: block;
  text-align: center;
  padding: 0.875rem;
  background: #059669;
  color: white;
  text-decoration: none;
  border-radius: 0.5rem;
  font-weight: 600;
  transition: all 0.3s;
}
.btn-back-login:hover { background: #047857; transform: translateY(-2px); }

@media (max-width: 768px) {
  .forgot-container { flex-direction: column; }
  .logo-section, .form-section { padding: 2rem; }
}
</style>
