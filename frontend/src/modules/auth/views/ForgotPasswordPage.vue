<template>
  <div class="forgot-page">
    <!-- Decorative Circles -->
    <div class="circle circle-1"></div>
    <div class="circle circle-2"></div>

    <!-- Back Button -->
    <router-link to="/login" class="back-btn">
      ← Kembali ke Login
    </router-link>

    <div class="forgot-container">
      <!-- Logo Section -->
      <div class="logo-section">
        <img src="@/assets/SITARA_RM_BG.png" alt="SITARA" class="logo" />
        <h1>SITARA</h1>
        <p>Sistem Informasi Terpadu</p>
      </div>

      <!-- Form Section -->
      <div class="form-section">

        <!-- STATE 1: Form Input Email -->
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
            <p class="subtitle">Masukkan email akun SITARA Anda untuk melanjutkan proses verifikasi.</p>
          </div>

          <!-- Info Domain Email -->
          <div class="info-box info-blue">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="info-icon">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="8" x2="12" y2="12"></line>
              <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <div>
              <p><strong>Email Internal SITARA</strong></p>
              <p>Akun staff SITARA menggunakan email internal <strong>@sitara.com</strong>.
                Proses reset password dilakukan melalui verifikasi oleh Administrator.</p>
            </div>
          </div>

          <form @submit.prevent="handleSubmit" class="forgot-form">
            <div class="form-group">
              <label for="email">Alamat Email</label>
              <input
                type="email"
                id="email"
                v-model="email"
                placeholder="nama@sitara.com"
                :class="{ error: errorMsg }"
                required
              />
              <span v-if="errorMsg" class="error-text">{{ errorMsg }}</span>
            </div>

            <button type="submit" class="btn-submit" :disabled="loading">
              <span v-if="loading" class="spinner"></span>
              <span>{{ loading ? 'Memproses...' : 'Kirim Permintaan' }}</span>
            </button>
          </form>
        </template>

        <!-- STATE 2: Sukses — Instruksi Verifikasi -->
        <template v-else-if="state === 'success'">
          <div class="success-state">
            <div class="success-icon">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.62 3.44 2 2 0 0 1 3.6 1.27h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6 6l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.73 16.92z"></path>
              </svg>
            </div>

            <h2>Permintaan Terkirim!</h2>
            <p class="subtitle">Permintaan reset password untuk <strong>{{ submittedEmail }}</strong> telah dicatat.</p>

            <!-- Box Instruksi Utama -->
            <div class="info-box info-yellow">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="info-icon">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
              </svg>
              <div>
                <p><strong>Verifikasi Email Diperlukan</strong></p>
                <p>Karena akun SITARA menggunakan email internal <strong>@sitara.com</strong>,
                  reset password <strong>tidak dapat dilakukan secara otomatis</strong>.</p>
              </div>
            </div>

            <!-- Langkah-langkah -->
            <div class="steps">
              <p class="steps-title">Langkah selanjutnya:</p>
              <div class="step-item">
                <div class="step-number">1</div>
                <p>Hubungi <strong>Administrator SITARA</strong> atau <strong>Super Admin</strong> klinik Anda.</p>
              </div>
              <div class="step-item">
                <div class="step-number">2</div>
                <p>Sampaikan bahwa Anda perlu reset password untuk email <strong>{{ submittedEmail }}</strong>.</p>
              </div>
              <div class="step-item">
                <div class="step-number">3</div>
                <p>Admin akan melakukan verifikasi identitas dan mereset password melalui panel manajemen.</p>
              </div>
              <div class="step-item">
                <div class="step-number">4</div>
                <p>Password baru akan diberikan langsung oleh Admin secara aman.</p>
              </div>
            </div>

            <router-link to="/login" class="btn-back-login">
              Kembali ke Halaman Login
            </router-link>
          </div>
        </template>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '../../../core/services/api'

const state = ref('form') // 'form' | 'success'
const email = ref('')
const submittedEmail = ref('')
const errorMsg = ref('')
const loading = ref(false)

const handleSubmit = async () => {
  errorMsg.value = ''

  if (!email.value) {
    errorMsg.value = 'Email harus diisi.'
    return
  }

  loading.value = true

  try {
    const response = await api.post('/forgot-password', { email: email.value })

    if (response.data?.success) {
      submittedEmail.value = email.value
      state.value = 'success'
    } else {
      errorMsg.value = response.data?.message || 'Terjadi kesalahan. Coba lagi.'
    }
  } catch (err) {
    if (err.response?.data?.message) {
      errorMsg.value = err.response.data.message
    } else {
      errorMsg.value = 'Tidak dapat terhubung ke server. Coba lagi.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.forgot-page {
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
.circle-1 { width: 400px; height: 400px; top: -100px; left: -100px; }
.circle-2 { width: 300px; height: 300px; bottom: -50px; right: -50px; }

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
.back-btn:hover { color: #1e3a8a; transform: translateX(-4px); }

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

/* Logo Section */
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
.logo { width: 120px; height: 120px; margin-bottom: 2rem; }
.logo-section h1 { font-size: 2.5rem; margin-bottom: 0.5rem; font-weight: 700; }
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
.header { margin-bottom: 1.5rem; }
.icon-wrapper {
  width: 56px; height: 56px;
  background: #eff6ff;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 1rem;
  color: #1e40af;
}
.icon-wrapper svg { width: 28px; height: 28px; }
.header h2 { font-size: 1.75rem; color: #1e293b; margin-bottom: 0.5rem; }
.subtitle { color: #64748b; font-size: 0.95rem; line-height: 1.5; }

/* Info Box */
.info-box {
  display: flex;
  gap: 0.75rem;
  padding: 1rem;
  border-radius: 0.625rem;
  font-size: 0.85rem;
  line-height: 1.5;
  margin-bottom: 1.5rem;
}
.info-blue { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
.info-yellow { background: #fefce8; border: 1px solid #fde047; color: #854d0e; }
.info-icon { width: 20px; height: 20px; flex-shrink: 0; margin-top: 2px; }
.info-box p { margin: 0 0 0.25rem; }

/* Form */
.forgot-form { display: flex; flex-direction: column; gap: 1.25rem; }
.form-group { display: flex; flex-direction: column; gap: 0.5rem; }
.form-group label { font-weight: 600; color: #334155; font-size: 0.875rem; }
.form-group input {
  padding: 0.875rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 1rem;
  transition: all 0.3s;
}
.form-group input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
.form-group input.error { border-color: #ef4444; }
.error-text { color: #ef4444; font-size: 0.875rem; }

.btn-submit {
  display: flex; align-items: center; justify-content: center; gap: 0.5rem;
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
.btn-submit:hover:not(:disabled) { background: #1e3a8a; transform: translateY(-2px); box-shadow: 0 6px 12px rgba(30, 64, 175, 0.3); }
.btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }

.spinner {
  width: 18px; height: 18px;
  border: 2px solid rgba(255,255,255,0.4);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Success State */
.success-state { display: flex; flex-direction: column; gap: 1rem; }
.success-icon {
  width: 64px; height: 64px;
  background: #ecfdf5;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: #16a34a;
}
.success-icon svg { width: 32px; height: 32px; }
.success-state h2 { font-size: 1.75rem; color: #1e293b; margin: 0; }

/* Steps */
.steps { display: flex; flex-direction: column; gap: 0.75rem; }
.steps-title { font-weight: 600; color: #334155; margin-bottom: 0.25rem; }
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
  background: #1e40af;
  color: white;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700;
  font-size: 0.8rem;
  flex-shrink: 0;
}
.step-item p { margin: 0; line-height: 1.5; }

.btn-back-login {
  display: block;
  text-align: center;
  padding: 0.875rem;
  background: #1e40af;
  color: white;
  text-decoration: none;
  border-radius: 0.5rem;
  font-weight: 600;
  transition: all 0.3s;
  margin-top: 0.5rem;
}
.btn-back-login:hover { background: #1e3a8a; transform: translateY(-2px); }

@media (max-width: 768px) {
  .forgot-container { flex-direction: column; }
  .logo-section { padding: 2rem; }
  .form-section { padding: 2rem; }
}
</style>
