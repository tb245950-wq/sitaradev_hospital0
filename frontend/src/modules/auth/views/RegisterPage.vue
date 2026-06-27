<template>
  <div class="register-container">
    <div class="register-card">
      <div class="register-header">
        <h1>🏥 SITARA</h1>
        <h2>Registrasi Akun Staff</h2>
        <p>Daftarkan akun untuk Admin, Dokter, atau Terapis</p>
      </div>

      <form @submit.prevent="handleRegister" class="register-form">
        <div class="form-group">
          <label>Nama Lengkap *</label>
          <input v-model="form.name" type="text" placeholder="Masukkan nama lengkap" required />
        </div>

        <div class="form-group">
          <label>Email *</label>
          <input v-model="form.email" type="email" placeholder="email@rumahsakit.com" required />
        </div>

        <div class="form-group">
          <label>NIP</label>
          <input v-model="form.nip" type="text" placeholder="Nomor Induk Pegawai" />
        </div>

        <div class="form-group">
          <label>No. Telepon</label>
          <input v-model="form.phone" type="text" placeholder="08xxxxxxxxxx" />
        </div>

        <div class="form-group">
          <label>Role *</label>
          <select v-model="form.role" required>
            <option value="" disabled>Pilih Role</option>
            <option value="dokter">Dokter</option>
            <option value="terapis">Terapis</option>
          </select>
          <small>Admin hanya dapat dibuat oleh Admin yang sudah ada.</small>
        </div>

        <div class="form-group">
          <label>Password *</label>
          <input v-model="form.password" type="password" placeholder="Minimal 8 karakter" minlength="8" required />
        </div>

        <div class="form-group">
          <label>Konfirmasi Password *</label>
          <input v-model="form.password_confirmation" type="password" placeholder="Ulangi password" required />
        </div>

        <div v-if="error" class="error-message">{{ error }}</div>
        <div v-if="success" class="success-message">{{ success }}</div>

        <button type="submit" class="btn-register" :disabled="loading">
          {{ loading ? 'Mendaftarkan...' : 'Daftar' }}
        </button>
      </form>

      <div class="register-footer">
        <p>Sudah punya akun? <router-link to="/login">Login di sini</router-link></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '../services/authService'

const router = useRouter()

const form = ref({
  name: '',
  email: '',
  nip: '',
  phone: '',
  role: '',
  password: '',
  password_confirmation: ''
})
const loading = ref(false)
const error = ref(null)
const success = ref(null)

async function handleRegister() {
  error.value = null
  success.value = null

  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Password dan konfirmasi password tidak cocok.'
    return
  }

  loading.value = true
  try {
    const result = await authService.register(form.value)
    if (result.success) {
      success.value = 'Pendaftaran berhasil! Akun Anda menunggu aktivasi oleh Admin.'
      setTimeout(() => router.push('/login'), 2000)
    } else {
      error.value = result.error || 'Pendaftaran gagal.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.register-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #0f4c81 0%, #1a73e8 100%);
  padding: 2rem;
}
.register-card {
  background: white;
  border-radius: 1rem;
  padding: 2.5rem;
  width: 100%;
  max-width: 480px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}
.register-header { text-align: center; margin-bottom: 2rem; }
.register-header h1 { font-size: 2rem; margin-bottom: 0.5rem; }
.register-header h2 { font-size: 1.3rem; color: #1a73e8; margin-bottom: 0.3rem; }
.register-header p { color: #6b7280; font-size: 0.9rem; }
.register-form { display: flex; flex-direction: column; gap: 1rem; }
.form-group { display: flex; flex-direction: column; gap: 0.25rem; }
.form-group label { font-size: 0.875rem; font-weight: 600; color: #374151; }
.form-group input,
.form-group select {
  padding: 0.625rem 0.875rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 0.95rem;
  transition: border-color 0.2s;
}
.form-group input:focus,
.form-group select:focus { outline: none; border-color: #1a73e8; box-shadow: 0 0 0 3px rgba(26,115,232,0.15); }
.form-group small { color: #9ca3af; font-size: 0.78rem; }
.error-message { background: #fee2e2; color: #dc2626; padding: 0.75rem 1rem; border-radius: 0.5rem; font-size: 0.875rem; }
.success-message { background: #dcfce7; color: #16a34a; padding: 0.75rem 1rem; border-radius: 0.5rem; font-size: 0.875rem; }
.btn-register {
  background: #1a73e8;
  color: white;
  border: none;
  padding: 0.75rem;
  border-radius: 0.5rem;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
  margin-top: 0.5rem;
}
.btn-register:hover:not(:disabled) { background: #1557b0; }
.btn-register:disabled { opacity: 0.6; cursor: not-allowed; }
.register-footer { text-align: center; margin-top: 1.5rem; color: #6b7280; font-size: 0.9rem; }
.register-footer a { color: #1a73e8; text-decoration: none; font-weight: 600; }
</style>
