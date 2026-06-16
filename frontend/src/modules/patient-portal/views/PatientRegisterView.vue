<template>
  <div class="patient-register-page">
    <div class="circle circle-1"></div>
    <div class="circle circle-2"></div>

    <router-link to="/" class="back-btn">← Kembali ke Beranda</router-link>

    <div class="register-container">
      <div class="logo-section">
        <img src="../../../assets/SITARA_RM_BG.png" alt="SITARA" class="logo" />
        <h1>Daftar Pasien</h1>
        <p>Buat akun untuk akses layanan SITARA</p>
      </div>

      <div class="form-section">
        <h2>Registrasi Akun Baru</h2>
        <p class="subtitle">Isi data diri Anda dengan lengkap</p>

        <form @submit.prevent="handleRegister" class="register-form">
          <div class="form-group">
            <label for="name">Nama Lengkap <span class="required">*</span></label>
            <input type="text" id="name" v-model="form.name" placeholder="Nama lengkap" required />
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="email">Email <span class="required">*</span></label>
              <input type="email" id="email" v-model="form.email" placeholder="nama@email.com" required />
            </div>

            <div class="form-group">
              <label for="nik">NIK <span class="required">*</span></label>
              <input type="text" id="nik" v-model="form.nik" placeholder="16 digit NIK" maxlength="16" required />
            </div>
          </div>

          <div class="form-group">
            <label for="phone">Nomor Telepon <span class="required">*</span></label>
            <input type="tel" id="phone" v-model="form.phone" placeholder="08xxxxxxxxxx" required />
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="password">Password <span class="required">*</span></label>
              <input type="password" id="password" v-model="form.password" placeholder="Min. 8 karakter" minlength="8" required />
            </div>

            <div class="form-group">
              <label for="password_confirmation">Konfirmasi Password <span class="required">*</span></label>
              <input type="password" id="password_confirmation" v-model="form.password_confirmation" placeholder="Ulangi password" minlength="8" required />
            </div>
          </div>

          <button type="submit" class="btn-register" :disabled="patientStore.loading">
            <span v-if="patientStore.loading">Memproses...</span>
            <span v-else>Daftar</span>
          </button>

          <div v-if="patientStore.error" class="error-message">
            {{ patientStore.error }}
          </div>

          <p class="login-link">
            Sudah punya akun? 
            <router-link to="/pasien/login">Masuk di sini</router-link>
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
  name: '',
  email: '',
  nik: '',
  phone: '',
  password: '',
  password_confirmation: ''
})

const handleRegister = async () => {
  if (form.value.password !== form.value.password_confirmation) {
    patientStore.error = 'Password dan konfirmasi tidak cocok'
    return
  }
  
  const result = await patientStore.register(form.value)
  if (result.success) {
    alert('Registrasi berhasil! Anda akan diarahkan ke dashboard.')
    router.push('/pasien/dashboard')
  }
}
</script>

<style scoped>
.patient-register-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #ecfdf5 0%, #ffffff 100%);
  position: relative;
  overflow: hidden;
  padding: 2rem;
}

.circle { position: absolute; border-radius: 50%; background: rgba(16, 185, 129, 0.1); z-index: 0; }
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
  padding: 2.5rem;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.form-section h2 { font-size: 1.75rem; color: #1e293b; margin-bottom: 0.5rem; }
.subtitle { color: #64748b; margin-bottom: 1.5rem; }

.register-form { display: flex; flex-direction: column; gap: 1rem; }

.form-group { display: flex; flex-direction: column; gap: 0.4rem; }
.form-group label { font-weight: 600; color: #334155; font-size: 0.875rem; }
.required { color: #ef4444; }
.form-group input {
  padding: 0.75rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 1rem;
}
.form-group input:focus {
  outline: none;
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

.btn-register {
  margin-top: 0.5rem;
  padding: 1rem;
  background: #059669;
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
}

.btn-register:hover:not(:disabled) { background: #047857; }
.btn-register:disabled { opacity: 0.6; cursor: not-allowed; }

.error-message {
  padding: 0.75rem;
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 0.5rem;
  color: #dc2626;
  text-align: center;
  font-size: 0.875rem;
}

.login-link { text-align: center; color: #64748b; font-size: 0.875rem; }
.login-link a { color: #10b981; text-decoration: none; font-weight: 600; }

@media (max-width: 768px) {
  .register-container { flex-direction: column; }
  .form-row { grid-template-columns: 1fr; }
  .logo-section, .form-section { padding: 2rem; }
}
</style>
